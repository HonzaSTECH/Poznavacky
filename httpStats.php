<?php
include 'connect.php';

//Z�skat datum a �as (den-m�s�c-rok + hodiny:minuty)
date_default_timezone_set("Europe/Prague");
$date = date("d-m-Y");
$time = date("H:i");

//Kontrola, zda ji� existuje z�znam pro danou minutu
$query = "SELECT pozadavky FROM statistika WHERE datum='$date' AND cas='$time'";
$result = mysqli_query($connection, $query);
if (!$result){die("An error occured while working with mysql server. Error code: ".mysqli_errno($connection).". Please, conntact administrator.");}
if (mysqli_num_rows($result) == 0)
{
    $query = "INSERT INTO statistika VALUES (NULL,'$date','$time',1)";
}
else
{
    $requests = mysqli_fetch_array($result);
    $requests = $requests['pozadavky'];
    $requests++;
    $query = "UPDATE statistika SET pozadavky=$requests WHERE datum='$date' AND cas='$time'";
}
if (!mysqli_query($connection,$query)){die("An error occured while working with mysql server. Error code: ".mysqli_errno($connection).". Please, conntact administrator.");}