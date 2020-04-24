<?php
    include '../included/httpStats.php';
    session_start();
    
    //Kontrola, zda je uživatel administrátorem.
    $username = $_SESSION['user']['name'];
    $query = "SELECT status FROM uzivatele WHERE jmeno='$username' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        header("Location: errSql.html");
        die();
    }
    $status = mysqli_fetch_array($result)['status'];
    if ($status !== 'admin')
    {
        //Zamítnutí přístupu
        die();
    }
    
    //Získání dat
    $picId = $_POST['to'];
    $reason = $_POST['sub'];
    $info = $_POST['msg'];
    
    //Pokud je v $info uložený časový údaj, odkódujeme symbol '>'
    if ($reason === '1')
    {
        $info = str_replace('&gt;', '>', $info);
    }
    
    $picId = mysqli_real_escape_string($connection, $picId);
    $reason = mysqli_real_escape_string($connection, $reason);
    $info = mysqli_real_escape_string($connection, $info);
    
    //Odstranění hlášení
    $query = "DELETE FROM hlaseni WHERE obrazky_id='$picId' AND duvod=$reason AND dalsi_informace='$info' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }