var classId;            //Ukládá ID spravované třídy (zda je přihlášený uživatel jejím správcem se kontroluje v PHP)
var initialStatus;      //Ukládá status třídy uložený v databázi
var initialCode;        //Ukládá vstupní kód třídy uložený v databázi
window.onload = function()
{
	//Získání globálních proměnných z dokumentu
	classId = document.getElementById("id").innerHTML;
    initialStatus = document.getElementById("statusInput").value;
    initialCode = document.getElementById("statusCodeInput").value;
    
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
    if ((document.getElementById("statusCodeInput").value === initialCode && document.getElementById("statusInput").value === initialStatus) || document.getElementById("statusCodeInput").value.length !== 4 || parseInt(document.getElementById("statusCodeInput").value) != document.getElementById("statusCodeInput").value)
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
    var code = document.getElementById("statusCodeInput").value;
    
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

function postRequest(url, success = null, error = null, classId, newName = null, newStatus = null, newCode = null){
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
    req.send("id="+classId+"name="+newName+"&status="+newStatus+"&code="+newCode);
    return req;
}
function responseFunc(response)
{
    eval(response);
}