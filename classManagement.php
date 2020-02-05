<?php
    $redirectIn = false;
    $redirectOut = true;
    require 'php/included/verification.php';    //Obsahuje session_start();
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width" />
        <link rel="stylesheet" type="text/css" href="css/css.css">
        <style>
            <?php 
                require 'php/included/themeHandler.php';
            ?>
        </style>
        <script type="text/javascript" src="jScript/classManagement.js"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <link rel="icon" href="images/favicon.ico">
        <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="images/icon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="images/icon-16x16.png">
        <link rel="manifest" href="manifest.json">
        <link rel="mask-icon" href="images/safari-pinned-tab.svg" color="#ffc835">
        <meta name="theme-color" content="#ffffff">
        <title>Správa třídy</title>
    </head>
    <body>
        <div class="container">
        <header>
            <h1>Správa třídy</h1>
        </header>
        <main class="basic_main">
            <table id="static_info">
                <tr>
                    <td class='table_left'>Vlastnost</td>
                    <td class='table_right' id="username">Hodnota</td>
                    <td class='table_action'>
                        <button class="button">Akce</button>
                    </td>
                </tr>
            </table>
            <br>
            
            <a href="list.php"><button class="button">Zpět</button></a>
        </main>
        <footer>
            <div id="help" class="footerOption"><a target='_blank' href="https://github.com/HonzaSTECH/Poznavacky/wiki">Nápověda</a></div>
            <div id="issues" class="footerOption" onclick="showLogin()"><a target='_blank' href="https://github.com/HonzaSTECH/Poznavacky/issues/new/choose">Nalezli jste problém?</a></div>
            <div class="footerOption"><a target='_blank' href='https://github.com/HonzaSTECH/Poznavacky/blob/master/documents/TERMS_OF_SERVICE.md'>Podmínky služby</a></div>
            <div id="about" class="footerOption">&copy Štěchy a Eksyska, 2019</div>
             <script>
                 function showLogin()
                 {
                     alert("Přihlašovací údaje pro nahlašování chyby:\nJméno: gjvj\nHeslo: poznavacky71");
                 }
             </script>
         </footer>
    </body>
</html>