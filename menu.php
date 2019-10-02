<?php
	$redirectIn = false;
	$redirectOut = true;
	require 'verification.php';    //Obsahuje session_start();
	
	//Nastavování současné poznávačky
	$cookieData = @$_COOKIE['current'];
	$cookieData = explode('&',$cookieData);
	$pId = @$cookieData[0];
	$pName = @$cookieData[1];
	
	//Mazání cookie current
	setcookie("current", "", time()-3600);
	
	if (!empty($pId))	//Poznávačka zvolena
	{
		$pArr = array($pId, $pName);
		$_SESSION['current'] = $pArr;
	}
	else if (!isset($_SESSION['current']))	//Poznávačka nezvolena ani nenastavena --> přesměrování na stránku s výběrem
	{
		echo "<script type='text/javascript'>location.href = 'list.php';</script>";
	}
?>
<!DOCTYPE html>
<html>
    <head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width" />
		<link rel="stylesheet" type="text/css" href="css/css.css">
		<link rel="icon" href="favicon.ico">
		<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="/icon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="/icon-16x16.png">
		<link rel="manifest" href="/manifest.json">
		<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#ffc835">
		<meta name="theme-color" content="#ffffff">
        <title>Menu: <?php echo $_SESSION['current'][1]; ?></title>
    </head>
    <body>
    <div class="container">
        <header>  				
            <div id="menuHeading">
				<?php echo $_SESSION['current'][1]; ?>
				(<a href="list.php">Změnit</a>)
			</div>
        </header>
        <main id="main_menu">
    	    <a href="addPics.php">
	           <div id="btn1" class="menu" onclick="addPics()">Přidat obrázky</div>
	        </a>
	           <a href="learn.php">
	           <div id="btn2" class="menu" onclick="learn()">Učit se</div>
            </a>
            <a href="test.php">
	           <div id="btn3" class="menu" onclick="test()">Vyzkoušet se</div>
            </a>  
        </main>
    </div>
        <footer>
			<div id="help" class="footerOption"><a target='_blank' href="https://github.com/HonzaSTECH/Poznavacky/wiki">Nápověda</a></div>
			<div id="issues" class="footerOption" onclick="showLogin()"><a target='_blank' href="https://github.com/HonzaSTECH/Poznavacky/issues/new/choose">Nalezli jste problém?</a></div>
			<div class="footerOption"><a target='_blank' href='https://github.com/HonzaSTECH/Poznavacky/blob/master/TERMS_OF_SERVICE.md'>Podmínky služby</a></div>
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
