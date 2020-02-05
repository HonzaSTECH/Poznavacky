<?php
    session_start();
    
    include '../included/httpStats.php'; //Zahrnuje connect.php
    include '../included/logger.php';
    
    $classId = $_GET['cId'];
    $userId = $_SESSION['user']['id'];
    $username = $_SESSION['user']['name'];
    
    $classId = mysqli_real_escape_string($connection, $classId);
    $userId = mysqli_real_escape_string($connection, $userId);
    
    $query = "DELETE FROM clenstvi WHERE uzivatele_id = $userId AND tridy_id = $classId LIMIT 1;";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        die('Během práce s databází došlo k chybě. Kontaktujte prosím správce.');
    }
    
    fileLog("Uživatel $username opustil třídu s ID $classId.");