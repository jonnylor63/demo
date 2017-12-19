<?php
/*************************************************
' Modulo.....: wf_error
' utilizzo...: segnalazione errori
 **************************************************/

/*****************************************************************************
/ Funzione : wf_error
/ Descrizione : visualizzazione, registrazione e segnalazione errori
/*****************************************************************************/
function wf_error($errori, $sql=null, $con=null) {
	$aerr=json_decode($errori);
	if (json_last_error()) {
		$errmsg=$errori;
		$msg.="<b>Errore</b> : ".$errmsg."<br>";
	} else {
		foreach($aerr as $key => $val) {
			$msg.="<b>$key</b> : ".$val."<br>";
		}
	}
	if ($sql) {
	  $msg.="<b>Sql</b> : [$sql]<br>";
	  $msg.="<b>Errore sql</b> : (".mysqli_errno($con) .") ". mysqli_error($con)."<br>";
	} else {
	  $msg.="<b>Sql</b> : nessuno<br>";
	}
	
  $msg.="<b>Data</b> : ".date("d.m.Y H:i:s")."<br>";
  $msg.="<b>Script</b> : ".$_SERVER[SERVER_NAME].$_SERVER[REQUEST_URI]."<br>";
  $msg.="<b>File</b> : ".$_SERVER[SCRIPT_FILENAME]."<br>";
  $msg.="<b>IP </b> : ".$_SERVER[REMOTE_ADDR]."<br>";
  if ($_POST) {
  	$msg.="<b>Variabili Post </b> : <br>";
	  foreach ($_POST as $key => $val) {
	  	if (is_array($val)) {
	  		$msg.="Array : $key<br>";
	    	foreach ($val as $key1 => $val1) {
	  			$msg.="- $key1 : $val1<br>";
	    	}
	  	} else {
	  		$msg.="- $key : $val<br>";
	  	}
	  }
  }

  $body="$msg<hr>$mail<a href='javascript:history.go(-1);'>Ritorna </a>";
	wf_displayerror("ERRORE DI SISTEMA", $body);
	
  if (defined('WF_MAILERR')) {
		$ok=wf_sendmail('abend@lotoservizi.it', WF_MAILERR, "Errore critico ".$_SERVER[SERVER_NAME], $msg);
		if ($ok) {
			$mail="Inviata Mail di segnalazione<br>";
		} else {
			$mail="Mail di segnalazione NON inviata<br>";
		}
  }
  echo $mail;

  $msg = str_replace("<br>","\n",$msg);
  $msg = str_replace("<b>","",$msg);
  $msg = str_replace("</b>","",$msg);
  $fp = fopen('error.log', 'a');
  fwrite($fp, $msg);
  fwrite($fp, "\n===============================================================================================\n");
  fclose($fp);
  
  exit;
}

/*****************************************************************************
/ Funzione : wf_alert
/ Descrizione : visualizzazione alert errore
/*****************************************************************************/
function wf_alert($msg){
	$body=$msg;
	wf_displayerror("ATTENZIONE", $body);
	echo "
	  	<script type='text/javascript'>
	  		setTimeout(\"location.href = 'index.php';\",2500);
	  	</script>
	  ";
	 exit;
}

/*****************************************************************************
/ Funzione : wf_displayerror
/ Descrizione : visualizzazione HTML errore
/*****************************************************************************/
function wf_displayerror($title, $body) {
  $html="
  	<body style='background: #444;'> 
		<div style='
				width: 600px;
				margin: 30px auto; 
				border: 1px solid #666; 
				padding: 20px; 
				background: #ddd; 
				-webkit-border-radius: 5px; 
				-moz-border-radius: 5px; 
				border-radius: 5px;
				-webkit-box-shadow: 3px 3px 15px 1px #333;
	      box-shadow:  3px 3px 15px 1px #333;'>
			<div style='text-align: center; font-weight: bold; color: red;'>$title</div>
			<hr>
			$body
		</div>
		</body>
  ";
  echo $html;	
}
/*****************************************************************************
/ Funzione : wf_ErrorHandler
/ Descrizione : controllo accessi
/*****************************************************************************/
// Impostare : set_error_handler("wf_ErrorHandler"); per intercettare gli errori
// ATTENZIONE : intercetta tutto indipendentemente da come � impostato error_reporting
function wf_ErrorHandler($errno, $errstr, $errfile, $errline) {
    $errortype = array (
                E_ERROR              => 'Error',
                E_WARNING            => 'Warning',
                E_PARSE              => 'Parsing Error',
                E_NOTICE             => 'Notice',
                E_CORE_ERROR         => 'Core Error',
                E_CORE_WARNING       => 'Core Warning',
                E_COMPILE_ERROR      => 'Compile Error',
                E_COMPILE_WARNING    => 'Compile Warning',
                E_USER_ERROR         => 'User Error',
                E_USER_WARNING       => 'User Warning',
                E_USER_NOTICE        => 'User Notice',
                E_STRICT             => 'Runtime Notice',
                E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
                );
    $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);

    $errordescr=$errortype[$errno];
    echo "$errno, $errordescr, $errstr, $errfile, $errline<br>";
    return true;
}













function wf_demo_ErrorHandler($errno, $errmsg, $filename, $linenum, $vars) {
		// if ($errno>1) return;
		
    // timestamp for the error entry
    $dt = date("Y-m-d H:i:s (T)");

    // define an assoc array of error string
    // in reality the only entries we should
    // consider are E_WARNING, E_NOTICE, E_USER_ERROR,
    // E_USER_WARNING and E_USER_NOTICE
    $errortype = array (
                E_ERROR              => 'Error',
                E_WARNING            => 'Warning',
                E_PARSE              => 'Parsing Error',
                E_NOTICE             => 'Notice',
                E_CORE_ERROR         => 'Core Error',
                E_CORE_WARNING       => 'Core Warning',
                E_COMPILE_ERROR      => 'Compile Error',
                E_COMPILE_WARNING    => 'Compile Warning',
                E_USER_ERROR         => 'User Error',
                E_USER_WARNING       => 'User Warning',
                E_USER_NOTICE        => 'User Notice',
                E_STRICT             => 'Runtime Notice',
                E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
                );
    // set of errors for which a var trace will be saved
    $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);
    
    $err = "<errorentry>\n";
    $err .= "\t<datetime>" . $dt . "</datetime>\n";
    $err .= "\t<errornum>" . $errno . "</errornum>\n";
    $err .= "\t<errortype>" . $errortype[$errno] . "</errortype>\n";
    $err .= "\t<errormsg>" . $errmsg . "</errormsg>\n";
    $err .= "\t<scriptname>" . $filename . "</scriptname>\n";
    $err .= "\t<scriptlinenum>" . $linenum . "</scriptlinenum>\n";

    if (in_array($errno, $user_errors)) {
        $err .= "\t<vartrace>" . wddx_serialize_value($vars, "Variables") . "</vartrace>\n";
    }
    $err .= "</errorentry>\n\n";
    
    // for testing
    // echo $err;

    // save to the error log, and e-mail me if there is a critical user error
    error_log($err, 3, "wf_error.log");
    if ($errno == E_USER_ERROR) {
        mail("info@lotoservizi.it", "Errore critico", $err);
    }
}