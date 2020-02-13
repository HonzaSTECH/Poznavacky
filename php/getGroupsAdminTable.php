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
    }
    
    $query = "SELECT poznavacky_id,nazev,casti FROM poznavacky WHERE tridy_id = $classId";
    $result = mysqli_query($connection, $query);
    if (mysqli_num_rows($result) > 0)
    {
        echo "<table id='testsTable'>";
        echo "
            <tr>
                <th>Název</th>
                <th>Části</th>
                <th>Akce</th>
            </tr>
            ";
        while ($test = mysqli_fetch_array($result))
        {
            echo "
                <tr class='testsTableRow'>
                    <td class='testsTableCell'>
                        ".$test['nazev']."
                    </td>
                    <td class='testsTableCell'>
                        ".$test['casti']."
                    </td>
                    <td class='testsTableCell'>
                        <button class='actionButton' onclick='editTest(event,".$test['poznavacky_id'].")' title='Upravit poznávačku'><img src='images/pencil.gif'></button>
                        <button class='actionButton' onclick='deleteTest(event,".$test['poznavacky_id'].")' title='Odstranit poznávačku'><img src='images/cross.gif'/></button>
                    </td>
                </tr>
                ";
        }
        echo "</table>";
        echo "
        <button class='actionButton' onclick='createTest()' title='Vytvořit novou poznávačku'><img src='images/plus.gif'></button>
        <div id='createForm'>
            <div id='createInfo'>Zadejte název nové poznávačky. Do poznávačky budete moci přidat části a přírodniny později.</div>
            <input id='createInput' placeholder='Název poznávačky' type='text' maxlength=31>
            <button onclick='createTestSubmit()' class='button'>Vytvořit</button>
            <button onclick='createTestHide()' class='button'>Zrušit</button>
        </div>
        ";
    }