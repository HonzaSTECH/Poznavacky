<?php
    include '../included/httpStats.php';
    include '../emailSender.php';
    include '../included/composeEmail.php';
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
    
    $action = $_POST['acc'];
    $oldName = $_POST['oldName'];
    $newName = NULL;
    
    $oldName = mysqli_real_escape_string($connection, $oldName);
    
    if ($action === "true")
    {
        $newName = $_POST['newName'];
        $newName = mysqli_real_escape_string($connection, $newName);
        
        //Změna jména
        $query = "UPDATE tridy SET nazev = '$newName' WHERE nazev = '$oldName'";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "alert('Nastala chyba SQL: ".mysqli_error($connection)." ".$query."');";
            die();
        }
        
        //Odeslat správci třídy e-mail informující o změně jména
        $query = "SELECT email FROM uzivatele WHERE uzivatele_id = (SELECT spravce FROM tridy WHERE nazev = '$newName' LIMIT 1) LIMIT 1;";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "alert(Nastala chyba SQL: ".mysqli_error($connection)." ".$query.");";
            die();
        }
        $email = mysqli_fetch_array($result)['email'];
        $emailResult = sendEmail($email, 'Název vámi spravované třídy byl změněn', getEmail(3, array("oldName" => $oldName, "newName" => $newName)));
        
        if (!empty($emailResult))
        {
            echo "alert(Automatický e-mail nemohl být odeslán. Chyba: $emailResult);";
        }
    }
    else
    {
        $reason = $_POST['msg'];
        
        //Odeslat správci třídy e-mail informující o zamítnutí žádosti.
        $query = "SELECT email FROM uzivatele WHERE uzivatele_id = (SELECT spravce FROM tridy WHERE nazev = '$oldName' LIMIT 1) LIMIT 1;";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "alert(Nastala chyba SQL: ".mysqli_error($connection)." ".$query.");";
            die();
        }
        $email = mysqli_fetch_array($result)['email'];
        $emailResult = sendEmail($email, 'Žádost o změnu názvu vámi spravované třídy byla zamítnuta', getEmail(4, array("oldName" => $oldName, "reason" => $reason)));
        
        if (!empty($emailResult))
        {
            echo "alert(Automatický e-mail nemohl být odeslán. Chyba: $emailResult);";
        }
        
        $newName = $oldName;
    }
    
    //Odstraňování žádosti
    $query = "DELETE FROM zadosti_jmena_tridy WHERE tridy_id = (SELECT tridy_id FROM tridy WHERE nazev = '$newName' LIMIT 1);";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert(Nastala chyba SQL: ".mysqli_error($connection)." ".$query.");";
    }
