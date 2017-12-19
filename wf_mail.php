<?php
//------------------------------------------------------------
// Invio email con allegati
// * Parametri obbligatori
// $mittente 			: mittente (es: "giovanni.lorenzini@gmail.com")
// $destinatario	: destinatari separati da virgola	(es: "giovanni.lorenzini@gmail.com, giovanni.lorenzini@lotoservizi.it")
// $soggetto			: oggetto
// $bodyhtml			: testo mail in formato html
// * Parametri facoltativi
// $bodytxt				: testo mail per client che non ricevono testo html; se non specificato utilizza il $bodyhtml ripulito dai tag html
// $allegato			: nome del file allegato (es: "documento.doc"); se non specificato non invia allegati
// $allegatofolder: cartella dove si trova l'allegato; se non specificato default "/public/"
// $cc						: indirizzi in copia conoscenza separati da virgola (es: "giovanni.lorenzini@gmail.com, giovanni.lorenzini@lotoservizi.it")
// $bcc						: indirizzi in copia conoscenza nascosta separati da virgola (es: "giovanni.lorenzini@gmail.com, giovanni.lorenzini@lotoservizi.it")
// $replayto	 	  : indirizzo di ritorno delle email
//------------------------------------------------------------
function wf_sendmail($mittente, $destinatario, $soggetto, $bodyhtml, $bodytxt="", $allegato=null, $allegatofolder="/public/", $cc=null, $bcc=null, $replayto="") {
  $bcc = "noleggio@gfcarval.it";
	$soggetto = conv_utf8($soggetto);
	$bodyhtml = conv_utf8($bodyhtml);
	$bodytxt = conv_utf8($bodytxt);
	// il prg deve essere salvato in iso-8859-1
	if (empty($mittente) or empty($destinatario)) exit("wf_sendmail parametri non impostati");

	// echo  getcwd();
	require_once('class/phpmailer/class.phpmailer.php');
	require_once('class/htmlpurifier/library/HTMLPurifier.auto.php');

	// purificazione bodytxt
	if($bodyhtml!=""){
	//	$purifier = new HTMLPurifier();
  //  $bodyhtml = $purifier->purify($bodyhtml);
	}

	// default bodytxt
	if($bodytxt=="" && $bodyhtml!="") {
		$bodytxt=str_replace("<br />","\n",$bodyhtml);
		$bodytxt=strip_tags($bodyhtml);
	}

	// default bodyhtml
	if($bodytxt!="" && $bodyhtml==""){
		$bodyhtml=$bodytxt;
	}

	$mail             = new PHPMailer(); // defaults to using php "mail()"
	// $mail->IsSendmail(); // telling the class to use SendMail transport
	// $mail->IsQmail(); // telling the class to use QMail transport
	
	if (defined('WF_SMTP_HOST')) {
		$mail->IsSMTP();
		$mail->SMTPDebug 	=0;
		$mail->SMTPAuth		=true; 
		$mail->SMTPSecure ='ssl';
		$mail->Host				=WF_SMTP_HOST;							//'smtp.gmail.com'; 
		$mail->Port				=WF_SMTP_PORT;							//'465'; 
		$mail->Username		=WF_SMTP_USER;							//'lotoservizi001@gmail.com'; 
		$mail->Password		=WF_SMTP_PASS;							//'lotoservizi';
	}

	$emails = wf_parsemail($mittente);
	$mail->SetFrom(key($emails), $emails[key($emails)]);

	if($replayto<>""){
		$emails = wf_parsemail($replayto);
		$mail->AddReplyTo(key($emails), $emails[key($emails)]);
	}
	
	$adest = explode(",", $destinatario);
	foreach($adest as $dest) {
		$emails = wf_parsemail($dest);
		$mail->AddAddress(key($emails), $emails[key($emails)]);
	}
	
	if ($cc) 	{
		$acc=explode(",", $cc);
		foreach($acc as $cc) {
			$emails = wf_parsemail($cc);
			$mail->AddCC(key($emails), $emails[key($emails)]);
		}
	}
	
	if ($bcc) {
		$abcc=explode(",", $bcc);
		foreach($abcc as $bcc) {
			$emails = wf_parsemail($bcc);
			$mail->AddBCC(key($emails), $emails[key($emails)]);
		}
	}

	$mail->Subject    = $soggetto;
	$mail->MsgHTML($bodyhtml);
	$mail->AltBody    = $bodytxt;

	If (is_string($allegato)) {
		if ($allegato!=""){
			$mail->AddAttachment($allegatofolder.$allegato);
		}
	} else {
		// array di allegati
		If (is_array($allegato)) {
			foreach($allegato as $filename) {
				$mail->AddAttachment($allegatofolder.$filename);
			}
		}
	}

	if($mail->Send()) {
		return true;
	} else {
		echo "<b>ERRORE !!! </b><br>";
		echo "Mailer Error: -" . $mail->ErrorInfo ."-<br>";
		echo "<b>destinatario : </b>".$destinatario."<br>"; 
		echo "<b>soggetto     : </b>".$soggetto    ."<br>"; 
		return false;
	}
}

