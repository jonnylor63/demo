<?php
//------------------------------------------------------------
// Import dati da file esterni in formato CSV
// * Parametri obbligatori
// $filename 		: percorso completo del file da importare
// $idaimpt	    : id della definizione dell'import presente nella tabella aimpt
// * Parametri facoltativi
// nessuno
//------------------------------------------------------------
// Richiede tabelle aimpt e aimpd
//------------------------------------------------------------
function wf_import_csv($idaimpt, $filename=null) {
	ini_set("auto_detect_line_endings", "1");
  // lettura intestazione
	$sql = "select * from aimpt where idaimpt=$idaimpt";
  $row = wf_getrow($sql, "wf_import_csv 001");
  if ($row["idaimpt"]<>$idaimpt) 
  	return array("error" => true, "code" => "001", "descr" => "Record definizione import non trovato");

	// parametri di importazione
	$tipo=$row["tipoaimpt"];
	$sepa=($row["sepaaimpt"]=="\\t"?"\t":$row["sepaaimpt"]);		// separatore campi
	$deli=$row["deliaimpt"];																		// delimitatore testi
	$inte=$row["inteaimpt"];																		// numero linee di intestazione
	$filename=($filename?$filename:$row["fileaimpt"]);

	// preparazione campi di destinazione
  $sql = "
	  select * 
	  from aimpd 
	  left join afilp on aimpd.idafilp=afilp.idafilp 
	  where idaimpt = $idaimpt 
	  order by tableafilp, fieldafilp";
  $ris = wf_getset($sql, "wf_import_csv 002");
	while ($row = wf_set2row($ris)) {
		$sqlf.=$row[fieldafilp].",";
		$acampi[$row[fieldafilp]][colonna]  = $row["colidaimpd"];
		$acampi[$row[fieldafilp]][costante] = $row["costaimpd"];
		$acampi[$row[fieldafilp]][daeval]   = $row["evalaimpd"];
	}
	$sqlf=substr($sqlf,0,-1);

	// lettura file
  if (!file_exists($filename)) 
  	return array("error" => true, "code" => "002", "descr" => "File di import non trovato");

	$handle = @fopen($filename, "r");
  if (!$handle) 
  	return array("error" => true, "code" => "003", "descr" => "File di import non leggibile");

	$nrow=0;
	$sql="insert into tanag_import ($sqlf) values ";
	if ($handle) {
    while (($csv = fgetcsv($handle, 4096, $sepa)) !== false) {
      // conversione caratteri ad ansi
      foreach($csv as $key => $val) {
        $csv[$key]=iconv("UTF-8", "ISO-8859-1", $val);
        $csv[$key]=addslashes($csv[$key]);
      }

			$nrow++;
			if ($nrow>$inte) {										// esclude righe intestazone
				foreach($acampi as $campo) {

					// caricamento diretto da colonna input
					if ($campo[colonna]>0) {
						$col=$campo[colonna]-1;
						$sqlv.=($csv[$col] ? "'".$csv[$col]."'" : 'null').",";
						$nonull=($csv[$col]==null?0:1);
						
					// caricamento di una costante
					} elseif ($campo[costante]) {
						$sqlv.='"'.$campo[costante].'",';

					// caricamento di un campo calcolato
					} else {
						$daeval=$campo[daeval];
						for ($i = 1; $i <= 20; $i++) {
							$daeval = str_replace("{".$i."}", $csv[$i-1], $daeval);
						}

						If (FALSE === @eval("\$x=$daeval;")) {
							$errmsg='WF_IMPORT - Errore nella espressione - eval fallita :  <['.$daeval.']>';
							wf_error($errmsg);
						}

						// echo "=>$x<=<br>";
						$sqlv.="'".$x."',";
					}
				}
				if ($nonull>0) {
					// scrive le righe non tutte null
					$sqlv=substr($sqlv,0,-1);
					$sql.="( $sqlv ) ,";
				}
				$sqlv="";
			}
    }
    if (!feof($handle))
  		return array("error" => true, "code" => "004", "descr" => "Anomalie nel file di import");

    fclose($handle);
	}
	$sql=substr($sql,0,-1);
  $ris = wf_getset("truncate table tanag_import", "wf_import_csv 003");
  $ris = wf_getset($sql, "wf_import_csv 003");

  // duplicati di importazione
  $sql="
  select 
  tanag.idtanag, tanag.emailprivtanag, tanag_import.emailprivtanag ema 
  from tanag_import
	left join tanag using (emailprivtanag) 
	where tanag.emailprivtanag is not null";
  $set = wf_getset($sql, "wf_import_csv 004");
  while ($row = wf_set2row($set)) {
  	echo "id: $row[idtanag], email: $row[emailprivtanag] - id: $row[id], email: $row[ema]<br>";
  }

  $sql="
  insert into tanag
  select tanag_import.* 
  from tanag_import
	left join tanag using (emailprivtanag) 
	where tanag.emailprivtanag is null";
  $set = wf_getset($sql, "wf_import_csv 004");
  
  return array("error" => false, "code" => "000", "descr" => "Tutto ok");
}

