<?php
/*****************************************************************************
/ Funzione 		: wf_template
/ Descrizione : predisposizione template pagina
/ Parametri
/ 	$tpl				file contenente lo schema
/		$apar				array degli elementi da sostituire
/ Ritorno
/ 	$html     : html da presentare
/=============================================================================
/ Autore 			: Giovanni Lorenzini
/*****************************************************************************/
function wf_template($wf_t_tpl, $wf_t_apar=null) {
	global $wf_js, $wf_loadjs;
	if (!file_exists($wf_t_tpl)) {
		wf_error("File '$wf_t_tpl' non trovato");
	}
	$wf_t_page=file_get_contents($wf_t_tpl);
	if ($wf_t_page) {
		// Creazione variabili
		if ($wf_t_apar) {
			foreach($wf_t_apar as $wf_t_key => $wf_t_value) {
				${$wf_t_key} = $wf_t_value;
			}
		}
		$wf_t_page = str_replace("{{", "<?=" ,$wf_t_page);
		$wf_t_page = str_replace("}}", "?>" ,$wf_t_page);

		ob_start();
		eval("?>" . $wf_t_page . "<?");
	  $wf_t_page = ob_get_contents();
	  ob_end_clean();

	  if ($wf_t_apar) {
			foreach($wf_t_apar as $wf_t_key => $wf_t_value) {
				$wf_t_key=strtoupper($wf_t_key);
				$wf_t_page = str_replace("%$wf_t_key%", $wf_t_value ,$wf_t_page);
			}
	  }
		$wf_t_page = str_replace("%WF_JS%"   	,$wf_js,$wf_t_page);
		$wf_t_page = str_replace("%WF_LOADJS%" ,$wf_loadjs,$wf_t_page);
	}
	return ($wf_t_page);
}

/*************
 * Funzione: wf_js
 * Caucamento script js prima di </body>
 */
function wf_js($js) {
	global $wf_js;
	$wf_js.=$js."\n";
}

/*************
 * Funzione: wf_loadjs
 * Caucamento script js in Head
 */
function wf_loadjs($js) {
	global $wf_loadjs;
	$wf_loadjs.=$js."\n";
}