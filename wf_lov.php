<?php
//======================================================
// Creazione LOV select/option caricando da tabella
// Parametri
// 	$tab 			: tabella da analizzare
//  $name			: nome del campo <select>
//  $id				: campo del DB che identifica la chiave
//  $descr		: campo del DB che identifica la descrizione
//  $default	: valore della chiave da impostare come selezionato
//  $where  	: condizione di filtro alla selezione dei valori
//  $orderby	: ordinamento della estrazione dati
//	$groupby	: raggruppamento
// 	$view     : visualizza etichetta "v"=VUOTO, "t"=TUTTI, "s"=SELEZIONA; entrambi con "vt"
// 	$auto		  : abilita la sumission automatica
//  $extra    : contenuto extra in select es: onchange, class, ..
// Ritorno
// 	$html     : html da presentare
//======================================================
// Autore - Paolo Tomassoni
//======================================================
function wf_lov($tab, $name, $id, $descr, $default, $where=null, $orderby=null, $groupby=null, $view=null, $auto=false, $extra="") {  
	$sqlw=($where?" where ".$where." ":"");
	
  $sql = "select $id chiave, $descr descr from $tab $sqlw $groupby";
	if ($orderby) $sql.= " order by $orderby";
	$ris = wf_getset($sql, "Errore LOV01");

	if ($auto) {
		$html.= "<select name=$name id=$name onchange='$(\"form:first\").submit();' $extra>";
	} else {
		$html.= "<select name=$name id=$name $extra>";
	}
  $selected = ("*"==$default ? "selected" : "" );
	if (strpos(strtoupper($view), "T")!==false) {
    $selected = (0===$default ? "SELECTED" : "");
		$html.="<option value=0 $selected>TUTTI</option>";
	}
	if (strpos(strtoupper($view), "S")!==false) {
    $selected = (0===$default ? "SELECTED" : "");
		$html.="<option value=0 $selected>Seleziona</option>";
	}

	$selected="";
	while ($row = wf_set2row($ris)) {
    if (substr($row[descr],0,1)<>".") {
      $selected = ($row[chiave]===$default ? "selected" : "");
      $html.="<option value=$row[chiave] $selected>".ucfirst(substr($row[descr],0,30))."</option>";
	  }
	}
	if (strpos(strtoupper($view), "V")!==false) {
    $selected = ("9999"===$default ? "selected" : "");
    $html.="<option value=9999 $selected>VUOTO</option>";
  }
  $html.="</select>";
  
	return ($html);
}

//======================================================
// Creazione LOV select/option con sql specifico
// Parametri
// 	$sql 			: frase sql contenente campi valore, descr 
//  $name			: nome del campo <select>
//  $id				: campo del DB che identifica la chiave
//  $descr		: campo del DB che identifica la descrizione
//  $default	: valore della chiave da impostare come selezionato
// 	$view     : visualizza etichetta VUOTO se "v" o TUTTI se "t" o entrambi con "vt"
// 	$auto		  : abilita la sumission automatica
//  $extra    : contenuto extra in select es: onchange, class, ..
// Ritorno
// 	$html     : html da presentare
//======================================================
// Autore - GL
//======================================================
function wf_sqllov($sql, $name, $id, $campi, $default, $view=null, $auto=false, $extra="") {
	$ris = wf_getset($sql, "Errore SQLLOV01");

	$acampi=explode(";",$campi);
  $descr=$acampi[0];
  $group=$acampi[1];

	if ($auto) {
		$html.= "<select name=$name id=$name onchange='$(\"form:first\").submit();' $extra>";
	} else {
		$html.= "<select name=$name id=$name $extra>";
	}
	$selected = ("*"==$default ? "selected" : "" );
	if (strpos(strtoupper($view), "T")!==false) {
		$html.="<option value=0 $selected>TUTTI</option>";
	}

	$rec=0;
	$chiave="xx";
	while ($row = wf_set2row($ris)) {
		if ($group!=null and $chiave<>$row[$group]) {
			if ($chiave<>"xx") {
				$html .= "</optgroup>";
			}
			$html .= "<optgroup label='$row[$group]'>";
			$chiave=$row[$group];
		}

		if (substr($row[descr],0,1)<>".") {
			$val=$row[$id];
			$des=$row[$descr];
			$rec++;
			$selected = ($val==$default) ? "selected" : "";
			$html.="<option value=$val $selected>".ucfirst($des)."</option>";

		}
	}
	if (strpos(strtoupper($view), "V")!==false) {
		$selected = ("9999"==$default) ? "selected" : "";
		$html.="<option value=9999 $selected>VUOTO</option>";
	}
	$html.="</select>";

	// controllo nessun record trovato
	if ($rec==0) {
		$html=false;
	}
	return ($html);
}
//======================================================
// Creazione LOV select/option caricando da array
// Parametri
// 	$json 		: json parametri es: '{"1":"primo","2":"secondo","3":"terzo"}' 
//  $name			: nome del campo <select>
//  $default	: valore della chiave da impostare come selezionato
// 	$view     : visualizza etichetta VUOTO se "v" o TUTTI se "t" o entrambi con "vt"
// 	$auto		  : abilita la sumission automatica
// Ritorno
// 	$html     : html da presentare
//======================================================
// Autore - GL
//======================================================
function wf_jlov($json, $name, $default=null, $view=null, $auto=null) {
  $array=json_decode($json, true);
	if ($auto) {
		$html.= "<select name=$name id=$name onchange='$(\"form:first\").submit();'>";
	} else {
		$html.= "<select name=$name id=$name>";
	}
  $selected = ("*"==$default ? "selected" : "" );
	if (strpos(strtoupper($view), "T")!==false) {
		$html.="<option value=0 $selected>TUTTI</option>";
	}

	foreach ($array as $key => $val) {
    $selected = ($key==$default) ? "selected" : "";
    $html.="<option value=$key $selected>".ucfirst(substr($val,0,30))."</option>";
	}

	if (strpos(strtoupper($view), "V")!==false) {
    $selected = ("9999"==$default) ? "selected" : "";
    $html.="<option value=9999 $selected>VUOTO</option>";
  }
  $html.="</select>";
  
	return ($html);
}