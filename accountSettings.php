<?php 
    $redirectIn = false;
    $redirectOut = true;
    require 'verification.php';    //Obsahuje session_start();

    $userdata = $_SESSION['user'];
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="css.css">
		<script type="text/javascript" src="accountSettings.js"></script>
		<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
		<title>Správa účtu</title>
		<style>
		  /*Toto jsou dočasné styly, které jsem vytvořil jenom provizorně.*/
		  /*Až to budeš stylovat, smaž tenhle blok a přesuň stylování do css.css*/
		  
		  #changeNameInput, #changeEmailInput, #changePasswordInput1, #changePasswordInput2, #changePasswordInput3, #deleteAccountInput1, #deleteAccountInput2{
		      display: none;
		  }
		</style>
	</head>
	<body>
	<div class="container">
		<header>
            <h1>Správa účtu</h1>
        </header>
		<main class="basic_main">
			<table id="static_info">
				<tr>
					<td class='table_left'>ID</td>
					<td class='table_right'><?php echo $userdata['id']; ?></td>
					<td class='table_action'><button disabled class="buttonDisabled">Nelze změnit</button></td>
				</tr>	
				<tr>
					<td class='table_left'>Jméno</td>
					<td class='table_right'><?php echo $userdata['name']; ?></td>
					<td class='table_action'>
						<button class="button" id="changeNameButton" onclick="changeName()">Vyžádat změnu</button>
						<div id="changeNameInput">
							<input class="text" id="changeNameInputField" type=text placeholder="Nové jméno" maxlength=15 />
							<button class="button" id="changeNameConfirm" onclick="confirmNameChange()">OK</button>
						</div>
					</td>
				</tr>
				<tr>
					<td class='table_left'>Heslo</td>
					<td class='table_right'>[Skryto]</td>
					<td class='table_action'>
						<button class="button" id="changePasswordButton" onclick="changePassword()">Změnit</button>
						<div id="changePasswordInput1">
							<input class="text" id="changePasswordInputFieldOld" type=password placeholder="Staré heslo" maxlength=31 />
							<button class="button" id="changePasswordNext1" onclick="changePasswordStage2()">Dále</button>
						</div>
						<div id="changePasswordInput2">
							<input class="text" id="changePasswordInputFieldNew" type=password placeholder="Nové heslo" maxlength=31 />
							<button class="button" id="changePasswordBack2" onclick="changePassword()">Zpět</button>
							<button class="button" id="changePasswordNext2" onclick="changePasswordStage3()">Dále</button>
						</div>
						<div id="changePasswordInput3">
							<input class="text" id="changePasswordInputFieldReNew" type=password placeholder="Nové heslo znovu" maxlength=31 />
							<button class="button" id="changePasswordBack3" onclick="changePasswordStage2()">Zpět</button>
							<button class="button" id="changePasswordConfirm" onclick="confirmPasswordChange()">Potvrdit</button>
						</div>
					</td>
				</tr>
				<tr>
					<td class='table_left'>E-mail</td>
					<td class='table_right' id="emailAddress"><?php echo $userdata['email']; ?></td>
					<td class='table_action'>
						<button class="button" id="changeEmailButton" onclick="changeEmail()">Změnit</button>
						<div id="changeEmailInput">
							<input class="text" id="changeEmailInputField" type=text placeholder="Nový e-mail" maxlength=255 />
							<button class="button" id="changeEmailConfirm" onclick="confirmEmailChange()">OK</button>
						</div>
					</td>
				</tr>
				<tr>
					<td class='table_left'>Přidané obrázky</td>
					<td class='table_right'><?php echo $userdata['addedPics']; ?></td>
					<td class='table_action'>Pro zvýšení přidávejte obrázky</td>
				</tr>
				<tr>
					<td class='table_left'>Uhodnuté obrázky</td>
					<td class='table_right'><?php echo $userdata['guessedPics']; ?></td>
					<td class='table_action'>Pro zvýšení se nechejte testovat</td>
				</tr>
				<tr>
					<td class='table_left'>Karma</td>
					<td class='table_right'><?php echo $userdata['karma']; ?></td>
					<td class='table_action'>Karmu získáte za činnost vedoucí ke zlepšení služby</td>
				</tr>
				<tr>
					<td class='table_left'>Status</td>
					<td class='table_right'><?php echo $userdata['status']; ?></td>
					<td class='table_action'>Zažádejte o status moderátora na poznavacky@email.com</td>
				</tr>
			</table>
			
			<button id="deleteAccountButton" onclick="deleteAccount()" style="background-color: #FF4077;">Odstranit účet</button>
			<div id="deleteAccountInput1">
				<input id="deleteAccountInputField" type=password placeholder="Zadejte své heslo pro potvrzení" maxlength=31 />
				<button class="button" id="deleteAccountConfirm" onclick="deleteAccountVerify()">OK</button>
			</div>
			<div id="deleteAccountInput2">
				<span>Tato akce je nevratná. Opravdu si přejete trvale odstranit svůj účet?</span>
				<button class="button" id="deleteAccountFinalConfirm" onclick="deleteAccountFinal()">Ano, odstranit účet</button>
				<button class="button" id="deleteAccountFinalCancel" onclick="deleteAccountCancel()">Ne, zrušit odstranění účtu</button>
			</div>
			<br>
			
			<a href="list.php"><button class="button">Zpět</button></a>
		</main>
	</div>
	<footer>
        <div id="issues" class="footerOption" onclick="showLogin()"><a href="https://github.com/HonzaSTECH/Poznavacky/issues/new/choose">Nalezli jste problém?</a></div>
        <div id="about" class="footerOption">Vytvořili Štěchy a Eksyska v roce 2019</div>
     	<div id="help" class="footerOption"><a href="https://github.com/HonzaSTECH/Poznavacky/wiki">Potřebujete pomoct?</a></div>
     	
     	<script>
         	function showLogin()
         	{
         		alert("Přihlašovací údaje pro nahlašování chyby:\nJméno: gjvj\nHeslo: poznavacky71");
         	}
     	</script>
     </footer>
	</body>
</html>