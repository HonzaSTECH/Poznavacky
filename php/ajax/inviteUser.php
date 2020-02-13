<?php
    session_start();
    
    include '../included/httpStats.php'; //Zahrnuje connect.php
    include '../included/logger.php';
    
    $cId = mysqli_real_escape_string($connection, $_POST['id']);
    $invited = $_POST['name'];
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
    
    //Kontrola délky jména
    if (mb_strlen($invited) < 6)
    {
        filelog("Uživatel $userName se pokusil pozvat uživatele do třídy s ID $cId, avšak neuspěl kvůli příliš krátkému jménu.");
        echo "alert('Jméno uživatele musí být alespoň 6 znaků dlouhé.');";
        die();
    }
    if (mb_strlen($invited) > 31)
    {
        filelog("Uživatel $userName se pokusil pozvat uživatele do třídy s ID $cId, avšak neuspěl kvůli příliš dlouhému jménu.");
        echo "alert('Jméno uživatele nesmí být více než 31 znaků dlouhé.');";
        die();
    }
    
    //Kontrola znaků ve jméně
    if(strlen($invited) !== strspn($invited, '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ '))
    {
        filelog("Uživatel $userName se pokusil pozvat uživatele do třídy s ID $cId, avšak neuspěl kvůli neplatným znakům ve jméně.");
        echo "alert('Jméno uživatele může obsahovat pouze písmena, číslice a mezery.');";
        die();
    }
    
    //KONTROLA DAT OK
    
    //Ochrana před SQL injekcí
    $invited = mysqli_real_escape_string($connection, $invited);
    
    //Kalkulace data expirace pozvánky
    $date = date('Y-m-d H:i:s', time() + 604800);   //604 800 s = 1 týden
    
    //Zjištění ID pozvaného uživatele
    $query = "SELECT uzivatele_id FROM uzivatele WHERE jmeno = '$invited' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Vyskytla se chyba při práci s databází. Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html');";
        die();
    }
    if (mysqli_num_rows($result) > 0)
    {
        $invited = mysqli_fetch_array($result)['uzivatele_id'];
    }
    else
    {
        echo "alert('Uživatel se zadaným jménem nebyl nalezen.');";
        die();
    }
    
    //Kontrola, zda již uživatel není do třídy pozván
    $query = "SELECT pozvanky_id FROM pozvanky WHERE uzivatele_id = $invited AND tridy_id = $cId";
    $result = mysqli_query($connection, $query);    
    if (mysqli_num_rows($result) === 0)
    {
        //Přidání pozvánky
        $query = "INSERT INTO pozvanky (uzivatele_id,tridy_id,expirace) VALUES ($invited, $cId, '$date')";
        $result = mysqli_query($connection, $query);
    }
    else
    {
        //Obnovení současné pozvánky
        $invitationId = mysqli_fetch_array($result)['pozvanky_id'];
        $query = "UPDATE pozvanky SET expirace = '$date' WHERE pozvanky_id = $invitationId";
        $result = mysqli_query($connection, $query);
    }
    
    if (!$result)
    {
        echo "alert('Vyskytla se chyba při práci s databází. Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html ".$query."');";
        die();
    }
    else
    {
        echo "alert('Pozvánka úspěšně odeslána.');";
        
        //Reset HTML
        echo "document.getElementById('inviteUserInput').value = '';";
        echo "inviteFormHide();";
    }
    
    $ip = $_SERVER['REMOTE_ADDR'];
    filelog("Uživatel $userName pozval uživatele $invited do třídy s ID $cId z IP adresy $ip.");