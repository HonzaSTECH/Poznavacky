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
    
    $query = "SELECT hlaseni.hlaseni_id, prirodniny.nazev, obrazky.obrazky_id, obrazky.zdroj, hlaseni.duvod, hlaseni.dalsi_informace, hlaseni.pocet FROM hlaseni JOIN obrazky ON hlaseni.obrazky_id = obrazky.obrazky_id JOIN prirodniny ON obrazky.prirodniny_id = prirodniny.prirodniny_id WHERE prirodniny.poznavacky_id = $tId ORDER BY pocet DESC;";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo mysqli_error($connection);
        die("Vyskytla se chyba při práci s databází. Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html");
    }
    
    echo '<table>';
    echo '<th>Přírodnina</th><th>Zdroj</th><th>Důvod</th><th>Další informace</th><th>Počet nahlášení</th><th>Akce</th>';
    while ($report = mysqli_fetch_array($result))
    {
        $reportId = $report['hlaseni_id'];
        $naturalName = $report['nazev'];
        $picId = $report['obrazky_id'];
        $picUrl = $report['zdroj'];
        $reason = $report['duvod'];
        $info = $report['dalsi_informace'];
        $count = $report['pocet'];
        
        echo '<tr>';
        echo "<td>$naturalName</td>";
        echo '<td><a href="'.$picUrl.'" target="_blank">'.$picUrl.'</a></td>';
        
        //Výpis důvodu
        echo '<td>';
        switch ($reason)
        {
            case 0:
                echo 'Obrázek se nezobrazuje správně';
                break;
            case 1:
                echo 'Obrázek se načítá příliš dlouho';
                break;
            case 2:
                echo 'Obrázek zobrazuje nesprávnou přírodninu';
                break;
            case 3:
                echo 'Obrázek obsahuje název přírodniny';
                break;
            case 4:
                echo 'Obrázek má příliš špatné rozlišení';
                break;
            case 5:
                echo 'Obrázek porušuje autorská práva';
                break;
            case 6:
                echo 'Jiný důvod';
        }
        echo '</td>';
        
        //Výpis přídavných informací
        $info = $report['dalsi_informace'];
        if ($reason == 6)
        {
            echo '<td><i title="'.$info.'">Najedďte sem myší pro zobrazení důvodu</i></td>';
        }
        else
        {
            echo "<td>$info</td>";
        }
        
        //Výpis počtu nahlášení
        echo "<td>$count</td>";
        
        //Výpis akcí
        echo '<td>';
            echo "<button class='reportAction activeBtn' onclick='showPicture(\"$picUrl\")' title='Zobrazit obrázek'>";
                echo '<img src="images/eye.gif"/>';
            echo '</button>';
            //echo "<button class='reportAction activeBtn' onclick='disablePicture(event, $picId)' title='Skrýt obrázek'>";
            //    echo '<img src="images/dot.gif"/>';
            //echo '</button>';
            echo "<button class='reportAction activeBtn' onclick='deletePicture(event, $picId)' title='Odstranit obrázek'>";
                echo '<img src="images/cross.gif"/>';
            echo '</button>';
            echo "<button class='reportAction activeBtn' onclick='deleteReport(event, $reportId)' title='Odstranit hlášení'>";
                echo '<img src="images/minus.gif"/>';
            echo '</button>';
        echo '</td>';
        
        echo '</tr>';
    }
    echo '</table>';