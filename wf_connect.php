<?php
/*************************************************
' Modulo.....: wf_connect
' utilizzo...: connessione al database
**************************************************/
include_once('config.php');

/*****************************************************************************
/ Funzione : wf_connect
/ Descrizione : connessione al database
/*****************************************************************************/
function wf_connect($schema="default") {
  global $wf_lastconn;
	$wf_dbhost["default"]= WF_DBHOST;
	$wf_dbname["default"]= WF_DBNAME;
	$wf_dbuser["default"]= WF_DBUSER;
	$wf_dbpass["default"]= WF_DBPASS;

  global $MYSQL_ERRNO, $MYSQL_ERROR;
  $con = mysqli_connect($wf_dbhost[$schema], $wf_dbuser[$schema], $wf_dbpass[$schema], $wf_dbname[$schema]);
  $wf_lastconn=$con;
  if (mysqli_connect_errno()) {
    $MYSQL_ERRNO = 0;
    $MYSQL_ERROR = "Connessione fallita al DB: $wf_dbhost.";
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    echo "<SCRIPT>alert(\"wf_connect error: ".mysqli_connect_error()." : ".WF_DBHOST.", ".WF_DBNAME.", ".WF_DBUSER."\");</SCRIPT>";
    return 0;
  }
  mysqli_set_charset($con,"utf8");
  return $con;
}

/*****************************************************************************
/ Funzione : wf_lastconn
/ Descrizione : ultima connessione al database
/*****************************************************************************/
function wf_lastconn() {
  global $wf_lastconn;
  return $wf_lastconn;
}
