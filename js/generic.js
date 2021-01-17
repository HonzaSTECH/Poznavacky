var smallTablet = 672;
var tablet = 768;

//vše, co se děje po načtení stránky
$(function() {
	//první možnosti v každém select-boxu je přiřazena třída "selected"
	//$(".custom-select-wrapper").find(".custom-option").first().addClass("selected");

	//event listener select boxů
	$(".custom-select-wrapper").each(function() {
		if (this.id != "add-natural-select" && this.id != "class-status-select" && this.id != "report-natural-select") 
		{
			$(this).find(".custom-option").first().addClass("selected");
		}
		$(this).click(function() {
			//$(this).find(".custom-option").first().addClass("selected");
			manageSelectBox($(this));
		})
	})

	//event listener kliknutí mimo select box
	$(window).click(function(e) {
		$(".custom-select").each(function() {
			if (!this.contains(e.target)) {
				$(this).removeClass('open');
			}
		})
	});

	//event listener přidávající třídu podle toho, jestli uživatel používá myš nebo tabulátor
	$(window).on("keydown", function(event) { 
		if (event.keyCode === 9)
			$("body").addClass("tab");
	})
	$(window).on("mousedown", function() {
		$("body").removeClass("tab");	
	})
})

//Funkce pro získání hodnoty cookie
//Zkopírována z https://www.w3schools.com/js/js_cookies.asp
function getCookie(cname)
{
	var name = cname + "=";
	var decodedCookie = decodeURIComponent(document.cookie);
	var ca = decodedCookie.split(';');
	for(var i = 0; i <ca.length; i++)
	{
		var c = ca[i];
		while (c.charAt(0) == ' ')
		{
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0)
		{
			return c.substring(name.length, c.length);
		}
	}
	return "";
}

//funkce upravující manipulaci s custom select boxy
function manageSelectBox(thisObj){
	thisObj.find(".custom-select").toggleClass("open");
	$(".custom-option").each(function() {
		$(this).click(function() {
			if (!$(this).hasClass('selected')) {
				$(this).siblings().removeClass('selected');
				$(this).addClass('selected');
				$(this).closest('.custom-select').find(".custom-select-main span").text($(this).text());
			}
		})
	})
}