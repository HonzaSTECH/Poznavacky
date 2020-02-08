<?php
    echo "alert('Změna stavu');";
    
    $cId = $_POST['id'];
    $status = $_POST['status'];
    $code = $_POST['code'];
    
    //Ověřit uživatele
    //Zpracovat proměnné
    //Udělat změny v databázi
    //Odeslat odpověď
    
    //Změna proměnných JavaScriptu (po obdržení odpovědi je odpověď vyhodnocena jako JS kód)
    echo "initialStatus = '$status';";
    if ($status === "Soukromá")
    {
        echo "initialCode = $code;";
    }
    echo "statusChange();";