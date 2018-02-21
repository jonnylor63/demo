<?php
/* ******** FROM TABELLARE ********
* Parametri
* $sqlid        generatore puntatore che identifica il rk (nella query potrà essere pintato grazie alla variabile $id ---> attenzione nome di variabile troppo generico
* $sqledit      passo sql di selezione dei rk da visualizzare
* $acampie      array di descrizione delle colonne della tabella contenente i seguenti campi :
*               0. label di testata
*               1. tipo di gestione valore : view - sola visualizzazione; checkbox, radio, text, ecc campi di form
*               2. dimensione campo form (non utilizzato nella view)
*               3. ???
*               4. nome campo $_POST
*               5. generatore valore campo
*               6. extra
* $aSqlProcess  array passi sql da processare alla conferma della form; ogni valore ha i seguenti due campi :
*               1. passo sql (il valore di $id o $wf_mf_id riportato nel passo SQL è pari all'idenificativo del record impostato con SqlID)
*               2. generatore di verifica sulla condizione di esecuzione
*               ad ogni passo Sql viene accodato un valore della nuova chiave generata nell'array $newid[]; 
* $PostProcess  azione da eseguire al termine delle attività; se non specificato rilancia la pagina in href
* $FuncProcess  funzione da lanciare ad ogni record es: "m2c11(\$invia,\$id)" dove $invia è il checkbox di selezione e $id è l'identificatore derivato da $sqlid
* $beforepaging html introdotto in testa alla form
* ================================
* 31/08/2011	corretto un malfunzionamento nella gestione dei radio button (era presente una nuova riga che sembra non poter funzionare)
* 06/11/2011	introdotto $beforepaging : html da ineserire prima della form
********************************** */
function wf_multiform($sqlid, $sqledit, $acampie, $aSqlProcess, $PostProcess, $FuncProcess, $beforepaging="") {
	$html.="<div class=wf_multiform>";
	
	/* LETTURA DATI */
	if (substr($sqledit, 0,1)=='"') {
		eval("\$sql = $sqledit;");
	} else {
		eval("\$sql = \"$sqledit\";");
	}
	if ($sql) {
		$ris = wf_getset($sql, "Errore MULFOR01");
	} else {
		wf_error("SQL non valido", $sqledit);
	}

		/* GESTIONE RISULTATO FORM */
  if ($_POST[frm_dadove]=="upd") {
		$ok=true;
    // RICRECA CAMPI CHIAVE
    foreach ($_POST as $key => $value) {
      // if ($_SESSION[debug]) echo "<br>key => value ". $key ." => ".$value;
			
      if (substr($key,0,10)=="frm_idrek_") {
        $id=substr($key,10);
        $wf_mf_id=substr($key,10);
        
        // creazione variabili per singolo record
        foreach ($acampie as $campo) { 
          if ($campo[1]<>"view") {
            $field=$campo[4]."_".$id;																// $campo[4] : nome del campo
						// if ($_SESSION[debug]) echo "<br>field ".$field;
						
            if (($campo[1]=="checkbox" or $campo[1]=="radio") and  ! $_POST[$field]) {
              $v=0;
            } else {
              $v=$_POST[$field];
            }
            // Creazione variabile
            eval("\$".$campo[4]." = ".$v.";");
            eval("\$wf_mf_".$campo[4]." = '".$v."';");
            
          }
        }	

        if ($aSqlProcess==null){
          eval("\$msg .= ".$FuncProcess.";");
        }else{
          // esecuzione passi SQL condizionati
          foreach ($aSqlProcess as $prog => $aSqlProc) {
            eval("\$cond = ($aSqlProc[1]);");
            if ($_SESSION[debug]) echo "<br>cond ".$cond;
            if ($cond) {
              $aSqlProc[0]=str_replace('{id}',$id,$aSqlProc[0]);
              
              $sql="nessun risultato<br>";
              eval("\$sql = $aSqlProc[0];");
              if ($_SESSION[debug]) echo "<br>sql ".$sql;
              
							$ris = wf_getset($sql, "Errore MULFOR02");
              $ni=wf_getid();
              $newid[]=$ni;
            }
          }
        }        
      } 
    }
    
    // Lancio pagina successiva
    if (! $_SESSION[debug]) {
      if ($PostProcess) {
      	eval("\$href = ".$PostProcess.";");
      } else {
      	$href=$_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"];
      }  
			Header("Location: ".$href);
    }

    $html = ".<center><br><br><br>$msg $href<br><br><br>controlla gli echo ! <br><br><br><br><br></center>";
	}

	/* DISEGNO FORM DATI */
	if (! $ok) {
		$html .= "<form method=post name=frm id=frm> \n";
		$html .= $beforepaging;
		$html .= "<table class=elenco>  \n";

		/* Intestazione colonne */
		$html .= "<tr class=wf_head> \n";
		foreach ($acampie as $campo) { 
			if ($campo[1]=="checkbox") {
				$html .="<td><center><a href=\"javascript:wf_toggle('".$campo[4]."')\">".$campo[0]."</a></center></td> \n";
			} else {
				$html .="<td>".$campo[0]."</td> \n"; 
			} 
		}

		/* ciclo dati */
    $class="wf_odd";
  	while ($row = wf_set2row($ris)) {
      eval("\$id = $sqlid;");
			$html .= "<tr class=$class> <input type=hidden name='frm_idrek_$id' value='$id'>  \n";
      $class=($class=="wf_odd"?"wf_even":"wf_odd");

			foreach ($acampie as $campo) {
				/* analisi campi */
				eval("\$field = $campo[5];");			// $campo[5] : valore del campo $row[...]

        $extra="";
				eval("\$extra = $campo[6];");
//				$extra=($campo[6]?$campo[6]:"");

				if ($campo[1]=="view") {
					$html .="<td>".$field."</td> \n"; 

				} elseif ($campo[1]=="text" or $campo[1]=="password" or $campo[1]=="hidden" or $campo[1]=="file") {
					$html .= "<td><input type='$campo[1]' size=$campo[2] name='$campo[4]_$id' id='$campo[4]_$id' value='$field' title='".strip_tags($campo[0])."' $extra> <span class=wf_errori>".$aErr[$i]."</span></td> \n";

        } elseif ($campo[1]=="checkbox") {
          $chk=($field==1?"CHECKED":"");
          $html .= "<td><center><input type='$campo[1]' name='$campo[4]_$id' id='$campo[4]_$id' class='$campo[4]' value='1' title='".strip_tags($campo[0])."' $chk $extra> <span class=errori>".$aErr[$i]."</span></center></td> \n";

        } elseif ($campo[1]=="radio" ) {
          $chk=($field==1?"CHECKED":"");
					$html .= "<td><center><input type='$campo[1]' name='$campo[4]_$id' id='$campo[4]_$id' value='$field' title='".strip_tags($campo[0])."' $chk $extra> <span class=errori>".$aErr[$i]."</span></center></td> \n";
          // * nuovo ma non funziona ... verificare... $html .= "<td><center><input type='$campo[1]' name='$campo[4]' id='$campo[4]' value='$id' $chk $extra> <span class=errori>".$aErr[$i]."</span></center></td> \n";
        }
			}
			$html .= "</tr> \n";
		}

		$html .= "</table> \n";
//		$html .= "<input class='BottoneMax' type=image src='".WF_DIR."go.gif' name=cmd_invia> \n";
		$html .= "<input class='wf_bottone' type='submit' title='Procedi' 	value='Procedi' 	name=cmd_invia> ";
		$html .= "<input class='wf_bottone' type='button' title='Annulla' 	value='Annulla' onclick='javascript:history.go(-1);'> ";
		$html .= "<input type=hidden name='frm_dadove' value='upd'> \n";
		$html .= "<input type=hidden name='fld_action' value='$ViewType'> \n";
		$html .= "<input type=hidden name='fld_id' value='$_POST[fld_id]'> \n";
		$html .= "</form> \n";
	}
	$html .="</div>";
	
	// abilita/disabilita tutto
	$html.="
	<SCRIPT type='text/javascript'>
	  function wf_toggle(id) {
	  	var flg;
	  	$('.'+id).each(function(index, item) {
		  	var checked = $(this).attr('checked');
				if(isNaN(flg)) {
					flg=(checked=='checked');
				}
				
				if(flg){
		  		$(this).attr('checked', false);
				} else {
		  		$(this).attr('checked', true);
				}
	  	});
	  }
	</SCRIPT>
  ";

	return ($html);
}
