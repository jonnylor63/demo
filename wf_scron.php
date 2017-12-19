<?php
//------------------------------------------------------------
// Registrazione eventi in scron
// * Parametri obbligatori
// $procscron 		: nome procedura (max 20chr)
// $notescron	    : note (max 200chr)
// * Parametri facoltativi
// nessuno
//------------------------------------------------------------
function wf_writescron($procscron, $notescron, $sendmail=false) {
 	$notescron = str_replace("'", "''", $notescron);
 	$sql = "
  INSERT INTO scron SET
       idscron		= 0
     , procscron	= '$procscron'
     , notescron	= '$notescron'
     , datainsert	= now()
         ";
  wf_getset($sql, "Errore wf_scron");

	if ($sendmail) {
		$testo="
		Server : ".$_SERVER['SERVER_NAME']."<br>
		Script : ".$_SERVER['SCRIPT_NAME']."<br>
		Processo : $procscron<br>
		Note : $notescron<br>
		Ora : ".date("d/m/Y G:i:s T");
//   	$sendok = wf_sendmail("info@lotoservizi.it", "paolopitti@gmail.com,giovanni.lorenzini@gmail.com", "CRON", $testo, $testo, null, null, null, null);
  	$sendok = wf_sendmail("info@lotoservizi.it", "giovanni.lorenzini@gmail.com", "CRON", $testo, $testo, null, null, null, null);
	}
	echo date("d/m/Y G:i:s T"), " ", $procscron, " ", $notescron, "<br>\n";
  return null;
}