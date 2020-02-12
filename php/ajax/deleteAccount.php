<?php
    session_start();

    include '../included/httpStats.php'; //Zahrnuje connect.php
    include '../included/logger.php';
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $loggedUser = $_SESSION['user']['name'];
    $user = $_POST['newName'];
    $pass = $_POST['oldPass'];
    
    if ($loggedUser !== $user)
    {
        echo "swal('Varování','Vypadá to, že jste upravil strukturu webové stránky pomocí nástrojů pro vývojáře. Za takových podmínek nemůže služba správně pracovat. Prosíme, vyvarujte se v budoucnosti takovým úpravám.','warning');";
        filelog("Uživatel $loggedUser se pokusil odstranit účet uživatele $user z IP adresy $ip.");
        die();
    }
    
    //Ochrana proti SQL injekci
    $user = mysqli_real_escape_string($connection, $user);
    
    //Hledání účtu se zadaným jménem
    $query = "SELECT uzivatele_id,heslo FROM uzivatele WHERE jmeno='$user' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (empty(mysqli_num_rows($result)))    //Uživatel nenalezen
    {
        echo "swal('Něco se pokazilo.','Zkuste to prosím později, nebo se zkuste odhlásit a znovu přihlásit.','error')";
        filelog("Uživatel $loggedUser se pokusil odstranit svůj účet z IP adresy $ip, ale neuspěl kvůli neplatnému jménu.");
        die();
    }
    
    //Kontrola správnosti hesla
    $result = mysqli_fetch_array($result);
    if (password_verify($pass, $result['heslo']))   //Heslo je správné
    {
        $userId = $result['uzivatele_id'];
        
        $query = "DELETE FROM uzivatele WHERE uzivatele_id=$userId LIMIT 1";  //Odstranění samotného účtu
        
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "location.href = 'errSql.html';";
            
            $err = mysqli_error($connection);
            filelog("Vyskytla se SQL chyba při odstraňování uživatelského účtu: $err.");
            
            die();
        }
        else
        {
            filelog("Uživatel $user odstranil svůj účet z IP adresy $ip.");
            echo "location.href = 'php/logout.php';";
        }
    }
    else
    {
        //Toto by se nemělo zobrazit, pokud nedojde k nějakému problému na straně uživatele. Heslo se kontroluje hned po jeho zadání, ne až po potvrzení odstranění účtu
        echo "swal('Špatné heslo.','','error')";
        die();
    }
