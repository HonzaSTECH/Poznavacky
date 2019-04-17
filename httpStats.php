<?php
include 'connect.php';

//Z�skat datum a �as (den-m�s�c-rok + hodiny:minuty)
date_default_timezone_set("Europe/Prague");
$date = date("d-m-Y");
$time = date("H:i");

//Kontrola, zda ji� existuje z�znam pro danou minutu
$query = "SELECT id,pozadavky FROM statistika WHERE datum='$date' AND cas='$time'";
$result = mysqli_query($connection, $query);
if (!$result){die("An error occured while working with mysql server. Error code: ".mysqli_errno($connection).". Please, conntact administrator.");}
if (mysqli_num_rows($result) == 0)
{
    //Vyb�r�n� posledn�ho zaznamenan�ho �asu v tabulce statistika
    $query = "SELECT id,datum,cas FROM statistika ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result){die("An error occured while working with mysql server. Error code: ".mysqli_errno($connection).". Please, conntact administrator.");}
    $result = mysqli_fetch_array($result);
    $currentDate = $result['datum'];
    $currentTime = $result['cas'];
    
    //Zapisov�n� �as� bez po�adavk� do datab�ze
    while ($date != $currentDate || $time != $currentTime)
    {
        $dateObj = date_create_from_format("d-m-Y H:i",$currentDate." ".$currentTime);
        date_add($dateObj, new DateInterval("PT1M"));    //P�id�n� jedn� minuty;
        $currentDate = $dateObj->format('d-m-Y');
        $currentTime = $dateObj->format('H:i');
        $query = "INSERT INTO statistika VALUES (NULL,'$currentDate','$currentTime',0)";
        $result = mysqli_query($connection, $query);
        if (!$result){die("An error occured while working with mysql server. Error code: ".mysqli_errno($connection).". Please, conntact administrator.");}
    }
}
else
{
    $requests = mysqli_fetch_array($result);
    $requests = $requests['pozadavky'];
    $requests++;
    $query = "UPDATE statistika SET pozadavky=$requests WHERE datum='$date' AND cas='$time'";
}
if (!mysqli_query($connection,$query)){die("An error occured while working with mysql server. Error code: ".mysqli_errno($connection).". Please, conntact administrator.");}