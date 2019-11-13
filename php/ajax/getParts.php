<?php
    include '../included/httpStats.php';
    
    $groupId = $_GET['groupId'];
    
    $groupId = mysqli_real_escape_string($connection, $groupId);
    
    //Získání id třídy pro tlačítko návrat
    $query = "SELECT tridy.id FROM tridy INNER JOIN poznavacky ON poznavacky.trida = tridy.id WHERE poznavacky.id = $groupId";
    $result = mysqli_query($connection, $query);
    $result = mysqli_fetch_array($result);
    $classId = $result['id'];
    
    echo "<table id='listTable'>
        <tr class='listRow' onclick='choose(0, $classId)'>
            <td class='listBack' colspan=3><i>Zpět na seznam poznávaček</i></td>
        </tr>
        <tr>
            <th>Název části</th>
            <th>Přírodniny</th>
            <th>Obrázky</th>
        </tr>
        ";
    
    $query = "SELECT * FROM casti WHERE poznavacka = $groupId";
    $result = mysqli_query($connection, $query);
    if (mysqli_num_rows($result) === 0)
    {
        echo '<tr class="infoRow">';
        echo '<td class="listNames" colspan=2>V této poznávačce zatím nejsou žádné skupiny</td>';
        echo '</tr>';
    }
    while ($info = mysqli_fetch_array($result))
    {
        $txt = "choose(2,".$info['id'].")";
        echo "<tr class='listRow' onclick=$txt>";
        echo '<td class="listNames">'.$info['nazev'].'</td>';
        echo '<td class="listNaturals">'.$info['prirodniny'].'</td>';
        echo '<td class="listPictures">'.$info['obrazky'].'</td>';
        echo '</tr>';
    }
    echo "</table>";