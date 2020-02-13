<?php
    session_start();
    
    include '../included/httpStats.php'; //Zahrnuje connect.php
    include '../included/logger.php';
    
    $cId = mysqli_real_escape_string($connection, $_POST['id']);
    $name = $_POST['name'];
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
    if (mb_strlen($name) < 6)
    {
        filelog("Uživatel $userName se pokusil změnit jméno třídy s ID $cId, avšak neuspěl kvůli příliš krátkému jménu.");
        echo "alert('Jméno třídy musí být alespoň 6 znaků dlouhé.')";
        die();
    }
    if (mb_strlen($name) > 31)
    {
        filelog("Uživatel $userName se pokusil změnit jméno třídy s ID $cId, avšak neuspěl kvůli příliš dlouhému jménu.");
        echo "alert('Jméno nesmí být více než 31 znaků dlouhé.')";
        die();
    }
    
    //Kontrola znaků ve jméně
    if(strlen($name) !== strspn($name, '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ():;,._ '))
    {
        filelog("Uživatel $userName se pokusil změnit jméno třídy s ID $cId, avšak neuspěl kvůli neplatným znakům ve jméně.");
        echo "alert('Jméno třídy může obsahovat pouze písmena, číslice, mezery a znaky . , ; : _ ( ).')";
        die();
    }
    
    //Ochrana před SQL injekcí
    $name = mysqli_real_escape_string($connection, $name);
    
    //Kontrola unikátnosti jména
    $query = "SELECT tridy_id FROM tridy WHERE nazev='$name' UNION SELECT tridy_id FROM zadosti_jmena_tridy WHERE nove='$name' LIMIT 1;";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "swal('Vyskytla se chyba při práci s databází.','Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html','error')";
        die();
    }
    if (mysqli_num_rows($result) > 0)
    {
        filelog("Uživatel $userName se pokusil změnit jméno třídy s ID $cId, avšak neuspěl kvůli neunikátnímu jménu.");
        echo "alert('Takto je již pojmenována jiná třída, nebo o změnu na toto jméno již někdo zažádal.')";
        die();
    }
    
    //KONTROLA DAT V POŘÁDKU
    
    //Kontrola, zda již třída na nějakou nevyřízenou změnu nečeká
    $query = "SELECT zadosti_jmena_tridy_id FROM zadosti_jmena_tridy WHERE tridy_id=$cId LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "swal('Vyskytla se chyba při práci s databází.','Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html','error')";
        die();
    }
    if (mysqli_num_rows($result) > 0)
    {
        $requestId = mysqli_fetch_array($result)['zadosti_jmena_tridy_id'];
        
        //Přepisování žádosti
        $query = "UPDATE zadosti_jmena_tridy SET nove = '$name', cas = ".time()." WHERE zadosti_jmena_tridy_id = $requestId;";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "alert('Vyskytla se chyba při práci s databází. Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html');";
            die();
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        filelog("Uživatel $userName změnil žádost o nové jméno třídy s ID $cId na $name z IP adresy $ip.");
        echo "alert('O změnu jména bylo zažádáno. Nové jméno bude co nejdříve zkontrolováno a případně nahradí stávající jméno třídy. Jakmile bude vaše žádost vyřízena, obdržíte oznámění e-mailem (pokud jste jej přidali do svého účtu). Tato žádost o změnu přepsala vaší nevyřízenou žádost o změnu jména z minulosti.');";
    }
    else
    {
        //Ukládání žádosti
        $query = "INSERT INTO zadosti_jmena_tridy (tridy_id, nove, cas) VALUES ($cId, '$name', ".time().")";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "alert('Vyskytla se chyba při práci s databází. Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html');";
            die();
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        filelog("Uživatel $userName zažádal o změnu jména třídy s ID $cId na $name z IP adresy $ip.");
        echo "alert('O změnu jména bylo zažádáno. Nové jméno bude co nejdříve zkontrolováno a případně nahradí stávající jméno třídy. Jakmile bude vaše žádost vyřízena, obdržíte oznámění e-mailem (pokud jste jej přidali do svého účtu).');";
    }