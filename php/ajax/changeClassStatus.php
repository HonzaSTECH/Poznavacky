<?php
    session_start();
    
    include '../included/httpStats.php'; //Zahrnuje connect.php
    include '../included/logger.php';
    
    $cId = mysqli_real_escape_string($connection, $_POST['id']);
    $status = $_POST['status'];
    $code = $_POST['code'];
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
    
    //Kontrola vstupních údajů
    if ($status !== "Veřejná" && $status !== "Soukromá" && $status !== "Uzamčená")
    {
        filelog("Uživatel $userName se pokusil změnit stav třídy, ale odeslal neplatný údaj o požadovaném stavu.");
        echo "alert('Neplatný stav!');";
        die();
    }
    if(strlen($code) !== strspn($code, '0123456789') || strlen($code) !== 4)
    {
        filelog("Uživatel $userName se pokusil změnit stav třídy, ale odeslal neplatný nový vstupní kód.");
        echo "alert('Neplatný kód!');";
        die();
    }
    
    //KONTROLA DAT OK
    
    //Převod stavu na databázový tvar
    switch ($status)
    {
        case "Veřejná":
            $data = "'public'";
            break;
        case "Soukromá":
            $data = "'private', kod = $code";
            break;
        case "Uzamčená":
            $data = "'locked'";
            break;
    }
    
    $query = "UPDATE tridy SET status = $data WHERE tridy_id = $cId;";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert(".mysqli_error($connection).");";
        echo $query;
        echo "swal('Vyskytla se chyba při práci s databází. Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html','','error')";
        die();
    }
    $ip = $_SERVER['REMOTE_ADDR'];
    filelog("Uživatel $userName změnil stav třídy s ID $cId na $status z IP adresy $ip. V případě, že stav byl změněn na 'Soukromá', byl vstupní kód třídy změněn na $code.");
    echo "alert('Stav třídy byl úspěšně změněn.');";
    
    //Změna proměnných JavaScriptu (po obdržení odpovědi je odpověď vyhodnocena jako JS kód)
    echo "initialStatus = '$status';";
    if ($status === "Soukromá")
    {
        echo "initialCode = $code;";
    }
    echo "statusChange();";