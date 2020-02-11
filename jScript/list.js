var selectedPartTR;	//Skladuje innerHTML řádku tabulky, který obsahuje právě vybranou část

function closeChangelog()
{
    document.getElementById("changelogContainer").style.display = "none";
    document.getElementById('listOverlay').style.visibility = 'hidden';
}
function newClass()
{
    document.getElementById('listOverlay').style.visibility = 'visible';
    document.getElementById('newClassFormContainer').style.display = "block";
}
function closeNewClassForm()
{
    document.getElementById('newClassFormContainer').style.display = "none";
    document.getElementById('listOverlay').style.visibility = 'hidden';
}
function applicationSubmit(event)
{
    event.preventDefault();
    var email = document.getElementById("newClassFormEmail").value;
    var className = document.getElementById("newClassFormName").value;
    var classCode = document.getElementById("newClassFormCode").value;
    var info = document.getElementById("newClassFormInfo").value;
    postRequest("php/ajax/newClassApply.php", applicationSent, alertResponse, email, className, classCode, info);
}
function enterClassCode()
{
    document.getElementById("classCodeBtn").style.display = "none";
    document.getElementById("classCodeForm").style.display = "block";
}
function closeClassCode()
{
    document.getElementById("classCodeForm").style.display = "none";
	document.getElementById("classCodeBtn").style.display = "block";
}
function submitClassCode()
{
    var code = document.getElementById("classCodeInput").value;
    postRequest("php/ajax/verifyClassCode.php", newClasses, alertResponse, null, null, code, null);
}
function showInvitations()
{
    document.getElementById("invitationsButton").style.display = "none";
    document.getElementById("invitationsContent").style.display = "block";
}
function acceptInvitation(event, cId)
{
    //Odstranění pozvánky z DOM
	event.target.parentNode.parentNode.parentNode.parentNode.removeChild(event.target.parentNode.parentNode.parentNode);
	
	getRequest("php/ajax/answerInvitation.php?accepted=1&classId=" + cId);
}
function declineInvitation(event, cId)
{
	//Odstranění pozvánky z DOM
	event.target.parentNode.parentNode.parentNode.parentNode.removeChild(event.target.parentNode.parentNode.parentNode);
	
	getRequest("php/ajax/answerInvitation.php?accepted=0&classId=" + cId);
}
function choose(event, depth, option = undefined, type = undefined)
{
	if (event !== null && event.target.id === 'listAction')
	{
		//Pokud bylo kliknuto na tlačítko, které slouží k provedení akce se třídou, nedělej nic
		return;
	}
	
    switch (depth)
    {
        //Vypsání všech tříd
        case 0:
            getRequest("php/getClasses.php", replaceTable, alertResponse);
            setDynamicDimensions();
            break;
        //Vybrání třídy
        case 1:
            getRequest("php/getGroups.php?classId=" + option, replaceTable, alertResponse);
            setDynamicDimensions();
            break;
        //Vybrání skupiny
        case 2:
            getRequest("php/getParts.php?groupId=" + option, loadParts, alertResponse);
            break;
        //Vybrání části
        case 3:
            document.cookie="current=" + option;
            switch (type)
            {
                case 0:
                	location.href = 'addPics.php';
                    break;
                case 1:
                	location.href = 'learn.php';
                    break;
                case 2:
                	location.href = 'test.php';
                    break;
            }
            break;
    }
}
function showOptions(event, option, allowAll)
{
    var row = event.target.parentNode;
    row.setAttribute("id","button_row");
    if (!Number.isInteger(Number(row.childNodes[1].innerHTML)))
    {
    	return; //Tlačítka jsou již zobrazená
    }
    else
    {
    	//Obnovení dříve vybraného řádku
    	for (var i = 0; i < row.parentNode.childNodes.length; i++)
    	{
    		if (row.parentNode.childNodes[i].childNodes.length === 1)
    		{
                row.parentNode.childNodes[i].innerHTML = selectedPartTR;
                row.parentNode.childNodes[i].removeAttribute("id")
    			break;
    		} 
    	}
    	
    	//Uložení současného řádku
    	selectedPartTR = row.innerHTML;
    }
    
    row.removeChild(row.childNodes[2]);
    row.removeChild(row.childNodes[1]);
    row.childNodes[0].setAttribute("colspan",3);
    if (allowAll === true)
    {
    
        row.childNodes[0].innerHTML = "<button class='button' onclick='choose(event,3,\""+option+"\""+",0)'>Přidat obrázky</button><button class='button' onclick='choose(3,"+"\""+option+"\""+",1)'>Učit se</button><button class='button' onclick='choose(3,"+"\""+option+"\""+",2)'>Vyzkoušet se</button>";
    }
    else
    {
        row.childNodes[0].innerHTML = "<button class='button' onclick='choose(event,3,\""+option+"\""+",0)'>Přidat obrázky</button><button class='button buttonDisabled' style='cursor: not-allowed;' title='Na tuto část se nemůžete učit,\nprotože zatím neobsahuje žádné obrázky'>Učit se</button><button class='button buttonDisabled' style='cursor: not-allowed;' title='Z této části se nemůžete nechat testovat,\nprotože zatím neobsahuje žádné obrázky'>Vyzkoušet se</button>";
    }
}
function leaveClass(classId)
{
	if (confirm("Opravdu chcete třídu opustit?"))
	{
		getRequest('php/ajax/leaveClass.php?cId='+classId, classLeft, alertResponse);
	}
}
function classLeft()
{
	choose(null,0);	//Aktualizace tabulky tříd
}
function setSolidDimensions()
{
    //Nastavení pevné šířky pro tabulku, aby se její šířka neměnila při zobrazování tlačítek
    document.getElementById('table').setAttribute("style","width:"+window.getComputedStyle(document.getElementById('table')).width+";");
    
  //Nastavení pevné šířky pro tabulku, aby se její šířka neměnila při zobrazování tlačítek (přeskakujeme hlavičku tabulky)
    for (var i = 1; i < document.getElementsByTagName("tr").length; i++)
    {
        document.getElementsByTagName("tr")[i].setAttribute("style","height:"+window.getComputedStyle(document.getElementsByTagName("tr")[i]).height+";");
    }
}
function setDynamicDimensions()
{
	//Nastavení pevné šířky pro tabulku, aby se její šířka neměnila při zobrazování tlačítek
    document.getElementById('table').removeAttribute("style");
    
    //Nastavení pevné šířky pro tabulku, aby se její šířka neměnila při zobrazování tlačítek (přeskakujeme hlavičku tabulky)
    for (var i = 1; i < document.getElementsByTagName("tr").length; i++)
    {
        document.getElementsByTagName("tr")[i].removeAttribute("style");
    }
}
function getRequest(url, success = null, error = null){
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
    req.open("GET", url, true);
    req.send();
    return req;
}
function postRequest(url, success = null, error = null, email, cName, cCode, info){
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
	req.send("email="+email+"&name="+cName+"&code="+cCode+"&info="+info);
	return req;
}
function replaceTable(response)
{
    document.getElementById("table").innerHTML = response;
}
function loadParts(response)
{
    replaceTable(response);
    setSolidDimensions();
}
function alertResponse(response)
{
    alert(response);
}
function applicationSent(response)
{
    closeNewClassForm();
    alert("Formulář byl úspěšně odeslán\nSledujte prosím svou e-mailovou schránku, obdržíte do ní informace, až bude třída připravená.");
}
function newClasses(response)
{
    alert(response);
    choose(null,0);
}