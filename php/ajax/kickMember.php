<?php
    session_start();
    
    include '../included/httpStats.php'; //Zahrnuje connect.php
    include '../included/logger.php';
    
    $cId = mysqli_real_escape_string($connection, $_POST['id']);
    $kicked = mysqli_real_escape_string($connection, $_POST['name']);
    $userId = $_SESSION['user']['id'];
    $userName = $_SESSION['user']['name'];
    
    //Ověření uživatele
    $query = "SELECT spravce FROM tridy WHERE tridy_id=$cId";
    $result = mysqli_query($connection, $query);
    $result = mysqli_fetch_array($result)['spravce'];
    
    if ($result !== $userId)
    {
        //Zamítnutí přístupu
        die("alert('Přístup zamítnut!')");
    }
    
    $query = "DELETE FROM clenstvi WHERE tridy_id = $cId AND uzivatele_id = (SELECT uzivatele_id FROM uzivatele WHERE jmeno = '$kicked' LIMIT 1) LIMIT 1";
    $result = mysqli_query($connection,$query);
    if (!$result)
    {
        echo "alert('Vyskytla se chyba při práci s databází. Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html');";
        die();
    }
    $ip = $_SERVER['REMOTE_ADDR'];
    filelog("Uživatel $userName odebral uživatele $kicked ze třídy s ID $cId.");
    
    //Změna proměnných JavaScriptu (po obdržení odpovědi je odpověď vyhodnocena jako JS kód)
    echo "updateMembers();";