/*****************************************************************************
/ Funzione 		: wf_import_csv
/ Descrizione : import TANAG versione 1
/ Parametri :
/ 	$par1				paramentro 1
/*****************************************************************************/
function wf_import_csv_001($idaimpt, $filename=null) {
	ini_set("auto_detect_line_endings", "1");
  // lettura intestazione
	$sql = "select * from aimpt where idaimpt=$idaimpt";
  $row = wf_getrow($sql, "wf_import_csv 001");
  if ($row["idaimpt"]<>$idaimpt) 
  	return array("error" => true, "code" => "001", "descr" => "Record definizione import non trovato");

	// parametri di importazione
	$tipo=$row["tipoaimpt"];
	$sepa=($row["sepaaimpt"]=="\\t"?"\t":$row["sepaaimpt"]);		// separatore campi
	$deli=$row["deliaimpt"];																		// delimitatore testi
	$inte=$row["inteaimpt"];																		// numero linee di intestazione
	$filename=($filename?$filename:$row["fileaimpt"]);

	// preparazione campi di destinazione
  $sql = "
	  select * 
	  from aimpd 
	  left join afilp on aimpd.idafilp=afilp.idafilp 
	  where idaimpt = $idaimpt 
	  order by tableafilp, fieldafilp";
  $ris = wf_getset($sql, "wf_import_csv 002");
	while ($row = wf_set2row($ris)) {
		$sqlf.=$row[fieldafilp].",";
		$acampi[$row[fieldafilp]][colonna]  = $row["colidaimpd"];
		$acampi[$row[fieldafilp]][costante] = $row["costaimpd"];
		$acampi[$row[fieldafilp]][daeval]   = $row["evalaimpd"];
	}
	$sqlf=substr($sqlf,0,-1);

	// lettura file
  if (!file_exists($filename)) 
  	return array("error" => true, "code" => "002", "descr" => "File di import non trovato");

	$handle = @fopen($filename, "r");
  if (!$handle) 
  	return array("error" => true, "code" => "003", "descr" => "File di import non leggibile");

	$nrow=0;
	$sql="insert into tanag_import ($sqlf) values ";
	if ($handle) {
    while (($csv = fgetcsv($handle, 4096, $sepa)) !== false) {
      // conversione caratteri ad ansi
      foreach($csv as $key => $val) {
        $csv[$key]=iconv("UTF-8", "ISO-8859-1", $val);
        $csv[$key]=addslashes($csv[$key]);
      }

			$nrow++;
			if ($nrow>$inte) {										// esclude righe intestazone
				foreach($acampi as $campo) {

					// caricamento diretto da colonna input
					if ($campo[colonna]>0) {
						$col=$campo[colonna]-1;
						$sqlv.=($csv[$col] ? "'".$csv[$col]."'" : 'null').",";
						$nonull=($csv[$col]==null?0:1);
						
					// caricamento di una costante
					} elseif ($campo[costante]) {
						$sqlv.='"'.$campo[costante].'",';

					// caricamento di un campo calcolato
					} else {
						$daeval=$campo[daeval];
						for ($i = 1; $i <= 20; $i++) {
							$daeval = str_replace("{".$i."}", $csv[$i-1], $daeval);
						}

						If (FALSE === @eval("\$x=$daeval;")) {
							$errmsg='WF_IMPORT - Errore nella espressione - eval fallita :  <['.$daeval.']>';
							wf_error($errmsg);
						}

						// echo "=>$x<=<br>";
						$sqlv.="'".$x."',";
					}
				}
				if ($nonull>0) {
					// scrive le righe non tutte null
					$sqlv=substr($sqlv,0,-1);
					$sql.="( $sqlv ) ,";
				}
				$sqlv="";
			}
    }
    if (!feof($handle))
  		return array("error" => true, "code" => "004", "descr" => "Anomalie nel file di import");

    fclose($handle);
	}
	$sql=substr($sql,0,-1);
  $ris = wf_getset("truncate table tanag_import", "wf_import_csv 003");
  $ris = wf_getset($sql, "wf_import_csv 003");

  // duplicati di importazione
  $sql="
  select 
  tanag.idtanag, tanag.emailprivtanag, tanag_import.emailprivtanag ema 
  from tanag_import
	left join tanag using (emailprivtanag) 
	where tanag.emailprivtanag is not null";
  $set = wf_getset($sql, "wf_import_csv 004");
  while ($row = wf_set2row($set)) {
  	echo "id: $row[idtanag], email: $row[emailprivtanag] - id: $row[id], email: $row[ema]<br>";
  }

  $sql="
  insert into tanag
  select tanag_import.* 
  from tanag_import
	left join tanag using (emailprivtanag) 
	where tanag.emailprivtanag is null";
  $set = wf_getset($sql, "wf_import_csv 004");
  
  return array("error" => false, "code" => "000", "descr" => "Tutto ok");
}
?>