var classId;            //Ukládá ID spravované třídy (zda je přihlášený uživatel jejím správcem se kontroluje v PHP)
var initialStatus;      //Ukládá status třídy uložený v databázi
var initialCode;        //Ukládá vstupní kód třídy uložený v databázi
window.onload = function()
{
	//Získání globálních proměnných z dokumentu
	classId = document.getElementById("id").innerHTML;
    initialStatus = document.getElementById("statusInput").value;
    initialCode = document.getElementById("statusCodeInputField").value;
    
    //Správně zobrazit tlačítko a vstupní pole pro kód
    statusChange();
}

function requestNameChange()
{
    document.getElementById("changeNameButton").style.display = "none";
    document.getElementById("changeNameInput").style.display = "block";
}
function confirmNameChange()
{
    var newName = document.getElementById("changeNameInputField").value;
    newName = encodeURIComponent(newName);
    
    postRequest("php/ajax/changeClassName.php", responseFunc, responseFunc, classId, newName);
    
    //Reset HTML
    document.getElementById("changeNameInputField").value = "";
    document.getElementById("changeNameInput").style.display = "none";
    document.getElementById("changeNameButton").style.display = "inline-block";
}
function statusChange()
{
    if (document.getElementById("statusInput").value === initialStatus)
    {
        //Status třídy se nezměnil
        if (document.getElementById("statusInput").value !== "Soukromá")
        {
            //Třída není ani jako soukromá --> není možné změnit vstupní kód --> vše skrýt
            document.getElementById("statusSaveButton").style.display = "none";
            document.getElementById("statusCodeInput").style.display = "none";
            return;
        }
    }
    else
    {
        //Status třídy se změnil
        if (document.getElementById("statusInput").value !== "Soukromá")
        {
            //Třída není nastavována jako soukromá --> zobraz tlačítko, ale skryj vstupní kód
            document.getElementById("statusSaveButton").style.display = "inline-block";
            document.getElementById("statusCodeInput").style.display = "none";
            return;
        }
    }
    //Sem se program dostane pouze pokud je třída nastavována jako soukromá --> zobraz vstupní kód
    document.getElementById("statusCodeInput").style.display = "inline-block";
    if ((document.getElementById("statusCodeInputField").value === initialCode && document.getElementById("statusInput").value === initialStatus) || document.getElementById("statusCodeInputField").value.length !== 4 || parseInt(document.getElementById("statusCodeInputField").value) != document.getElementById("statusCodeInputField").value)
    {
        //Kód není platný --> skryj tlačítko
        document.getElementById("statusSaveButton").style.display = "none";
    }
    else
    {
        //Kód je platný --> zobraz tlačítko
        document.getElementById("statusSaveButton").style.display = "inline-block";
    }
    
}
function confirmStatusChange()
{
    var newStatus = document.getElementById("statusInput").value;
    var code = document.getElementById("statusCodeInputField").value;
    
    if (newStatus === "Soukromá" && (code.length !== 4 || parseInt(code) != code))
    {
        //Není zadán platný kód
        alert("Zadaný kód není platný. Kód musí být obsahovat čtyři číslice.");
        return;
    }
    
    var confirmation;
    switch (newStatus)
    {
        case "Veřejná":
            confirmation = confirm("Třída bude nastavena jako veřejná a všichni přihlášení uživatelé do ní budou mít přístup. Pokračovat?");
            break;
        case "Soukromá":
            confirmation = confirm("Třída bude nastavena jako soukromá a všichni uživatelé, kteří nikdy nezadali platný vstupní kód třídy, ztratí do třídy přístup. Pokračovat?");
            break;
        case "Uzamčená":
            confirmation = confirm("Třída bude uzamčena a žádní uživatelé, kteří nyní nejsou jejími členy do ní nebudou moci vstupit (včetně těch, kteří zadají platný vstupní kód v budoucnosti). Pokračovat?");
            break;
        default:
            alert("Zvolený stav třídy není platný!");
            return;
    }
    
    if (!confirmation){return;}
    
    postRequest("php/ajax/changeClassStatus.php", responseFunc, responseFunc, classId, null, newStatus, code);
    
    //Reset HTML
    document.getElementById("changeNameInput").style.display = "statusSaveButton";
}
function updateMembers()
{
	postRequest("php/getMembers.php", updateMembersTable, responseFunc, classId);
}

