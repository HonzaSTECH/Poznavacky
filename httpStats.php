<?php
include 'connect.php';

function groupStats()
{
	global $connection;
	$query = 'SELECT * FROM statistika ORDER BY id LIMIT 16';	//V tabulce statistika v�dy mus� z�stat zachov�na alespo� jedna ��dka.
	$result = mysqli_query($connection, $query);
	if (!$result){die('Error occured while executing '.$query);}
	$numRows = mysqli_num_rows($result);
	if ($numRows == 16)
	{
		$row = mysqli_fetch_array($result);
		$dateFrom = $row['datum'];
		$timeFrom = $row['cas'];
		$idFrom = $row['id'];
		$sum = $row['pozadavky'];
		for ($i = 1; $i < 15; $i++)
		{
			$row = mysqli_fetch_array($result);
			$idTo = $row['id'];
			$sum += $row['pozadavky'];
		}
		//Po��t�n� pr�m�ru
		$sum /= 15;
		$sum = round($sum);
		
		$query = 'INSERT INTO pozadavkyZa15Minut VALUES(NULL, "'.$dateFrom.'", "'.$timeFrom.'", '.$sum.')';
		$result = mysqli_query($connection, $query);
		if (!$result){die('Error occured while executing '.$query);}
		
		$query = 'DELETE FROM statistika WHERE id BETWEEN '.$idFrom.' AND '.$idTo;
		$result = mysqli_query($connection, $query);
		if (!$result){die('Error occured while executing '.$query);}
		
		return true;
	}
	return false;
}

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
        if ($date == $currentDate && $time == $currentTime){$query = "INSERT INTO statistika VALUES (NULL,'$currentDate','$currentTime',1)"; break;}
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

while(groupStats()){}