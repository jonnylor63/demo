$(document).ready(function() {
 
		$("#wf_menu > li > a").find("+ ul").hide(1);
		$("#wf_menu > li > a > span").text('+'); // add an indicator to the menu items to show there is a child menu
		$("#wf_menu > li > a").each(function() {
			toggleMenu(this);
			checkCookie(this);
		});

		$("#wf_menu > li > ul > li > a").find("+ ul").hide(1);
		$("#wf_menu > li > ul > li > a > span").text('+'); // add an indicator to the menu items to show there is a child menu
		$("#wf_menu > li > ul > li > a").each(function() {
			toggleMenu(this);
			checkCookie(this);
		});
 
 
		function checkCookie(id)
			{
				/*	check if there is a cookie set for a sub menu 
						if there is then show the menu		
				*/
				var cookieName = id.id;
				var c = readCookie(cookieName);
				if(c === 'show') {
					$(id).each(function() {
						$(this).children("span").text('-');
						$(this).find("+ ul").show(1);
					});
				}
			}
 
		function toggleMenu(id)
			{
				$(id).click(function() {
					/* toggle the +/- indicators */
					togglePlusMinus(this);	
					/* toggle the menu open or closed */
					$(this).find("+ ul").slideToggle("100");
				});
			}
			
		function togglePlusMinus(id)
			{
				$(id).each(function() {
					if($(this).find("+ ul").is(':visible')) {
							$(this).children("span").text('+');
							eraseCookie(this.id);
						} else {
							$(this).children("span").text('-');
							createCookie(this.id, 'show', 365);
						}
				});
			}
});
 
// cookie functions http://www.quirksmode.org/js/cookies.html
function createCookie(name,value,days) {
		if (days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		} else var expires = "";
		document.cookie = name+"="+value+expires+"; path=/";
	}
function readCookie(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	}
function eraseCookie(name) {
		createCookie(name,"",-1);
	}
 