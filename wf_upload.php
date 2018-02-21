<?php
function wf_upload($tabella, $campo, $campoid, $valoreid, $href, $dirdoc="./doc/", $afield=null, $ext_ok="*", $max_w=0, $max_h=0, $dim_max="20000000") {
  /*************************************************
  * gestione upload files
	* ================================================
	* $tabella 			Nome tabella su cui fare update
	* $campo				Nome campo in cui riportare nome del file
	* $campoid			Nome campo da utilizzare come chiave di ricerca
	* $valoreid			Valore chiave da ricercare per fare l'upload
	* $href					URL su cui fare redirect al termine del caricamento
	* $afield				Array associativo (Key=>Val) di campi e valori da inserire nell'update
	* $ext_ok				Estensioni accettate
	* $max_w				Massima larghezza (per immagini)
	* $max_h				Massima altezza (per immagini)
	* $dim_max			Dimesione massima
  **************************************************/
  
  if (! isset($_POST['upload'])) {
		// form di acquisizione nome file
		$self=$PHP_SELF;
    $html .= <<<HTML
		<form method='post' onsubmit='return valid_form_upload(this)' action='$self' name=form_upload enctype='multipart/form-data'>
			<table>
				<tr><td>
					<INPUT type='hidden' name=MAX_FILE_SIZE value=$dim_max />
					Nome file <input type=file name=txt_fileorig size=40 />
					<input type=submit value=carica  class=pulsante name=upload /> 
					<input type=reset  value=annulla class=pulsante name=reset />
				</td></tr>
			</table>
    </form>
HTML;
		
		// ricerca file attualmente caricato
		$sql = "select $campo from $tabella where $campoid = $valoreid";
		$ris = wf_getset($sql, "Errore UPLOAD01");
		$row = wf_set2row($ris);
		$nomefile=$row[$campo];
		$est=substr($nomefile, strrpos($nomefile, '.') +1);
		$video=(strpos("avi,mpg,flv,peg", $est)===false?false:true);
		$estview=(strpos("gif,jpg,png,pdf", $est)===false?false:true);
		$rr=strpos("gif,jpg,png,pdf", $est);
		
		// visualizzazione file attualmente caricato
		if ($nomefile<>"" and ! $video and $estview) {
			$viewfile="<iframe src='$nomefile' style='width:600px; height:400px; border: 1px solid #000' frameborder='0'></iframe>";
		} else {
			$viewfile="<a href='$nomefile'>$nomefile</a>";
		}
			$html .= "<table>
								<tr><td>File attuale</td></tr>
								<tr><td>$viewfile</td></tr>
								</table>";
  } else {
		// trattamento file selezionato

    // controlli
		$errore=false;
		$desc_errore="nessun errore";
		// controllo estensione
		$est = substr($_FILES['txt_fileorig']['name'], strrpos($_FILES['txt_fileorig']['name'], '.') +1);
		
		$est = strtolower($est);
		$ext_ok = strtolower($ext_ok);
		
		if ($ext_ok<>"*") {
			$pos=strpos($ext_ok, $est);
			if ($pos === false) {
				$errore=true;
				$desc_errore="Errore. I file ammessi debbono avere una estensione : ".$ext_ok;
				$desc_errore.=$_FILES['txt_fileorig']['name']." - ".$est;
			}
		}
		if ($errore==true) { return ($desc_errore); }
		
		// sostituzione delle "\\" in "'/"
    if(!isset($_SERVER['DOCUMENT_ROOT'])){
      if(isset($_SERVER['SCRIPT_FILENAME'])){
       $_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0-strlen($_SERVER['PHP_SELF'])));
      };
    };
    if(!isset($_SERVER['DOCUMENT_ROOT'])){
      if(isset($_SERVER['PATH_TRANSLATED'])){
        $_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0-strlen($_SERVER['PHP_SELF'])));
      };
    };

    // generazione numero documento
    $progdoc = wf_readsdati("progdoc") + 1;
    $sql = "UPDATE sdati SET valoresdati = $progdoc WHERE keysdati = 'progdoc'";
		$ris = wf_getset($sql, "Errore UPLOAD02");
    $progdoc = wf_ntoc($progdoc, 8);

		// memorizzazione documento
    $dest = $dirdoc . $progdoc . '.' . $est;
    $valdb= $dirdoc . $progdoc . '.' . $est;
    $orig = $_FILES['txt_fileorig']['tmp_name'];
    $nome = $_FILES['txt_fileorig']['name'];
		
		// verifica tipo di file
		$filetype=$_FILES['txt_fileorig']['type'];
		$isimg=strpos("image/jpeg,image/pjpeg,image/gif,image/x-png", $filetype);
    
		if ($isimg===false or ($max_w==0 and $max_h==0)) {
			// spostamento del file senza ridimensionamenti
			$ok=move_uploaded_file($orig, $dest);
		} else {
			// richiesta di ridimensionamento
			list($width, $height, $type, $attr) = getimagesize($orig);																							// ottiene dimensioni dell'immagine originale
			$pro = $width/$height;																																									// calcola le proporzioni dell'immagine
			if ($max_w>0) {																																													// ridimensionamento in base alla larghezza
				$new_width =$max_w;																																										// nuova larghezza
				$new_height=round($max_w/$pro);																																				// nuova altezza
			} else {																																																// ridimensionamento in base all'altezza
				$new_height = $max_h;																																									// nuova altezza
				$new_width = round($max_h*$pro);																																			// nuova larghezza
			}
			
			$image_new = imagecreatetruecolor($new_width, $new_height);																							// crea una immagine vuota delle dimensioni volute
			$image_old = imagecreatefromjpeg($orig);																																// carica la vecchia immagine
			$ok=imagecopyresampled($image_new, $image_old, 0, 0, 0, 0, $new_width, $new_height, $width, $height);		// carica sull'immagine nuova quella vecchia ridimensionandola
			$ok=($ok and imagejpeg($image_new,$dest,100));																													// scrive l'immagine
		}
		
    if ($ok) {
			// caricamento avvenuto con successo
			if ($afield) {
				foreach($afield as $key=>$val) {
					$set=",".$key."=".$val;
				}
			}
      $sql = "update $tabella set $campo = '".$valdb."' $set where $campoid = $valoreid";
			$ris = wf_getset($sql, "Errore UPLOAD03");
      $html .= "<br />Caricamento file ".$_FILES['txt_fileorig']['name']." avvenuto con successo !";

			// visualizzazione file caricato
			if ($estview) {
				$html .= '<table>';
				$html .= "<tr><td>File caricato </td></tr>";
				$html .= "<tr><td><iframe src='$valdb' style='width:600px; height:500px; border: 1px solid #000' frameborder='1'></iframe>></td></tr>";
				$html .= '</table>';
			}

      $html .="
        <script type='text/javascript'>
          setTimeout(\"location.href=\'".$href."';\",5000);
        </script>";
    } else {
      $html .= "<br />Si sono verificati dei problemi durante l'Upload<br /> Orig : $orig - $nome<br />Dest: $dest";
      $html .= "<br />Per immagini : w $width, h $height, t $type, a $attr";
    }
  }

  return $html;
}