<?php
    session_start();
    
    include '../included/httpStats.php'; //Zahrnuje connect.php
    include '../included/logger.php';
    
    $cId = mysqli_real_escape_string($connection, $_POST['id']);
    $invited = mysqli_real_escape_string($connection, $_POST['name']);
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
    
    echo "alert('Uživatel pozván.');";
    //TODO