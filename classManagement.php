<?php
    $redirectIn = false;
    $redirectOut = true;
    require 'php/included/verification.php';    //Obsahuje session_start();
    
    //Test, zda je přihlášený uživatel skutečně správcem třídy
    $classId = mysqli_real_escape_string($connection, $_GET['cId']);
    $userId = mysqli_real_escape_string($connection, $_SESSION['user']['id']);
    $query = "SELECT COUNT(*) AS 'cnt' FROM tridy WHERE tridy_id = $classId AND spravce = $userId";
    $result = mysqli_query($connection, $query);
    $result = mysqli_fetch_array($result)['cnt'];
    if (!$result > 0)
    {
        //Zamítnutí přístupu
        header('Location: err403.html');
        die();
    }
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
		<style>
	        #changeNameInput, #inviteForm, #deleteClassInput1, #deleteClassInput2 {
		      display: none;
		    }
		</style>
    </head>
    <body>
        <div class="container">
        <header>
            <h1>Správa třídy</h1>
        </header>
        <?php
            $query = "SELECT * FROM tridy WHERE tridy_id = $classId LIMIT 1";
            $result = mysqli_query($connection, $query);
            $classData = mysqli_fetch_array($result);
        ?>
        <main class="basic_main">
            <table id="static_info">
                <tr>
                    <td class='table_left'>ID třídy</td>
                    <td class='table_right' id="id"><?php echo $classData['tridy_id']; ?></td>
                    <td class='table_action'></td>
                </tr>
                <tr>
                    <td class='table_left'>Název třídy</td>
                    <td class='table_right' id="name"><?php echo $classData['nazev']; ?></td>
                    <td class='table_action'>
                        <button id="changeNameButton" class="button" onclick="requestNameChange()">Vyžádat změnu</button>
                        <div id="changeNameInput">
                            <input class="text" id="changeNameInputField" type=text placeholder="Nový název" maxlength=31 />
                            <button class="button" id="changeNameConfirm" onclick="confirmNameChange()">Potvrdit</button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class='table_left'>Stav třídy</td>
                    <td class='table_right' id="status">
                        <select id="statusInput" onchange="statusChange()">
                        <?php
                            switch ($classData['status'])
                            {
                                case 'public':
                                    echo "<option value='Veřejná' selected title='Do veřejných tříd mají přístup všichni přihlášení uživatelé.'>Veřejná</option>";
                                    echo "<option value='Soukromá' title='Do soukromých tříd mohou vstoupit pouze uživatelé, kteří alespoň jednou zadali platný vstupní kód.'>Soukromá</option>";
                                    echo "<option value='Uzamčená' title='Do uzamčených tříd nemohou vstoupit žádní uživatelé, kteří nedostali pozvánku.'>Uzamčená</option>";
                                    break;
                                case 'private':
                                    echo "<option value='Veřejná' title='Do veřejných tříd mají přístup všichni přihlášení uživatelé.'>Veřejná</option>";
                                    echo "<option value='Soukromá' selected title='Do soukromých tříd mohou vstoupit pouze uživatelé, kteří alespoň jednou zadali platný vstupní kód.'>Soukromá</option>";
                                    echo "<option value='Uzamčená' title='Do uzamčených tříd nemohou vstoupit žádní uživatelé, kteří nedostali pozvánku.'>Uzamčená</option>";
                                    break;
                                case 'locked':
                                    echo "<option value='Veřejná' title='Do veřejných tříd mají přístup všichni přihlášení uživatelé.'>Veřejná</option>";
                                    echo "<option value='Soukromá' title='Do soukromých tříd mohou vstoupit pouze uživatelé, kteří alespoň jednou zadali platný vstupní kód.'>Soukromá</option>";
                                    echo "<option value='Uzamčená' selected title='Do uzamčených tříd nemohou vstoupit žádní uživatelé, kteří nedostali pozvánku.'>Uzamčená</option>";
                                    break;
                            }
                        ?>
                        </select>
                    </td>
                    <td class='table_action'>
                    	<div id='statusCodeInput'>
                    		<span>Vstupní kód: </span>
                        	<input id='statusCodeInputField' type=text maxlength=4 value=<?php echo $classData['kod']; ?> style='width: 2rem;' oninput="statusChange()"/>
                        </div>
                        <button id="statusSaveButton" class="button" onclick="confirmStatusChange()">Aktualizovat</button>
                    </td>
                </tr>
                <tr>
                    <td class='table_left'>Členové třídy</td>
                    <td id='membersCell' class='table_right' colspan=2>
                    	<?php
                	       include 'php/getMembers.php';
                    	?>
                    </td>
                    <td class='table_action'></td>
                </tr>
            </table>
            
            <button class="button" id="deleteClassButton" onclick="deleteClass()">Zrušit třídu</button>
			<div id="deleteClassInput1">
				<input class="text" id="deleteClassInputField" type=password placeholder="Zadejte své heslo" maxlength=31 />
				<button class="button" id="deleteClassConfirm" onclick="deleteClassVerify()">OK</button>
			</div>
			<div id="deleteClassInput2">
				<span>Tato akce je nevratná. Opravdu si přejete trvale odstranit tuto třídu, včetně všech poznávaček, přířodnin a obrázků, které do ní patří?</span><br>
				<button class="button" id="deleteClassFinalConfirm" onclick="deleteClassFinal()">Ano, zrušit třídu</button>
				<button class="button" id="deleteClassFinalCancel" onclick="deleteClassCancel()">Ne, zachovat třídu</button>
			</div>
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