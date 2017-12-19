<?
/* ******************************************* 
** progetto..: WEBFUNC
** modulo....: wf_security.php
** scopo.....: controllo GET e POST anti SQL INJECTION
** autore....: LOTO Servizi - Giovanni Lorenzini
** *******************************************/

function wf_secureCleaner($key, $val) {
	global $dbhost, $dbuser, $dbpasswd;

	$val = htmlspecialchars($val, ENT_IGNORE, 'utf-8');
	$val = stripslashes($val);
	$val = strip_tags($val);
//	$val = str_ireplace("script", "blocked", $val);
	$val = mysqli_real_escape_string($val);
	return $val;
}

function wf_secureGlobals() {
/*
	foreach($_GET  as $key => $val) {
		$new=wf_secureCleaner($key, $val);
		$_GET[$key]=$new;
	}
	foreach($_POST as $key => $val) {
		$new=wf_secureCleaner($key, $val);
		$_POST[$key]=$new;
	}
*/
}