<?php
    session_start();
    
    include '../included/httpStats.php'; //Zahrnuje connect.php
    include '../included/logger.php';
    
    $userId = $_SESSION['user']['id'];
    $userName = $_SESSION['user']['name'];
    $class = mysqli_real_escape_string($connection, $_GET['classId']);
    
    $query = "DELETE FROM pozvanky WHERE uzivatele_id = $userId AND tridy_id = $class LIMIT 1;";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "Vyskytla se chyba při práci s databází. Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html";
        die();
    }
    if ($_GET['accepted'] == 1 && mysqli_affected_rows($connection) === 1)
    {
        //Pozvánka byla přijata i nalezena (něco bylo smazáno)
        $query = "INSERT INTO clenstvi (uzivatele_id,tridy_id) VALUES ($userId,$class)";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "Vyskytla se chyba při práci s databází. Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html";
            die(mysqli_error($connection));
        }
        filelog("Uživatel $userName přijal pozvánku do třídy s ID $class.");
    }
    else if ($_GET['accepted'] == 0 && mysqli_affected_rows($connection) === 1)
    {
        filelog("Uživatel $userName odmítnul pozvánku do třídy s ID $class.");
    }