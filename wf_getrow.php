<?php
/*************************************************
' Modulo.....: wf_getrow
' utilizzo...: interrogazione DB
 **************************************************/

/*****************************************************************************
/ Funzione : wf_getset
/ Descrizione : Estrazione set di record
/*****************************************************************************/
function wf_getset($sql, $errmsg="Errore wf_getset", $schema="default") {
  global $wf_last_id;
  $con = wf_connect($schema);

  if (WF_LOG) {
	  $fp = fopen('getset.log', 'a');
	  fwrite($fp, date("d.m.Y H:i:s")." >".$sql."< \n");
	  fclose($fp);	  
  }

  $set        = mysqli_query($con, $sql);
  $wf_last_id = mysqli_insert_id($con);

  if (!$set) {
	  wf_error($errmsg, $sql, $con);
  }
  return ($set);
}

/*****************************************************************************
/ Funzione : wf_getid
/ Descrizione : Ultimo ID creato
/*****************************************************************************/
function wf_getid() {
  global $wf_last_id;
  return $wf_last_id;
}

/*****************************************************************************
/ Funzione : wf_getrow
/ Descrizione : Estrare prima riga
/*****************************************************************************/
function wf_getrow($sql, $msg="Errore wf_getrow", $schema="default") {
  $set = wf_getset($sql, $msg, $schema);
  $row = mysqli_fetch_assoc($set);
  mysqli_free_result($set);
  return ($row);
}

/*****************************************************************************
/ Funzione : wf_set2row
/ Descrizione : Estrare riga da set
/*****************************************************************************/
function wf_set2row($set) {
		return mysqli_fetch_assoc($set);
}

/*****************************************************************************
/ Funzione : wf_utf8_encode
/ Descrizione : ricodifica UTF8 del record
/*****************************************************************************/
function wf_utf8_encode($row) {
	foreach($row as $key => $val) {
		$new[$key]=utf8_encode($val);
	}
	return $new;
}