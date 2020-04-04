<?php
    if (session_status() == PHP_SESSION_NONE)
    {
        include 'included/httpStats.php'; //Statistika se zaznamenává, pouze pokud je skript zavolán jako AJAX
        session_start(); //Session se startuje, pouze pokud je skript zavolán jako AJAX
    }
    echo "<table id='listTable'>
                <tr  class='main_tr'>
                    <td>Název třídy</td>
                    <td>Poznávačky</td>
                    <td></td>
                </tr>
            ";
    
    $userId = $_SESSION['user']['id'];
    $query = "SELECT * FROM `tridy` WHERE smazana = 0 AND (status = 'public' OR tridy_id IN (SELECT tridy_id FROM clenstvi WHERE uzivatele_id = $userId));";
    $result = mysqli_query($connection, $query);
    if (mysqli_num_rows($result) === 0)
    {
        echo '<tr class="infoRow">';
        echo '<td class="listNames listEmpty" colspan=3>Zatím nemáte přístup do žádné třídy.</td>';
        echo '</tr>';
    }
    while ($info = mysqli_fetch_array($result))
    {
        echo '<tr class="listRow" onclick="choose(event,1,'.$info['tridy_id'].')">';
        echo '<td class="listNames">'.$info['nazev'].'</td>';
        echo '<td class="listNames">'.$info['skupiny'].'</td>';
        if ($userId == $info['spravce'])
        {
            echo '<td class="listNaturals" onclick=""><a href="classManagement.php?cId='.$info['tridy_id'].'"><button id="listAction" class="button">Správa třídy</button></a></td>';
        }
        else if ($info['status'] !== 'public')
        {
            echo '<td class="listNaturals" onclick=""><button id="listAction" class="button" onclick="leaveClass('.$info['tridy_id'].')">Opustit třídu</button></a></td>';
        }
        else
        {
            echo '<td></td>';
        }
        echo '</tr>';
    }
    echo "</table>
    <button class='button' onclick='newClass()'>Zažádat o vytvoření nové třídy</button>
    <div id='classCodeBtn' style='display:block;'>
        <button class='button' onclick='enterClassCode()'>Zadat kód soukromé třídy</button>
    </div>
    <div id='classCodeForm' style='display:none;'>
        <input id='classCodeInput' type=text maxlength=4 class='text' style='width: 3.2rem;'/>
        <button class='button' onclick='submitClassCode()'>OK</button>
        <button class='button' onclick='closeClassCode()'>Zpět</button>
    </div>";
    
    //Získávání pozvánek
    $query = "SELECT tridy.tridy_id,tridy.nazev,(SELECT jmeno FROM tridy JOIN uzivatele ON uzivatele.uzivatele_id = tridy.spravce WHERE tridy_id = pozvanky.tridy_id) AS 'spravce' FROM pozvanky JOIN tridy ON tridy.tridy_id = pozvanky.tridy_id WHERE pozvanky.uzivatele_id = $userId ORDER BY pozvanky.expirace;";
    $result = mysqli_query($connection, $query);
    echo "
    <div id='invitationsButton'>
        <button class='button' onclick='showInvitations()'>Pozvánky do tříd (".mysqli_num_rows($result).")</button>
    </div>";
    if (mysqli_num_rows($result) > 0)
    {
        echo "
        <div id='invitationsContent'>
            <table id='invitationsTable'>
                <tr>
                    <th>Název třídy</th>
                    <th>Správce</th>
                    <th>Akce</th>
                </tr>
        ";
        while ($invitation = mysqli_fetch_array($result))
        {
            echo "
            <tr>
                <td>".$invitation['nazev']."</td>
                <td>".$invitation['spravce']."</td>
                <td>
                    <button class='actionButton' onclick='acceptInvitation(event, ".$invitation['tridy_id'].")' title='Přijmout pozvánku'><img src='images/tick.gif'/></button>
                    <button class='actionButton' onclick='declineInvitation(event, ".$invitation['tridy_id'].")' title='Odebrat pozvánku'><img src='images/cross.gif'/></button>
                </td>
            </tr>
            ";
        }
        echo "
            </table>
        </div>
        ";
    }
    
    //Vymazat zvolenou třídu ze $_SESSION
    $_SESSION['class'] = 0;
    
    //Aktualizovat uživateli poslední prohlíženou složku
    $userId = $_SESSION['user']['id'];
    $userId = mysqli_real_escape_string($connection, $userId);
    $query = "UPDATE uzivatele SET posledni_uroven = 0, posledni_slozka = NULL WHERE uzivatele_id=$userId LIMIT 1";
    $result = mysqli_query($connection, $query);
    