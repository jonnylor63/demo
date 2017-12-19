<?php
/* ******************************************* 
** progetto..: WEBFUNC
** modulo....: wf_index.php
** scopo.....: gestione progetto
** autore....: LOTO Servizi - Giovanni Lorenzini
** *******************************************/

/*****************************************************************************
/ Funzione 		: wf_index
/ Descrizione : routing richieste
/ Parametri :
/ 	nessuno				
/*****************************************************************************/
function wf_index() {
	wf_secureGlobals();								// controllo GET e POST anti SQL INJECTION
	$baseurl="http://".$_SERVER["SERVER_NAME"].dirname($_SERVER["SCRIPT_NAME"]);

	if (! wf_chklogin()) {
/*
    Header("Location: login.php");
		exit;
*/
	}

	$proc=($_GET["proc"]?$_GET["proc"]:"home");
	
	$aret=wf_proc($proc);
	$tit =$aret[TITLE];
	$html=$aret[CONTENT];
	$tpl =$aret[TEMPLATE];
	$bre =$aret[BREAD];
	$pri =$aret[ISPRIVATE];
	$htitle =$aret[HTITLE];
	$hdescr =$aret[HDESCR];
	$hkeywo =$aret[HKEYWO];
	// preparazione pagina
	if (!$tpl) {
		echo "Template -$tpl- non trovato ! Procedura -".$proc."-";
		exit;
	}

	$page	=	file_get_contents($tpl);
	/* elaborazione dei frammenti di codice php <?=xxxx?> */
	$page = preg_replace_callback(
		'/(\<\?=|\<\?php=|\<\?php)(.*?)\?\>/', 
		function ($matches) {
			If (FALSE === @eval("\$html = $matches[2];")) {
				echo "Errore nel template -$matches[0]-";
				exit;
			}
			return $html;
		},
		$page);
		
	$page = wf_index_head($page);
	$page = wf_index_menu($page);
	
	$back='<a href="javascript:history.go(-1);" title="torna indietro"><img src="img/back.gif" height="17"></a>';
	
	$page = str_replace("%PROC%"      , $proc, $page);
	$page = str_replace("%HTITLE%"    , $htitle, $page);
	$page = str_replace("%HDESCR%"    , $hdescr, $page);
	$page = str_replace("%HKEYWO%"    , $hkeywo, $page);

	$page = str_replace("%TITOLO%"    ,$tit  ,$page);
	$page = str_replace("%BREADCRUMB%",$bre  ,$page);
	$page = str_replace("%BACK%"      ,$back ,$page);
	if (is_array($html)) {
		foreach($html as $key => $value) {
			$page = str_replace("%$key%"   ,$value ,$page);
		}
		$page = str_replace("%CONTENT%"   ,"" ,$page);
	} else {
		$page = str_replace("%CONTENT%"   ,$html ,$page);
	}

	echo $page;
}

/*****************************************************************************
/ Funzione 		: wf_index_head
/ Descrizione : gestione menu utente
/ Parametri :
/ 	$page				HTML della pagina
/*****************************************************************************/
function wf_index_head($page) {
	// Login e Logout
	if (! wf_chklogin()) {
		$upper='';
	} else {
		$avatar=($_SESSION[s_imgsuten]?$_SESSION[s_imgsuten]:"img/noavatar.png");
		$upper='
			<div id="uppermenu">
				<table>
				<tr>
					<td>
						<a href="index.php?proc=suten&wfact=e&wfid='.$_SESSION[s_idsuten].'" class="noborder" title="Modifica Profilo">
							<img src="'.$avatar.'" />
						</a>
					</td>
					<td>
						'.$_SESSION[s_nomesuten].'<br>
						'.$_SESSION[s_cognomesuten].'
					</td>
					<td>
						'.$_SESSION[s_descrsloca].'
					</td>
					<td>
						<a href="login.php">Logout</a>
					</td>
				</tr>
				</table>
			</div>
		';
	}
	$page = str_replace("%UPPERMENU%" , $upper, $page);
	return $page;
}

/*****************************************************************************
/ Funzione 		: wf_index_menu
/ Descrizione : gestione menu principale
/ Parametri :
/ 	$page				HTML della pagina
/*****************************************************************************/
function wf_index_menu($page) {
	$uri=substr($_SERVER["PHP_SELF"], strrpos($_SERVER["PHP_SELF"], "/")+1);
	if ($uri=="login.php") {
		$menu="";
	} else {
		// $menu=wf_menu31(false);
		$menu=wf_menu("admin");
	}

	$page = str_replace("%MAINMENU%" , $menu, $page);
	return $page;
}

?>