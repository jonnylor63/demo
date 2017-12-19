// avvio wf_menu 05

		$(document).ready(function(){
				var SelfLocation = window.location.href.split('?');
				switch (SelfLocation[1]) {
						case "defined_width":
								$(".MegaMenuLink").megamenu(".MegaMenuContent", {width: "850px"});
								break;
						case "auto_width_right":
								$(".MegaMenuLink").megamenu(".MegaMenuContent", {justify: "right"});
								$('.MegaMenu').css("text-align", "right");
								break;
						case "defined_width_right":
								$(".MegaMenuLink").megamenu(".MegaMenuContent", {justify: "right", width: "850px"});
								$('.MegaMenu').css("text-align", "right");
								break;
						default:
								$(".MegaMenuLink").megamenu(".MegaMenuContent");
								break;
				}
		});