function kickUser(event)
{
    var user = event.target.parentNode.parentNode.parentNode.childNodes[0].innerText;
    if (!confirm("Opravdu chcete odebrat uživatele " + user + " ze třídy?"))
    {
        return;
    }
    postRequest("php/ajax/kickMember.php", responseFunc, responseFunc, classId, user);
}
function inviteFormShow()
{
    document.getElementById("inviteForm").style.display = "block";
}
function inviteFormHide()
{
    document.getElementById("inviteForm").style.display = "none";
}
function inviteUser()
{
    var user = document.getElementById("inviteUserInput").value;
    postRequest("php/ajax/inviteUser.php", responseFunc, responseFunc, classId, user);
}
function manageTest(testId)
{
	postRequest("php/ajax/getTestReports.php", showTestReports, responseFunc, classId, null, null, testId);
}
function showTestReports(response)
{
    document.getElementById("classManagementOverlay").style.visibility = "visible";
    document.getElementById("testReportsScreen").style.display = "block";
    document.getElementById("testReportsContent").innerHTML = response;
}
function showPicture(url)
{
    document.getElementById("classManagementTestReportsOverlay").style.visibility = "visible";
    document.getElementById("testReportsImagePreview").style.display = "block";
    document.getElementById("previewImage").setAttribute("src", url);
}
function closeImagePreview()
{
	document.getElementById("classManagementTestReportsOverlay").style.visibility = "hidden";
    document.getElementById("testReportsImagePreview").style.display = "none";
    document.getElementById("previewImage").setAttribute("src", "images/imagePreview.png");
}
function deletePicture(picId)
{
    //TODO
}
function deleteReport(reportId)
{
    //TODO
}
function closeTestReports()
{
	document.getElementById("classManagementOverlay").style.visibility = "hidden";
    document.getElementById("testReports").style.display = "none";
}
function createTest()
{
	document.getElementById("createForm").style.display = "block";
}
function createTestSubmit()
{
	var name = document.getElementById("createInput").value;
    postRequest("php/ajax/createGroup.php", updateTests, responseFunc, classId, name);
}
function createTestHide()
{
	document.getElementById("createForm").style.display = "none";
}
function updateTests(response)
{
	if (response.length === 0)	//Nedošlo k žádné chybě
	{
		document.getElementById("createInput").value = "";
		postRequest("php/getGroupsAdminTable.php", updateTestsTable, responseFunc, classId);
	}
	else
	{
		eval(response);
	}
}
function editTest(testId)
{
	postRequest("php/ajax/getTestContent.php", showTestEditation, responseFunc, classId, null, null, testId);
}
function showTestEditation(response)
{
    document.getElementById("classManagementOverlay").style.visibility = "visible";
    document.getElementById("testEditor").style.display = "block";
    document.getElementById("testEditorContent").innerHTML = response;
}
function addPart()
{
    //TODO - implementovat funkci pro přidání části
}
function renamePart(event)
{
    //TODO - implementovat funkci pro přejmenování části
}
function removePart(event)
{
    //TODO - implementovat funkci pro odebrání části
}
function showNaturals(partId)
{
    //TODO - implementovat funkci pro zobrazení přírodnin
    var naturalRows = document.getElementsByClassName("testEditorNaturalRow");
    var cnt = naturalRows.length;
    for (var i = 0; i < cnt; i++)
    {
        if (naturalRows[i].className.includes("part_id_" + partId))
        {
        	naturalRows[i].style.display = "block";
        }
        else
        {
        	naturalRows[i].style.display = "none";
        }
    }
}
function closeTestEditation(save)
{
	if (save === true)
	{
		//TODO - uložit změny do databáze
	    document.getElementById("classManagementOverlay").style.visibility = "hidden";
	    document.getElementById("testEditor").style.display = "none";
	}
	else
	{
		if (confirm("Opravdu chcete odejít? Vaše změny budou zahozeny!"))
	    {
			document.getElementById("classManagementOverlay").style.visibility = "hidden";
		    document.getElementById("testEditor").style.display = "none";
	    }
	}
}
function deleteTest(id)
{
    var name = event.target.parentNode.parentNode.parentNode.childNodes[0].innerText;
    if (confirm("Opravdu chcete trvale odstranit poznávačku " + name + " včetně všech jejích částí, přírodnin a obrázků? Tato akce je nevratná!"))
    {
    	postRequest("php/ajax/deleteGroup.php", updateTests, responseFunc, classId, id);
    }
}
function deleteClass()
{
	document.getElementById("deleteClassButton").style.display = "none";
	document.getElementById("deleteClassInput1").style.display = "inline-block";
}

function deleteClassVerify()
{
	var password = document.getElementById("deleteClassInputField").value;
	
	postRequest("php/ajax/checkPassword.php", deleteClassConfirm, responseFunc, null, null, null, null, password);
}

function deleteClassConfirm(response)
{
	if (response === "ok")
	{
	document.getElementById("deleteClassInput2").style.display = "inline-block";
	document.getElementById("deleteClassInput1").style.display = "none";
	}
	else
	{
		alert("Špatné heslo.");
		document.getElementById("deleteClassInputField").value = "";
	}
}

function deleteClassFinal()
{
	var pass = document.getElementById("deleteClassInputField").value;
	postRequest("php/ajax/deleteClass.php", responseFunc, null, classId, null, null, null, pass);
}

function deleteClassCancel()
{
	document.getElementById("deleteClassInputField").value = "";
	document.getElementById("deleteClassButton").style.display = "inline-block";
	document.getElementById("deleteClassInput2").style.display = "none";
}

function postRequest(url, success = null, error = null, classId, newName = null, newStatus = null, newCode = null, password = null){
    var req = false;
    //Creating request
    try
    {
        //Most broswers
        req = new XMLHttpRequest();
    } catch (e)
    {
        //Interned Explorer
        try
        {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        }catch(e)
        {
            //Older version of IE
            try
            {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            }catch(e)
            {
                return false;
            }
        }
    }
    
    //Checking request
    if (!req) return false;
    
    //Checking function parameters and setting intial values in case they aren´t specified
    if (typeof success != 'function') success = function () {};
    if (typeof error!= 'function') error = function () {};
    
    //Waiting for server response
    req.onreadystatechange = function()
    {
        if(req.readyState == 4)
        {
            return req.status === 200 ? success(req.responseText) : error(req.status);
        }
    }
    req.open("POST", url, true);
    req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    req.send("id="+classId+"&name="+newName+"&status="+newStatus+"&code="+newCode+"&oldPass="+password);
    return req;
}
function responseFunc(response)
{
    eval(response);
}
function updateMembersTable(response)
{
    document.getElementById("membersCell").innerHTML = response;
}
function updateTestsTable(response)
{
	document.getElementById("testsCell").innerHTML = response;
}