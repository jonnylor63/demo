/* *************************************************************
** modulo...: webfunc.js
** scopo....: attivazione servizi javascript inclusi in webfunc
** autore...: Giovanni Lorenzini
**
** Data     Au Note
** 22.08.12 GL Revisione
** *************************************************************/

// -------------------------------------------------------------
// redy : attivazione datepicker
// -------------------------------------------------------------
$(document).ready(function() {
	$( ".input-data").datepicker({
				dateFormat:"dd/mm/yy"
			,	changeMonth: true
			,	changeYear: true
			, numberOfMonths: 2
			, showButtonPanel: true	
			, showOn: "button"
			, buttonImage: "img/calendar.gif"
			});
});

// non necessario in quanto richiamato nella wf_multinput