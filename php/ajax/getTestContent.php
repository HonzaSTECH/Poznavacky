<?php
    session_start();
    
    include '../included/httpStats.php'; //Zahrnuje connect.php
    
    $cId = mysqli_real_escape_string($connection, $_POST['id']);
    $tId = mysqli_real_escape_string($connection, $_POST['code']);
    $userId = $_SESSION['user']['id'];
    
    //Ověření uživatele
    $query = "SELECT spravce FROM tridy WHERE tridy_id=$cId";
    $result = mysqli_query($connection, $query);
    $result = mysqli_fetch_array($result)['spravce'];
    
    if ($result !== $userId)
    {
        //Zamítnutí přístupu
        die("alert('Přístup zamítnut!')");
    }
    
    echo '<table id="testEditorTable">';
    echo '<tr>';
    
    echo '<td>';
    echo '<table id="testEditorPartsTable">';
    $query = "SELECT casti_id,nazev FROM casti WHERE poznavacky_id = $tId";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        die("Vyskytla se chyba při práci s databází. Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html");
    }
    while ($row = mysqli_fetch_array($result))
    {
        $partName = $row['nazev'];
        $partId = $row['casti_id'];
        echo '<tr>';
            echo "<td>$partName</td>";
            echo '<td>';
                echo '<button class="actionButton" onclick="showNaturals('.$partId.')" title="Zobrazit přírodniny"><img src="images/list.gif"/></button>';
                echo '<button class="actionButton" onclick="renamePart(event)" title="Přejmenovat část"><img src="images/pencil.svg"/></button>';
                echo '<button class="actionButton" onclick="removePart(event)" title="Odstranit část (přírodniny i jejich obrázky zůstanou zachovány)"><img src="images/cross.svg"/></button>';
            echo '</td>';
        echo '</tr>';
    }
    echo '<tr><td cellspan=2><button class="actionButton" onclick="addPart()" title="Přidat část"><img src="images/plus.svg"></button></td></tr>';
    echo '</table>';    //id="testEditorPartsTable"
    echo '</td>';
    
    echo '<td>';
    echo '<table id="testEditorNaturalsTable">';
    $query = "SELECT prirodniny.prirodniny_id, prirodniny.nazev AS 'nazev_prirodnina', prirodniny.obrazky, casti.casti_id, casti.nazev AS 'nazev_cast' FROM prirodniny JOIN casti ON casti.casti_id = prirodniny.casti_id WHERE prirodniny.poznavacky_id = $tId";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo mysqli_error($connection);
        die("Vyskytla se chyba při práci s databází. Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html");
    }
    echo '<tr class="testEditorNaturalRow" style="display:block;"><td cellspan=4>Pro zobrazení přírodnin klikněte na "Zobrazit přírodniny" u některé z částí vlevo.</td></tr>';
    while ($row = mysqli_fetch_array($result))
    {
        $naturalId = $row['prirodniny_id'];
        $naturalName = $row['nazev_prirodnina'];
        $picCount = $row['obrazky'];
        $partId = $row['casti_id'];
        $partName = $row['nazev_cast'];
        
        echo '<tr class="testEditorNaturalRow part_id_'.$partId.'">';
            echo "<td>$naturalName</td>";
            echo "<td>$picCount</td>";
            echo '<td>';
                echo '<select>';
                    echo "<option value=$partId>$partName</option>";
                    //TODO - vypsat všechny části v poznávačce
                echo '</select>';
            echo '</td>';
            echo '<td>';
                echo '<button class="actionButton" onclick="renameNatural(event)" title="Přejmenovat přírodninu"><img src="images/pencil.svg"/></button>';
                echo '<button class="actionButton" onclick="removeNatural(event)" title="Odstranit přírodninu a všechny její obrázky"><img src="images/cross.svg"/></button>';
            echo '</td>';
        echo '</tr>';
    }
    echo '</table>';    //id="testEditorNaturalsTable"
    echo '</td>';
    
    echo '</tr>';
    echo '</table>';    //id="testEditorTable"