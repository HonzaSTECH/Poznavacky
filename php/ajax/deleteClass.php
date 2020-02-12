<?php
    session_start();
    
    include '../included/httpStats.php'; //Zahrnuje connect.php
    include '../included/logger.php';
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $loggedUserName = $_SESSION['user']['name'];
    $cId = $_POST['id'];
    $password = $_POST['oldPass'];
    
    //Ochrana proti SQL injekci
    $cId = mysqli_real_escape_string($connection, $cId);
    
    //Získání jména a hashe hesla správce třídy, kterou se pokoušíme zrušit
    $query = "SELECT uzivatele.jmeno,uzivatele.heslo FROM uzivatele WHERE uzivatele.uzivatele_id = (SELECT tridy.spravce FROM tridy WHERE tridy.tridy_id = $cId LIMIT 1) LIMIT 1;";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo $query;
        fileLog("Uživatel se pokusil odstranit třídu s ID $cId z IP adresy $ip, ale došlo k chybě databáze.");
        
        echo "alert('Něco se pokazilo. Zkuste to prosím později a pokud chyba přetrvává, kontaktujte správce.)";
        die();
    }
    
    //Kontrola správnosti hesla
    $result = mysqli_fetch_array($result);
    if (password_verify($password, $result['heslo']))   //Heslo je správné
    {
        //Kontrola, zda je uživatel správcem třídy
        $admin = $result['jmeno'];
        if ($admin === $loggedUserName)
        {
            $query = "UPDATE uzivatele SET posledni_uroven = 0, posledni_slozka = NULL WHERE posledni_uroven = 1 AND posledni_slozka = $cId;"; //Přesměrování všech uživatelů nacházejících se na list.php na seznamu poznávaček smazané třídy zpět na seznam tříd
            $query .= "UPDATE uzivatele SET posledni_uroven = 0, posledni_slozka = NULL WHERE posledni_uroven = 2 AND posledni_slozka IN (SELECT poznavacky_id FROM poznavacky WHERE tridy_id = $cId);"; //Přesměrování všech uživatelů nacházejících se na list.php na seznamu částí, které jsou součástí poznávaček patřících do smazané třídy
            $query .= "UPDATE tridy SET smazana = 1 WHERE tridy_id = $cId LIMIT 1;";  //Odstranění třídy jejím označením jako smazané
            
            $result = mysqli_multi_query($connection, $query);
            if (!$result)
            {
                echo "alert('Něco se pokazilo. Zkuste to prosím později a pokud chyba přetrvává, kontaktujte správce.)";
                echo "location.href = 'errSql.html';";
                
                $err = mysqli_error($connection);
                filelog("Vyskytla se SQL chyba při odstraňování třídy: $err.");
                
                die();
            }
            else
            {
                filelog("Uživatel $loggedUserName zrušil třídu s ID $cId z IP adresy $ip.");
                echo "location.href = 'list.php';";
            }
        }
        else
        {
            filelog("Uživatel $loggedUserName se pokusil zrušit třídu s ID $cId, ale není jejím správcem.");
            echo "alert('Nejste správcem dané třídy!');";
        }
    }
    else
    {
        //Toto by se nemělo zobrazit, pokud nedojde k nějakému problému na straně uživatele. Heslo se kontroluje hned po jeho zadání, ne až po potvrzení odstranění účtu
        echo "alert('Špatné heslo.','','error');";
        die();
    }