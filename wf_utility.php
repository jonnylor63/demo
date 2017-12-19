<?php
/*************************************************
' modulo.....: wf_utility
' utilizzo...: funzioni di utilità
' autore.....: gl
'
' Data     Au Note
' 21.08.12 GL Creazione
**************************************************/

/*****************************************************************************
/ Funzione : wf_nocache
/ Descrizione : accesso a file css o js con superamento cache
/*****************************************************************************/
function wf_nocache($filename, $media=null) {
	$timestamp=date ("YmdHis", filemtime($filename));

	if (substr($filename,-3)=="css") {
		$media=($media?'media="'.$media.'"':'');
		$html="<link rel='stylesheet' type='text/css' $media href='$filename?$timestamp' />";

	} elseif (substr($filename,-2)=="js") {
		$html="<script type='text/javascript' language='javascript' src='$filename?$timestamp'></script>";

	}
	return $html;
}