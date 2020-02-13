<?php
    if (session_status() == PHP_SESSION_NONE)
    {
        include 'included/httpStats.php';  //Statistika se zaznamenává, pouze pokud je skript zavolán jako AJAX
        session_start(); //Sezení se zahajuje pouze v případě, že již nebylo zahájeno
        
        $classId = mysqli_real_escape_string($connection, $_POST['id']);    //Pokud je skript zavolán pomocí 'include', je již proměnná přiřazena
    
        //Ověření, zda je uživatel správcem třídy (pokud je skript zavolán pomocí 'include', je již ověření provedeno)
        $userId = mysqli_real_escape_string($connection,$_SESSION['user']['id']);
        
        //Kontrola, zda má uživatel do třídy přístup
        $query = "SELECT COUNT(*) AS 'cnt' FROM tridy WHERE tridy_id = $classId AND spravce = $userId;";
        $result = mysqli_query($connection, $query);
        $result = mysqli_fetch_array($result);
        if ($result['cnt'] < 1)
        {
            //Odepření přístupu
            header('Location: ../err403.html');
            die();
        }
        
        //Získání informací o třídě (při 'include' použití jsou již stejným způsobem získána)
        $query = "SELECT * FROM tridy WHERE tridy_id = $classId LIMIT 1";
        $result = mysqli_query($connection, $query);
        $classData = mysqli_fetch_array($result);
    }
    
    //Kontrola, zda není třída nastavena jako veřejná
    if ($classData['status'] === 'public')
    {
        echo "<span>Funkce správy členů je dostupná pouze pro soukromé a uzamčené třídy.</span>";
    }
    else
    {
        echo "<table id='membersTable'>";
        echo "<th>Uživatelské jméno</th><th>Akce</th>";
    
    
        //Výběr všech uživatelů ve třídě
        $query = "SELECT uzivatele.jmeno FROM clenstvi JOIN uzivatele ON clenstvi.uzivatele_id = uzivatele.uzivatele_id WHERE clenstvi.tridy_id = $classId AND uzivatele.uzivatele_id != $userId ORDER BY uzivatele.posledni_prihlaseni DESC;";
        $result = mysqli_query($connection, $query);
        while ($user = mysqli_fetch_array($result))
        {
            echo "<tr class='membersTableRow'>";
            echo "<td class='membersTableCell'>".$user['jmeno']."</td>";
            echo "<td class='membersTableCell'>";
            echo "<button class='actionButton' onclick='kickUser(event)' title='Odstranit ze třídy'><img src='images/cross.gif'/></button>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "<button class='actionButton' onclick='inviteFormShow()' title='Pozvat nového člena'><img src='images/plus.gif'></button>";
        echo "
        <div id='inviteForm'>
            <div id='inviteUserInfo'>Pozvaný uživatel bude mít týden na přijmutí pozvánky. Pozvání nelze odvolat.</div>
            <input id='inviteUserInput' placeholder='Jméno uživatele' type='text' maxlength=31>
            <button onclick='inviteUser()' class='button'>Pozvat</button>
            <button onclick='inviteFormHide()' class='button'>Zrušit</button>
        </div>
        ";
    }