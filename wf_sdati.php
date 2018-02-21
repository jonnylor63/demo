<?php
/***
'-------------------------------------------------
' aggiorna sdati da parametri
'-------------------------------------------------
***/
function wf_readsdati($par1) {
  $sql = "select valoresdati from sdati where keysdati = '" . $par1 . "'";
	$ris = wf_getset($sql, "Errore SDATI01");
  $row = wf_set2row($ris);
  $retval = $row['valoresdati'];
  mysqli_free_result($ris);
  return $retval;
}

function wf_writesdati($par1, $par2) {
  $sql = "
         update sdati
            set valoresdati = '" . $par2 . "'
         where keysdati = '" . $par1 . "'
         ";
	$ris = wf_getset($sql, "Errore SDATI02");
  $row = wf_set2row($ris);
  mysqli_free_result($ris);
  $retval = true;
  return $retval;
}