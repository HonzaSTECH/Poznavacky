var smallTablet = 672;

//vše, co se děje po načtení stránky
$(function() {
	$(".logout-button").click(function() {logout()});
	$("#close-changelog-button").click(function() {closeChangelog()})

	//skrytí částí postranního panelu, aby se při prvním otevření zobrazila jen jedna
	$("#aside-nav").hide();
	$("#aside-settings").hide();
	
	//skryje tlačítko na změnu zobrazení složek, pokud se uživatel nenachází na stránce se složkami
	if($("body").attr("id")!="menu")
		$("#change-folders-layout-button").hide();

	checkHeader();
});

//vše, co se děje při změně velikosti okna
$(window).resize(function(){
	checkHeader();
})

//funkce nastavující padding mainu podle velikosti okna (různá zobrazení pro mobily a desktopy)
function checkHeader() {
	if ($(window).width() <= smallTablet)
		$("main, aside").css("padding-top", $("header").outerHeight());
	else 
		$("main, aside").css("padding-top", 0);
}

//odešle požadavek na odhlášení
function logout()
{
	$.get('menu/logout', function (response, status) { ajaxCallback(response, status, function() {}); }, "json");
}

function closeChangelog() {
	$("#changelog").remove();
	$("#overlay").removeClass("show");
}