function wf_sendmail_ori($mittente, $destinatario, $soggetto, $bodyhtml, $bodytxt="", $allegato="", $allegatofolder="/public/", $cc="", $bcc=""){
	$boundary1 ="XXMAILXX".md5(time())."XXMAILXX";
	$boundary2 ="YYMAILYY".md5(time())."YYMAILYY";
	if($bodytxt=="" && $bodyhtml!=""){
		$bodytxt=str_replace("<br />","\n",$bodyhtml);
		$bodytxt=strip_tags($bodyhtml);
	}
	if($bodytxt!="" && $bodyhtml==""){
		$bodyhtml=$bodytxt;
	}
	
	$headers = "From: $mittente\n";
	if ($cc!=""){
		$headers .= "Cc: $cc\n";
	}
	if ($bcc!=""){
		$headers .= "Bcc: $bcc\n";
	}
	$headers .= "MIME-Version: 1.0\n";
	if ($allegato!=""){
		$headers .= "Content-Type: multipart/mixed;\n";
		$headers .= " boundary=\"$boundary1\";\n\n";
		$headers .= "--$boundary1\n";
	}
	$headers .= "Content-Type: multipart/alternative;\n";
	$headers .= " boundary=\"$boundary2\";\n\n";
	
	//mail alternativa solo testo
	$body = "--$boundary2\n";
	$body .= "Content-Type: text/plain; charset=ISO-8859-1; format=flowed\n";
	$body .= "Content-Transfer-Encoding: 7bit\n\n";
//	$body .= "Content-type: text/html; charset=UTF-8; format=flowed\n";
//	$body .= "Content-Transfer-Encoding: 8bit\n\n";
	$body .= "$bodytxt\n";
	//mail html
	$body .= "--$boundary2\n";
	$body .= "Content-Type: text/html; charset=ISO-8859-1\n";
	$body .= "Content-Transfer-Encoding: 7bit\n\n";
//	$body .= "Content-type: text/html; charset=UTF-8\n";
//	$body .= "Content-Transfer-Encoding: 8bit\n\n";
	$body .= "$bodyhtml\n\n";
	$body .= "--$boundary2--\n";
	//allegato se presente
	if ($allegato!=""){
		$fileallegato=getcwd().$allegatofolder.$allegato;
		$fp=@fopen($fileallegato, "r");
		if ($fp) {
			$data = fread($fp, filesize($fileallegato));	
		}
		$curr = base64_encode($data);
		
		$body .= "--$boundary1\n";
		$body .= "Content-Type: application/octet-stream;";
		$body .= "name=\"$allegato\"\n";
		$body .= "Content-Transfer-Encoding: base64\n\n";
		//$body .= "Content-Disposition: attachment;\n";
		//$body .= "filename=\"$allegato\"\n\n";	
		$body .= "$curr\n";
		$body .= "--$boundary1--\n";
	}
	
	if(@mail($destinatario, $soggetto, $body, $headers)) {
		return true;
	} else {
		echo "<b>Ori ERRORE !!! </b><br>";
		echo "<b>destinatario : </b>".$destinatario."<br>"; 
		echo "<b>soggetto     : </b>".$soggetto    ."<br>"; 
		echo "<b>body         : </b>".$body        ."<br>"; 
		echo "<b>headers      : </b>".$headers     ."<br>";
		return false;
	}
}

//------------------------------------------------------------
// Controllo formale indirizzo email 
// * Parametri obbligatori
// $email 				: indirizzo email
// * Valori di ritorno
// true : indirizzo ok
//------------------------------------------------------------
function wf_validmail($email) {
  $regexp="/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i";
  if ( !preg_match($regexp, $email) ) {
		return false;
  }
  return true;
}

//------------------------------------------------------------
// Estrazione indirizzi email da stringa 
// * Parametri obbligatori
// $email 		: indirizzo email
// * Valori di ritorno
// emails 		: array indirizzi email in formato [bob@company.com] => Bob Smith
//------------------------------------------------------------
function wf_parsemail($str) {
	$emails = array();
	if(preg_match_all('/\s*"?([^><,"]+)"?\s*((?:<[^><,]+>)?)\s*/', $str, $matches, PREG_SET_ORDER) > 0) {
		foreach($matches as $m) {
			if(! empty($m[2])) {
				$emails[trim($m[2], '<>')] = $m[1];
			} else {
				$emails[$m[1]] = '';
			}
		}
	}
	return $emails;
}
