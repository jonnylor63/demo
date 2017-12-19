/*****************************************************************************
/ Funzione 		: wf_siono
/ Descrizione : richiede conferma prima di un traserimento di url
/ Parametri :
/ 	$msg				Messaggio da erogare
/ 	$href				URL di destinazione in caso di conferma
/*****************************************************************************/

function wf_siono(msg, href) {
 	event.stopPropagation();
 	conf=confirm(msg);
 	if (conf) location.href=href;
	return 1;
}
