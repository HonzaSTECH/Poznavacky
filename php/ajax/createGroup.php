<?php
    session_start();
    
    include '../included/httpStats.php'; //Zahrnuje connect.php
    include '../included/logger.php';
    
    $cId = mysqli_real_escape_string($connection, $_POST['id']);
    $groupName = $_POST['name'];
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
    if (mb_strlen($groupName) < 4)
    {
        filelog("Uživatel $userName se pokusil vytvořit poznávačku ve třídě s ID $cId, avšak neuspěl kvůli příliš krátkému názvu.");
        echo "alert('Název poznávačky musí být alespoň 4 znaky dlouhý.');";
        die();
    }
    if (mb_strlen($groupName) > 31)
    {
        filelog("Uživatel $userName se pokusil vytvořit poznávačku ve třídě s ID $cId, avšak neuspěl kvůli příliš dlouhému názvu.");
        echo "alert('Název poznávačky nesmí být více než 31 znaků dlouhý.');";
        die();
    }
    
    //Kontrola znaků ve jméně
    if(strlen($groupName) !== strspn($groupName, '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ():;,._ '))
    {
        filelog("Uživatel $userName se pokusil vytvořit poznávačku ve třídě s ID $cId, avšak neuspěl kvůli neplatným znakům v názvu.");
        echo "alert('Název poznávačky může obsahovat pouze písmena, číslice, mezery a znaky . , ; : _ ( ).');";
        die();
    }
    
    //KONTROLA DAT OK
    
    //Ochrana před SQL injekcí
    $groupName = mysqli_real_escape_string($connection, $groupName);
    
    //Zkouška, zda již ve třídě takto pojmenovaná poznávačka existuje
    $query = "SELECT COUNT(*) AS 'cnt' FROM poznavacky WHERE nazev = '$groupName' AND tridy_id = $cId";
    $result = mysqli_query($connection, $query);
    $result = mysqli_fetch_array($result);
    if (!empty($result['cnt']))
    {
        filelog("Uživatel $userName se pokusil vytvořit poznávačku ve třídě s ID $cId, avšak neuspěl kvůli duplicitnímu názvu.");
        echo "alert('Takto pojmenovaná poznávačka již ve vaší třídě existuje.');";
        die();
    }
    
    $query = "INSERT INTO poznavacky (nazev,casti,tridy_id) VALUES ('$groupName', 0, $cId)";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Vyskytla se chyba při práci s databází. Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html');";
        die();
    }
    
    filelog("Uživatel $userName vytvořil novou poznávačku ve třídě s ID $cId.");