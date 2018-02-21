<?php
/*************************************************
' function...: wf_mutimput
' utilizzo...: gestione tabellare
' autore.....: gl
' progetto...: webfunc v1
'
' Data     Au Note
' 24.11.09 GL ...
**************************************************/

/* ******* GESTIONE TABELLE *******
* Parametri
* $ViewType     modalità di avvio => b=browse; i=insert; e=edit; r=read; d=delete; p=print
* $sqlid        identificativo rk in trattamento (chiave primaria); (es: $sqlid='$row[idtboll]'; ) nel sql di insert o update usere $_POST[fld_id] che contiene il valore della chiave primaria
* $sqlread      comando sql che mette a disposizione le colonne da portare in lettura
* $acampib      array campi browse con i seguenti valori:
*               1. Intestazione della colonna (es: 'ID' )
*               2. Contenuto cella (es: '"$row[idsmenu]"' oppure '"<a href=index.php?proc=menu&id=$row[idsmenu]>abilitazioni</a>"')
*
* $RowPaging    numero delle righe di visualizzazione; 0 fa vedere tutte le righe e non visualizza il paginatore
*
* $Grant        definizione dei diritti. 
*               modalità carattere (es: "crudpxsn") con cui specificare 
*									c=create, r=read, u=update, d=delete, p=print, x=export, s=hiddenSearch, n=newfrom dup, f=fixlayout; 
*               modalità array es1:
*                 $grant[]="ru";																						// autorizzazioni
*                 $grant[]=array("crea.gif","modifica.gif","cancella.gif");	// immagini
*                 $grant[]=array("creami","modificami","cancellami");				// tooltip
*                 $grant[]=array("","","deleok.php?cliente&id=$id");				// ajax di controllo
*               modalità array es2:
*                 $grant[]="crud";
*                 $grant[]="";
*                 $grant[]="";
*                 $grant[]=array("","editok.php?id=cliente&$id","deleok.php?cliente&id=$id");
*
*               1° valore equivalente a modalità carattere
*               2° valore sotto array di immagini da associare ai pulsanti rispettivamente di create, update, delete
*               3° valore sotto array di descrizioni da associare alle immagini 
*               4° valore sotto array di chiamate ajax di verifica correttezza operazione. (parametro controllo insert non ancora gestito)
*									 Se la chiamata restituisce 1 si procede altrimenti operazione bloccata e visualizzazione messaggio con valore di ritorno 
*
* $sqledit      comando sql che mette a disposizione le colonne da portare in edit
*               es 1: $sqledit='"SELECT * FROM triti, aprod WHERE triti.idaprod=aprod.idaprod AND idtriti=\"$_POST[fld_id]\""'; 
*               es 2: $sqledit="SELECT * FROM triti, aprod WHERE triti.idaprod=aprod.idaprod AND idtriti='$_POST[fld_id]'"; 
* $acampie      array campi da editare con i seguenti valori:

*               (Label, Type, Lenght, Row,

*               1. Label campo (es: 'ID' )
*               2. Tipo campo : 
*                 "text"      campo classico
*                 "textarea"  campo testo multiriga
*                 "date"      verifica la corretteza della data e mostra una icona di calendario a fianco
*                 "option"    visualizza un menu a tendina predefinito
*                 "view"      non permette l'editing del campo ma visualizza la label
*                 "label"     visualizza solo la label e non il campo
*                 "complete"  visualizza un menu a tendina dinamico (AJAX)
*                 "hidden"    non visualizza il campo ma permette la gestione del contenuto (da usarsi con autocomplete)
*                 "checkbox"  visualizza un checkbox
*                 "radio"     visualizza un radiobutton
*                 "FCKeditor" visualizza un editor html
*                 "html" 			visualizza un editor html
*               3. lunghezza del campo (per : text,textarea,date,option,view,complete,html) valori da 1 a 12 (vedi grid bootstrap)
*                  valore associato (per : radio)
*               4. riga della form (0 tutte righe diverse) - non è necessario che siano conseguenti
*               5. nome campo (es: 'fld_barcode') da usare nei passi sql (es: $_POST[fld_barcode])
*               6. prelievo valore campo (es: '$row[barcode]'); il campo deve essere presente nella select $sqledit; non è possibile speciticare tabella.campo (es: 'triti.idtriti')
*               7. espressione di controllo della validità del campo; deve tornare true, altrimenti blocca edit 
*                   modalità carattere (es: '$_POST[fld_matricola]' vuol dire che il campo non deve essere vuoto, altro esempio '$_POST[fld_numero]>20')
*                   modalità array (es: array('$_POST[fld_matricola]','$_POST[fld_numero]>20') ) la prima condizione che risulta falsa blocca il processo; viene visualizzata la label di errore corrispondente
*               8. label di segnalazione errore 
*                   modalità carattere (es: 'Inserire Matricola')
*                   modalità array (es: array('Inserire Matricola','Numero troppo basso') )
*               9. EXTRA : espressione che viene inserita dopo il tag input; usata principalmente per controlli javascript (es: "onblur='isDate(this)'")
*               10. se il tipo è option riporta i valori della tendina con la notazione : "val1:des1;val2:des2;...". 
*										E' possibile usare la funzione wf_OptionTable per la generazione della variabile (es: $oAprob=wf_OptionTable("aprob", "idaprob:descraprob");) 
*										E' possibile avere una option a 2 livelli con "val1:des1:gruppo1;val2:des2:gruppo1;val3:des3:gruppo2;..." NB: attualmente la wf_OptionTable non è predisposta per generare LOV a più livelli
* 									Se alla fine della stringa opzioni si interpone un | seguito dalla procedura di gestione della tabella questa verra aperta in una nuova finsestra es: val1:des1;val2:des2|procgest
*								11. complete : url da chiamare per l'estrazione dei dati
*
* $asqlinsert   array di comandi sql di inserimento
*               modalità carattere 1 : contiene solo l'sql (es: $asqlinsert[]='"INSERT INTO atilo (idatilo,	descratilo) VALUES ($_POST[fld_idatilo], \"$_POST[fld_descratilo]\")"'; )
*               modalità carattere 2 : contiene solo l'sql (es: $asqlinsert[]="INSERT INTO atilo (idatilo,	descratilo) VALUES ($_POST[fld_idatilo], '{$_POST[fld_descratilo]}')"; )
*               modalità array : array contenetnte il comando sql e la condizioni di lancio; $asqlinsert[]=array('"INSERT INTO atilo (idatilo,	descratilo) VALUES ($_POST[fld_idatilo], \"$_POST[fld_descratilo]\")"';, '$_POST[fld_idatilo]>0'); )
* $asqlupdate   array di comandi sql di aggiornamento (es 1: $asqlupdate[]='"UPDATE rlobr SET idabren=$_POST[fld_idabren], idsloca=\"$_POST[fld_idsloca]\" WHERE idrlobr=$_POST[fld_id]"'; )
*                                                     (es 2: $asqlupdate[]="UPDATE rlobr SET idabren=$_POST[fld_idabren], idsloca='$_POST[fld_idsloca]' WHERE idrlobr=$_POST[fld_id]"; )
* $asqldelete   array di comandi sql di cancellazione (es 1: $asqldelete[]='"DELETE FROM rlobr WHERE idrlobr=$_POST[fld_id]"'; )
*                                                     (es 2: $asqldelete[]="DELETE FROM rlobr WHERE idrlobr=$_POST[fld_id]"; )
* $apostprocess array di URL da lanciare dopo il processo; l'indice dell'array indica a fronte di quale processo lanciare l'URL -> "e" per edit; "i" per insert; "d" per delete (es: $apostprocess[e]='"index.php?proc=accessori&tipo=edit&id=$_POST[fld_id]"';
* $schema				db di accesso
* $multikey			chiave identificativa multinput; necessaria in caso di visualizzazione di più multinput contemporanee
*
**********************************
* 09/01/10 Avviata realizzazione stampa; introdotto parametro "p"
* 16/01/10 Realizzato sistema di controllo ajax sulla crud
* 23/11/11 Rimosso passaggio variabili by-reference deprecato
* 31/08/12 convertita chiamata ajax a jQuery
* 02/11/12 introdotto layout fix (paramentro f in grant)
* 05/11/12 convertito autocomplete a jQuery
********************************** */

/********
* migrare da fckeditor a tinymce
Prendere esempio da edithtml() in g_mail.php
*
*********/

function wf_multinput($ViewType, $sqlid, $sqlread, $acampib, $RowPaging, $Grant, $sqledit, $acampie, $asqlinsert, $asqlupdate, $asqldelete, $apostprocess, $nsearch=null, $beforepaging="", $schema="default", $multikey=null, $urlinsert=null) {
	$ini ="\n <!-- inizio multinput --> \n";
	if (! $nsearch) {
		$nsearch=1;
	}

	// Variabili GET
	$wfact		=$_GET["wfact".$multikey];			// azione richiesta
	$wfid			=$_GET["wfid".$multikey];				// id record in processing
	$wfpag		=$_GET["wfpag".$multikey];			// pagina di browse
	$wfsearch	=$_GET["wfsearch".$multikey];		// stringa di ricerca
	$wfsearch2=$_GET["wfsearch2".$multikey];	// 2° stringa di ricerca		
	
	//wftkn
	
	if ($wfact) {
		$ViewType=($wfact?$wfact:$ViewType);
	} else {
		//$ViewType=($_POST[fld_action]?$_POST[fld_action]:$ViewType);
		if ($_POST[frm_multikey]==$multikey and $_POST[fld_action]) {
      $ViewType=$_POST[fld_action];
    }
	}

	// controllo accessi con premessi
	if (is_array($Grant)) {
		$crud=$Grant[0];
	} else {
		$crud=$Grant;
	}
	global $fixlayout;
	if ( (strpos(strtolower($crud), "c")===false) or ! $_SESSION["s_flgcreate"]	) {$create=false;			} else {$create=true;			}
	if ( (strpos(strtolower($crud), "r")===false) or ! $_SESSION["s_flgread"]		) {$read=false;  			} else {$read=true;				}
	if ( (strpos(strtolower($crud), "u")===false) or ! $_SESSION["s_flgupdate"]	) {$update=false;			} else {$update=true;			}
	if ( (strpos(strtolower($crud), "d")===false) or ! $_SESSION["s_flgdelete"]	) {$delete=false;			} else {$delete=true;			}
	if ( (strpos(strtolower($crud), "p")===false) or ! $_SESSION["s_flgprint"]	) {$print=false; 			} else {$print=true;			}
	if ( (strpos(strtolower($crud), "x")===false) or ! $_SESSION["s_flgexport"]	) {$export=false;			} else {$export=true;			}
	if ( (strpos(strtolower($crud), "n")===false) or ! $_SESSION["s_flgnew"]		) {$duplicate=false; 	} else {$duplicate=true;	}
	if ( (strpos(strtolower($crud), "s")===false)  															) {$viewric=true;			} else {$viewric=false;		}
	if ( (strpos(strtolower($crud), "f")===false)  															) {$fixlayout=false;	} else {$fixlayout=true;	}

	$crud="";
	$crud.=($create			?	"c"	:	"");
	$crud.=($read 			?	"r"	:	"");
	$crud.=($update 		?	"u"	:	"");
	$crud.=($delete 		?	"d"	:	"");
	$crud.=($print			?	"p"	:	"");
	$crud.=($export 		?	"x"	:	"");
	$crud.=($viewric		?	""	:	"s");
	$crud.=($duplicate	?	"n"	:	"");
	if (is_array($Grant)) {
		$Grant[0]=$crud;
	} else {
		$Grant=$crud;
	}

	if ($ViewType=='b') {
		$html = wf_BrowseTable($sqlid, $sqlread, $acampib, $RowPaging, $Grant, $apostprocess, $nsearch, $beforepaging, $schema, $multikey, $urlinsert);
	} elseif (
		($ViewType=='i' and $create) or
		($ViewType=='e' and $update) or 
		($ViewType=='r' and $read) ) {
		$html = wf_EditTable($ViewType, $sqlid, $sqlread, $acampib, $sqledit, $acampie, $asqlinsert, $asqlupdate, $asqldelete, $apostprocess, $schema, $multikey, $wfact);
	} elseif ($ViewType=='d' and $delete) {
		$html = wf_DeleteTable($asqldelete, $apostprocess, $schema, $multikey);
	} elseif ($ViewType=='p' and $print) {
		$html .= wf_PrintTable($sqlread, $acampib);
		$html .= wf_BrowseTable($sqlid, $sqlread, $acampib, $RowPaging, $Grant, $apostprocess, $nsearch, $schema, $multikey, $urlinsert);
	} elseif ($ViewType=='x' and $export) {
		$html .= wf_ExportTable($sqlread, $acampib);
		$html .= wf_BrowseTable($sqlid, $sqlread, $acampib, $RowPaging, $Grant, $apostprocess, $nsearch, $schema, $multikey, $urlinsert);
	} else {
		echo "Parametro ($ViewType) inaspettato o azione non autorizzata multikey:$multikey: create:$create: read:$read: update:$update: crud:$crud: s_flgupdate:".$_SESSION["s_flgupdate"].": controlla i permessi";
    exit;
	}

	$fin ="<!-- fine multinput --> \n\n";
	return ($ini.$cale.$html.$fin);
}

// ******* BROWSE *******
function wf_BrowseTable($sqlid, $sqlread, $aCampi, $rowpaging, $grant, $apostprocess, $nsearch, $beforepaging, $schema, $multikey=null, $urlinsert=null) {
	$html="";
	// Riferimenti per gestione inser, edit, ...
	$href=getCurrentURL("wfact$multikey,wfid$multikey", "&");
	$refins="'$href&wfact$multikey='+type+'&wfid$multikey='+id";
	$refupd="'$href&wfact$multikey='+type+'&wfid$multikey='+id";
	$refdel="'$href&wfact$multikey=d&wfid$multikey='+id";
	$refdup="'$href&wfact$multikey='+type+'&wfid$multikey='+id";

	$refins="'$href&wfact'+key+'='+type+'&wfid'+key+'='+id";
	$refupd="'$href&wfact'+key+'='+type+'&wfid'+key+'='+id";
	$refdel="'$href&wfact'+key+'=d&wfid'+key+'='+id";
	$refdup="'$href&wfact'+key+'='+type+'&wfid'+key+'='+id";

	/* gestione autorizzazioni */
  if (gettype($grant)=="array") {
    $iconIns=($grant[1][0]==null ? "plus"					  : $grant[1][0] );
    $iconVie=($grant[1][1]==null ? "search"				  : $grant[1][1] );
    $iconUpd=($grant[1][1]==null ? "pencil"				  : $grant[1][1] );
    $iconDel=($grant[1][2]==null ? "trash"				  : $grant[1][2] );
    $iconDup=($grant[1][4]==null ? "plus-circle"	  : $grant[1][3] );
    
    $desins=($grant[2][0]==null ? "Inserisce un nuovo record" : $grant[2][0] );
    $desupd=($grant[2][1]==null ? "Modifica il record"        : $grant[2][1] );
    $desdel=($grant[2][2]==null ? "Cancella il record"        : $grant[2][2] );
    $desdup=($grant[2][3]==null ? "Duplica il record"         : $grant[2][3] );
    
    $verins=($grant[3][0]);
    $verupd=($grant[3][1]);
    $verdel=($grant[3][2]);
    $verdup=($grant[3][3]);
  
    $refins=($grant[4][0] ? "'".$grant[4][0]. (strpos($grant[4][0], "?")===false ? "?" : "&"). "wfid'+key+'='+id" : $refins);
    $refupd=($grant[4][1] ? "'".$grant[4][1]. (strpos($grant[4][1], "?")===false ? "?" : "&"). "wfid'+key+'='+id" : $refupd);
    $refdel=($grant[4][2] ? "'".$grant[4][2]. (strpos($grant[4][2], "?")===false ? "?" : "&"). "wfid'+key+'='+id" : $refdel);
    $refdup=($grant[4][3] ? "'".$grant[4][3]. (strpos($grant[4][3], "?")===false ? "?" : "&"). "wfid'+key+'='+id" : $refdup);
    
    $grant=$grant[0];
  } else {
		$iconIns="plus"					;
		$iconVie="search"				;
		$iconUpd="pencil"				;
		$iconDel="trash"				;
		$iconDup="plus-circle"	;

    $desins="Crea elemento";
    $desupd="Modifica il record";
    $desdel="Cancella il record";
    $desdup="Duplica il record";
  }
	if (strpos(strtolower($grant), "c")===false) {$create=false;} else {$create=true;	}
	if (strpos(strtolower($grant), "r")===false) {$read=false;  } else {$read=true;		}
	if (strpos(strtolower($grant), "u")===false) {$update=false;} else {$update=true;	}
	if (strpos(strtolower($grant), "d")===false) {$delete=false;} else {$delete=true;	}
	if (strpos(strtolower($grant), "p")===false) {$print=false; } else {$print=true;	}
	if (strpos(strtolower($grant), "x")===false) {$export=false;} else {$export=true;	}
	if (strpos(strtolower($grant), "s")===false) {$viewric=true;} else {$viewric=false;}
	if (strpos(strtolower($grant), "n")===false) {$duplicate=false;} else {$duplicate=true;}

	
	/* inizio multimput */

	/* Gestione pulsante inserimento */
	if ($create==false) {
		$urlinsert = "";
	} elseif (!$urlinsert) {
		$urlinsert="javascript:confermaEdit(0, 'i', '$multikey')";
	}

	/* paginazione */
	$paging	= wf_init_paging($sqlread, $rowpaging, $pag, $all, $schema);
	$ris      =$paging["ris"];
	$html.= wf_head($paging, $nsearch, $beforepaging, $schema, $viewric, $urlinsert);

	if ($_SESSION[debug]) $html.="<br><br> sqlRead ".$paging["sql"];
	
	/* inizio tabella */
	$html.='
    <div class="row">
		<div class="box-body table-responsive wf_browse">
	';
	$html .='<table class="table table-bordered table-hover dataTable">';

  /************************/	
	/* Intestazione colonne */
	$html .="<tr class=wf_head> \n";
	foreach ($aCampi as $campo) {
		$field=$campo[0]; 
		If (strpos(strtoupper($campo[2]), "C")!==false) {$field="<center>".$field."</center>";}
		If (strpos(strtoupper($campo[2]), "I")!==false) {$field="<i>".$field."</i>";}
		If (strpos(strtoupper($campo[2]), "B")!==false) {$field="<b>".$field."</b>";}
		$html .="<th>".$field."</th> \n";
	}
	/* Fine Intestazioni colonne */
	if($read or $update or $delete or $duplicate) {
		$html .="<th>Azioni</tr> \n";
	}

  /********************************/
	/* Inizio Generazione Contenuto */
  $class="wf_odd";
	$numrec=0;
	while ($row = wf_set2row($ris)) {
		$numrec+=1;
		if ($sqlid) {
			If (FALSE === @eval("\$id = $sqlid;")) {
				$errmsg='MULTIMPUT ('.$multikey.') - Errore nella descrizione del campo sqlID - eval fallita :  <['.$sqlid.']>';
				wf_error($errmsg);
			}
		} else {
			$id=0;
		}

		// inibito view da click sulla riga
    if ($update==true and false) {
			$href="javascript:confermaEdit(".$id.", \"r\", \"$multikey\")";		
			$html .= "<tr class='$class wfTableRow' onclick='$href' title='Visualizza il record'> \n";
		} else {
			$html .= "<tr class='$class wfTableRow'> \n";
		}
    $class=($class=="wf_odd"?"wf_even":"wf_odd");

		foreach ($aCampi as $campo) {
			If (FALSE === @eval("\$field = $campo[1];")) {
				$errmsg='MULTIMPUT ('.$multikey.') - Errore nella descrizione del campo - eval fallita :  campo <['.$campo[1].']>';
				wf_error($errmsg);
			}
			$extra="";
			If (strpos(strtoupper($campo[2]), "N")!==false) {$field=number_format((double)$field,0,",","."); $extra="style='text-align: right;'";}
			If (strpos(strtoupper($campo[2]), "D")!==false) {$field=(substr($field,0,4)=="0000"?"-":wf_dtoc($field));}
			If (strpos(strtoupper($campo[2]), "E")!==false) {$field="&euro; ".$field; $extra="style='text-align: right;'";}
			If (strpos(strtoupper($campo[2]), "C")!==false) {$field="<center>".$field."</center>";}
			If (strpos(strtoupper($campo[2]), "B")!==false) {$field="<b>".$field."</b>";}
			If (strpos(strtoupper($campo[2]), "I")!==false) {$field="<i>".$field."</i>";}
			
			$html .="<td col=\"$campo[0]\" id=\"$id\" $extra>".$field."</td> \n";
			$extra="";
		}

    // pulsanti
		$bottoni="";
		$nbottoni=0;
		if ($duplicate==true) {
			$href="javascript:confermaDup(".$id.", 'i', '$multikey')";
			$bottoni .= '<a href="'.$href.'" class="btn btn-primary btn-sm btn-nofloat" title="'.$desdup.'"><i class="fa fa-'.$iconDup.'"></i></a>';
			$nbottoni+=1;
		}
    if ($read==true) {
			$href="javascript:confermaEdit(".$id.", 'r', '$multikey')";
			$bottoni .= '<a href="'.$href.'" class="btn btn-success btn-sm btn-nofloat" title="Visualizza"><i class="fa fa-'.$iconVie.'"></i></a>';
			$nbottoni+=1;
		}
    if ($update==true) {
			$href="javascript:confermaEdit(".$id.", 'e', '$multikey')";
			$bottoni .= '<a href="'.$href.'" class="btn btn-warning btn-sm btn-nofloat" title="'.$desupd.'"><i class="fa fa-'.$iconUpd.'"></i></a>';
			$nbottoni+=1;
    }
    if ($delete==true) {
			$href="javascript:confermaDel(".$id.", '$multikey')";
			$bottoni .= '<a href="'.$href.'" class="btn btn-danger btn-sm btn-nofloat" title="'.$desdel.'"><i class="fa fa-'.$iconDel.'"></i></a>';
			$nbottoni+=1;
    }
		$size=$nbottoni*32+8;
		$html .= '<td align="center" style="min-width: '.$size.'px;"><div class="btn-group">'.$bottoni.'</div></td>';
	}

	/* Fine Generazione Contenuto */
	$html .="</table>";
	if ($numrec==0) {
		$html .='<br><center>Nessun record trovato.</center><br>';
	}

	/* !funzione di verifica duplica */
	if ($verdup==null) {
		$jDup="
		function confermaDup(id, type, key){ 
			if (confirm('Confermi la duplica del record ?')) {
				location.href=$refdup;
			}	
		}
		";
	} else {
		$jDup="
		function confermaDup(id, type, key){ 
			$.ajax({
			  url: '".$verdup."'+id,
			  type: 'GET',
			  dataType: 'html',
			  async: false,
			  error: function(){
					alert('File ".$verdup."'+id+' non trovato.');
			  },
			  success: function(retval) {
			  	if (retval=='1') {
			  		location.href=$refdup;
					} else {
						alert('Creazione non possibile. '+retval);
					}
				}
			});
		}
		";
	}

	/* !funzione di verifica edit */
	if ($verupd==null) {
		$jEdit="
		function confermaEdit(id, type, key){ 
			if (type=='I') {
				location.href=$refins;
			} else {
				location.href=$refupd;
			}
		}
		";
	} else {
		$jEdit="
		function confermaEdit(id, type, key){ 
			$.ajax({
			  url: '".$verupd."'+id,
			  type: 'GET',
			  dataType: 'html',
			  async: false,
			  error: function(){
					alert('File ".$verupd."'+id+' non trovato.');
			  },
			  success: function(retval) {
			  	if (retval=='1') {
						if (type=='i') {
							location.href=$refins;
						} else {
							location.href=$refupd;
						}
					} else {
						alert('Modifica non possibile. '+retval);
					}
				}
			});
		}
		";
	}

	/* !funzione di verifica cancellazione */
	if ($verdel==null) {
		$jDele="
		function confermaDel(id, key){ 
			type='D';
			if (confirm('Confermi la cancellazione del record ?')) {
				location.href=$refdel;
			}
		}
		";
	} else {
		$jDele="
		function confermaDel(id, key){ 
			if (confirm('Confermi la cancellazione del record ?')) {
				$.ajax({
				  url: '".$verdel."'+id,
				  type: 'GET',
				  dataType: 'html',
				  async: false,
				  error: function(){
						alert('File ".$verdel."'+id+' non trovato.');
				  },
				  success: function(retval) {
				  	if (retval=='1') {
				  		location.href=$refdel;
						} else {
							alert('Cancellazione non possibile. '+retval);
						}
					}
				});
			}
		}
		";
	}
	wf_js($jDup);
	wf_js($jEdit);
	wf_js($jDele);

	$html .="</div></div>";
	$html .=wf_foot($paging);
	return ($html);
}

/*****************************************************************************
/ Funzione 		: wf_EditTable()
/ Descrizione : Gestione EDIT/INSERT
/ Parametri :
/ 	$par1				paramentro 1
/*****************************************************************************/
function wf_EditTable($ViewType, $sqlid, $sqlread, $acampib, $sqledit, $acampie, $asqlinsert, $asqlupdate, $asqldelete, $apostprocess, $schema, $multikey, $wfact) {
	$focus=$acampie[0][4];              // Campo a Focus
	wf_loadjs(wf_nocache("plugins/balloon/jquery.balloon.min.js", "js"));
	// METTE A SETFOCUS IL PRIMO CAMPO
	wf_js('$(document).ready(function() { $( "#'.$focus.'").focus();});');

	$html = "<div class=wf_browse>";
	
	/* LETTURA DATI */
	if (substr($sqledit, 0, 1)=='"') {
		If (FALSE === @eval("\$sql = $sqledit;")) {
			$errmsg='MULTIMPUT - Errore nella descrizione del valore sqlEdit - eval fallita :  <['.$sqledit.']>';
			wf_error($errmsg);
		}
	} else {
		$sql=$sqledit;
	} 

	$ris = wf_getset($sql, "Errore MULINP02");
	$row = wf_set2row($ris);
	$ok=false;
	
	/* !AZIONE DI UPDATE DA FORM */
	if ($_POST[frm_multikey]==$multikey and $_POST[frm_dadove]=="upd") {
		$ok=true;

		/* !CONTROLLO ERRORI */
		$sep="- ";
		foreach ($acampie as $campo) {
			$e="";
			// messa focus 1° campo
			if ((! $focus) and wf_instr($campo[1],"text;date;textarea")) $focus=$campo[4];
			if ($campo[1]=="date") {
				// !controllo date
				if (!wf_chkdate($_POST[$campo[4]])) {
					echo "A1<br>";
					$ok=false;
					$e=$sep."Data errata";
				}
			}
			if ($e=="") {
				if (gettype($campo[6])=="array") {
					$n=0;
					$okc=true;
					foreach ($campo[6] as $valid) {
						if ($okc) {
							If (FALSE === @eval("\$field = $valid;")) {
								$errmsg= 'MULTIMPUT - Errore nella descrizione del valore Campo[6] - eval fallita :  <['.$valid.']>';
								wf_error($errmsg);
							}
							If (! $field) {
								if ($ok) {$focus=$campo[4];}        // messa a focus 1° campo con errore
								echo "A2<br>";
								$ok=false;
								$okc=false;
								$e=$sep.$campo[7][$n];
							}
						}
						$n=$n+1;
					}
				} else {
					if ($campo[6]!='') {
						If (FALSE === @eval("\$field = $campo[6];")) {
							$errmsg= 'MULTIMPUT - Errore nella descrizione del valore Campo[6] - eval fallita :  <['.$campo[6].']>';
							wf_error($errmsg);
						}
						If (! $field) {
							if ($ok) {$focus=$campo[4];}        // messa a focus 1° campo con errore
							$ok=false;
							$e=$sep.$campo[7];
						}
					}
				}
			}
			// Array Errori
			$aErr[]=$e;
		}


		/* !SCRITTURA */
		if ($ok) {
			if ($ViewType=="i") {
				/* !Elaborazione Sql INSERT */
				$msg="INSERIMENTO";
				foreach ($asqlinsert as $itemid => $ele) {
          if (gettype($ele)=="string") {
            $sql=$ele;
            $con='true';
          } else {
            $sql=$ele[0];
            $con=$ele[1];
          }
          if ($_SESSION[debug]) echo "<br><br> CON ".$con;
					If (FALSE === @eval("\$vero = $con;")) {
						$errmsg= 'MULTIMPUT - Errore nella descrizione del valore Con - eval fallita :  <['.$con.']>';
						wf_error($errmsg);
					}
          if ($vero) {
            if ($_SESSION[debug]) echo "<br><br> sql ".$sql;
            if (substr($sql, 0, 1)=='"') {
							If (FALSE === @eval("\$field = $sql;")) {
								$errmsg= 'MULTIMPUT - Errore nella descrizione del valore sql - eval fallita :  <['.$sql.']>';
								wf_error($errmsg);
							}
          	} else {
              $field = $sql;
          	}

            if ($_SESSION[debug]) echo "<br><br> esql ".$field;
						$ris = wf_getset($field, "Errore MULINP03");
            $ni	 = wf_getid();
            $newid[]=$ni;
            $wf_newid[$itemid]=$ni;

            /* rilascio risultati select */
            if (strtolower(substr(trim($field),0,6))=="select") {
              $row = wf_set2row($ris);
              $wf_row[] = $row;
            } else {
              $wf_row[] = "";
            }
          }
				}
			} elseif ($ViewType=="e") {
				/* !Elaborazione Sql UPDATE */
				$msg="AGGIORNAMENTO";
				foreach ($asqlupdate as $itemid => $ele) {
          if (gettype($ele)=="string") {
            $sql=$ele;
            $con='true';
          } else {
            $sql=$ele[0];
            $con=$ele[1];
          }
					If (FALSE === @eval("\$vero = $con;")) {
						$errmsg= 'MULTIMPUT - Errore nella descrizione del valore Con - eval fallita :  <['.$con.']>';
						wf_error($errmsg);
					}
          if ($vero) {
            if ($_SESSION[debug]) echo "<br><br> sql ".$sql;
            if (substr($sql, 0, 1)=='"') {
							If (FALSE === @eval("\$field = $sql;")) {
								$errmsg= 'MULTIMPUT - Errore nella descrizione del valore sql - eval fallita :  <['.$sql.']>';
								wf_error($errmsg);
							}
          	} else {
              $field = $sql;
          	}
            if ($_SESSION[debug]) echo "<br><br> esql ".$field;
						$ris = wf_getset($field, "Errore MULINP04");
            $ni	 = wf_getid();
            $newid[]=$ni;
            $wf_newid[$itemid]=$ni;

            /* rilascio risultati select */
            if (strtolower(substr(trim($field),0,6))=="select") {
              $row = wf_set2row($ris);
              $wf_row[] = $row;
            } else {
              $wf_row[] = "";
            }
          }
				}
			}

			// ATTIVITA' COMPLETATA
			if ($ViewType=="i" and $apostprocess[i]) {
				If (FALSE === @eval("\$href = $apostprocess[i];")) {
					$errmsg= 'MULTIMPUT - Errore nella descrizione del valore $apostprocess[i] - eval fallita :  <['.$apostprocess[i].']>';
					wf_error($errmsg);
				}
			} elseif ($ViewType=="e" and $apostprocess[e]) {
				If (FALSE === @eval("\$href = $apostprocess[e];")) {
					$errmsg= 'MULTIMPUT - Errore nella descrizione del valore $apostprocess[e] - eval fallita :  <['.$apostprocess[e].']>';
					wf_error($errmsg);
				}
			} else {
				$href=getCurrentURL("wftkn$multikey,wfact$multikey,wfid$multikey","&");
			}
      if ($_SESSION[debug]) {
				$html .= "<center><br><br><br><br><br><br>$msg FATTO !<br><br><br><br><br></center>";
			} else {
				Header("Location: ".$href);
				exit;
			}
		}
	}

	/* !DISEGNO FORM DATI */
	if (! $ok) {
        $html .= wf_MiPrintForm($acampie, $row, $ViewType, $wfact, $multikey, $aErr);
    }
	$html .="</div> \n";
	return ($html);
}



/*****************************************************************************
/ Funzione 		: wf_MiPrintForm()
/ Descrizione : Generazione HTML della from di EDIT/INSERT
/ Parametri :
/ 	$par1				paramentro 1
/*****************************************************************************/
function wf_MiPrintForm($acampie, $row, $ViewType, $wfact, $multikey, $aErr) {
	$elemxriga=[];
  $html = "<form method=post name=frm > \n";
  $html .= "<table class=edit>  \n";

  $form  = "<form method=post name=frm > \n";
  $form .= '<div class="box-body no-padding">';
  $riga=-1;
  $primo=true;

  // determinazione massimo numero elementi per riga
  foreach ($acampie as $campo) {
    $elemxriga[$campo[3]]+=1;
  }
  $maxelemxriga=max($elemxriga);

  $rowflip="";
  foreach ($acampie as $elecampo => $campo) {
		$fLabel	=$campo[0];
		$fType	=$campo[1];
		$fSize	=$campo[2];
		$fRiga	=$campo[3];
		$fName	=$campo[4];
		$fEspr	=$campo[5];
		$fExtra	=($ViewType=='r'?"DISABLED ":$campo[8]);
		$fOpti	=$campo[9];
		$fCall	=$campo[10];

		if ($riga<>$fRiga) {
			if ($riga<>-1) $form.="</div>";
			$form.="<div class='row gruppo$fRiga' id='gruppo$fRiga'>";
			$riga=$fRiga;
		}
    /* DETERMINAZIONE VALORE CAMPO */
    if ($_POST["frm_dadove"]=="upd" and $fType<>"view") {
      $fValue=$_POST[$fName];
    } else {
      if ($campo[1]=="label") {
        $fValue="";
      }else{
        If (FALSE === @eval("\$fValue = $fEspr;")) {
          $errmsg= 'MULTIMPUT - Errore nella descrizione del valore $campo[5] - eval fallita :  <['.$fEspr.']>';
          wf_error($errmsg);
        }
      }
    }
		$fValue=htmlspecialchars($fValue);
//    $label=str_replace(" ", "&nbsp;", $label); // nbsp == non-breaking space
//    $label=str_replace("§", " ", $label); // nbsp == non-breaking space

		// Tooltip
    $alabel	=explode("|", $fLabel);
    $label	=$alabel[0];
    $balinfo=str_replace("'", "&apos;", $alabel[1]);
    if ($balinfo) {
      $tooltip =' <img id="info_'.$campo[4].'" src="'.WF_DIR.'/question-mark.gif" data-toggle="tooltip" data-original-title="'.$balinfo.'">';
    } else {
			$tooltip="";
		}


		$mdCol=4;
//		if ($fRiga==0) $mdCol=12;
		if ($fSize<=12) $mdCol=$fSize;

		if ($fType=="label") {
			$form .= '
          <div class="col-md-12">
              <h4 class="no-margin">' . $label . ' </h4>
          </div>
        ';
		}

		$hasError=($aErr[$elecampo]<>""?"has-error":"");
    if ($fType=="text" or $fType=="view" or $fType=="password" or $fType=="file") {
			$fExtra.=($fType=="view" ?" DISABLED":"");
			$fType=($fType=="view" ?"text":$fType);
      $form .= '
          <div class="col-md-'.$mdCol.'">
              <div class="form-group '.$hasError.'">
                  <label for="'.$fName.'">'.$label.$tooltip.' '.$aErr[$elecampo].'</label>
                  <input class="form-control form-text" placeholder="Inserisci '.$label.'" name="'.$fName.'"  id="'.$fName.'" type="'.$fType.'" value="'.($fValue).'" '.$fExtra.'>
              </div>
          </div>
        ';

		} elseif ($campo[1]=="complete") {
			$fExtra.=($fType=="view" ?" DISABLED":"");
			$fType=($fType=="view" ?"text":$fType);
			$form .= '
          <div class="col-md-'.$mdCol.'">
              <div class="form-group '.$hasError.'">
                  <label for="'.$fName.'">'.$label.$tooltip.' '.$aErr[$elecampo].'</label>
                  <input type="text" class="form-control form-text" placeholder="Inserisci '.$label.'" name="'.$fName.'" id="'.$fName.'" '.$fExtra.' autocomplete="off">

              </div>
          </div>
        ';
			wf_js('$("#'.$fName.'").typeahead({
				ajax: '.$fCall.',
				displayField: "name",
				valueField: "id",
				onSelect: function(item) {
					$("#'.$fName.'_hidden").val(item.value);
        	console.log(item.value);
    		},
			});');

		} elseif ($campo[1]=="date") {
			/**** Campo DATE ****/
			$form .= '
          <div class="col-md-'.$mdCol.'">
						<div class="form-group '.$hasError.'">
							<label for="'.$fName.'">'.$label.$tooltip.' '.$aErr[$elecampo].'</label>
							<div class="input-group date">
								<input class="form-control pull-right datepicker" placeholder="Inserisci '.$label.'" name="'.$fName.'" id="'.$fName.'" type="text" value="'.($fValue).'" '.$fExtra.'>
								<div class="input-group-addon">
									<i class="fa fa-calendar"></i>
								</div>
							</div>
						</div>
          </div>
        ';

		} elseif ($fType=="datetime") {
			/**** Campo DATE ****/
			$form .= '
          <div class="col-md-'.$mdCol.'">
						<div class="form-group '.$hasError.'">
							<label for="'.$fName.'">'.$label.$tooltip.' '.$aErr[$elecampo].'</label>
							<div class="input-group date">
								<input class="form-control pull-right datetimepicker" placeholder="Inserisci '.$label.'" name="'.$fName.'" id="'.$fName.'" type="text" value="'.($fValue).'" '.$fExtra.'>
								<div class="input-group-addon">
									<i class="fa fa-calendar"></i>
								</div>
							</div>
						</div>
          </div>
        ';

		} elseif ($fType=="textarea") {
			/**** Campo TEXTAREA ****/
			$form .= '
          <div class="col-md-'.$mdCol.'">
						<div class="form-group '.$hasError.'">
							<label for="'.$fName.'">'.$label.$tooltip.' '.$aErr[$elecampo].'</label>
							<textarea class="form-control" rows="3" placeholder="Inserisci '.$label.'" name="'.$fName.'" id="'.$fName.'" '.$fExtra.'>'.($fValue).'</textarea>
						</div>
					</div>
        ';

		} elseif ($fType=="editor" ) {
			/**** Campo EDITOR HTML ****/
			wf_loadjs('<link href="plugins/summernote/summernote.css" rel="stylesheet">');
			wf_loadjs('<script src="plugins/summernote/summernote.js"></script>');
			$fExtra.=($fType=="view" ?" DISABLED":"");
			$form .= '
          <div class="col-md-'.$mdCol.'">
              <div class="form-group '.$hasError.'">
								<label for="'.$fName.'">'.$label.$tooltip.' '.$aErr[$elecampo].'</label>
								<textarea class="form-control summernote" placeholder="Inserisci '.$label.'" name="'.$fName.'" id="'.$fName.'" '.$fExtra.'>'.($fValue).'</textarea>
              </div>
          </div>
        ';
			wf_js("$('.summernote').summernote({height: \"100px\"});");

		} elseif ($fType=="hidden" ) {
			/**** Campo HIDDEN ****/
			$form .= '<input type="hidden" name="'.$fName.'" id="'.$fName.'" value="'.($fValue).'" >';

		} elseif ($fType=="option") {
			/**** Campo OPTION ****/
			$aElement=explode("|",$fOpti);
			$aSelect=explode(";",$aElement[0]);
			$form .= '
          <div class="col-md-'.$mdCol.'">
              <div class="form-group '.$hasError.'">
								<label for="'.$fName.'">'.$label.$tooltip.' '.$aErr[$elecampo].'</label>
              ';
			if ($aElement[1]) {
				$form .= '<div class="input-group plus">';
			}
			$form .= '<select name="'.$fName.'" id="'.$fName.'" class="form-control" '.$fExtra.'>';

			$gruppo="xx";
			foreach ($aSelect as $aSel) {
				$aOpt=explode(":",$aSel);
				if ($aOpt[2]<>"" and $aOpt[2]<>$gruppo) {
					if ($gruppo<>"xx") {
						$form .= "</optgroup>";
					}
					$form .= "<optgroup label='$aOpt[2]'>";
					$gruppo=$aOpt[2];
				}
				$form .= "<option value='$aOpt[0]' ".($fValue==$aOpt[0]?"SELECTED":"").">".$aOpt[1]."</option>";
			}
			if ($gruppo<>"xx") {
				$form .= "</optgroup>";
			}
			$form .= "</select>";
			// gestione insert con tasto +
			if ($aElement[1]) {
				$form .= '
					<div class="input-group-addon">
						<a href="index.php?proc='.$aElement[1].'" target="_blank"><i class="fa fa-plus"></i></a>
					</div>
				';
			}
			$form .= '
						</div>
          </div>
        ';

		} elseif ($fType=="checkbox" or $fType=="radio" ) {
			/**** Campo CHECKBOX o RADIO ****/
			$defValue=$campo[2];
			$fExtra.=($fType=="view" ?" DISABLED":"");
			$chk=($fValue==$defValue?"CHECKED":"");
			$fType=($fType=="view" ?"text":$fType);
			$prec=$acampie[$elecampo -1][1];
			$succ=$acampie[$elecampo +1][1];

			if ($fType<>$prec) {
				$form .= '
						<div class="col-md-'.$mdCol.'">
								<div class="form-group '.$hasError.'">
								<div class="checkbox">
					';
			}
			$form .= '
								<label for="'.$fName.'">'.$label.$tooltip.' '.$aErr[$elecampo].$prossimo.'</label>
								<input placeholder="Inserisci '.$label.'" name="'.$fName.'"  id="'.$fName.'" type='.$fType.' value="'.($defValue).'" '.$chk.' '.$fExtra.'>
								';
			if ($fType<>$succ) {
				$form .= '
								</div>
								</div>
						</div>
					';
			}

		} elseif ($fType=="print" ) {
			$form .= 'campo *'.$fType.'* non gestito - ';
			$html .= "<td class=wf_label>$label</td>";
			$html .= "<td><b>$fValue</b></td> \n";

		} elseif ($campo[1]=="image" ) {
			$form .= 'campo  '.$fType.' non gestito';
			$html .= "<td class=wf_label>$label</td>";
			if ($fValue) {
					$html .= "<td>
										<img src='$fValue' id=$campo[4] syle>
										<input type='hidden' name='$campo[4]' id=$campo[4] value=\"$fValue\" $extra>
										<input type='button' value='cancella'>
										<input type='button' value='cambia'>
										</td> \n";
			} else {
					$html .= "<td>
										<input type='button' value='carica'>
										</td> \n";
            }
		} else {
			$form .= '
          <div class="col-md-'.$mdCol.'">
              <div class="form-group '.$hasError.'">
                  <label >campo tipo "'.$fType.'" non gestito</label>
              </div>
          </div>
        ';
		}
	}
	if ($riga<>-1) $form.="</div>";


  $html .= "</tr></table></td></tr>";
  $html .= "</table> \n";

  $form .='</div>';
  $form .='<div class="box-footer">';

  // $html .= "<input class='BottoneMax' type=image src='".WF_DIR."go.gif' name=cmd_invia> ";
  if ($wfact=="r") {
    $url=(str_replace("&wfact$multikey=r","&wfact$multikey=e", $_SERVER['REQUEST_URI']));
    $html .= '<input class="wf_bottone" type="button" title="Modifica" value="Modifica" onclick="javascript:location.href=\''.$url.'\';"> ';

    //$html .= "<input class='wf_bottone' type='button' title='Indietro' value='Indietro'  onclick='javascript:history.go(-1);'> ";
    $url=getCurrentURL("wftkn$multikey,wfact$multikey,wfid$multikey","&");
    $html .= '<input class="wf_bottone" type="button" title="Indietro" value="Indietro" onclick="javascript:location.href=\''.$url.'\';"> ';

    $urlMod=(str_replace("&wfact$multikey=r","&wfact$multikey=e", $_SERVER['REQUEST_URI']));
    $form .='<button type="button" class="btn btn-default" onclick="javascript:history.go(-1);">Indietro</button>';
    $form .='<button type="button" class="btn btn-warning pull-right" onclick="javascript:location.href=\''.$urlMod.'\';">Modifica</button>';
  } else {
    //$html .= "<input class='wf_bottone' type='submit' title='Salva' 		value='Salva' 	name=cmd_invia> ";
    //$html .= "<input class='wf_bottone' type='button' title='Annulla' 	value='Annulla' onclick='javascript:history.go(-1);'> ";

    $form .='<button type="button" class="btn btn-default" onclick="javascript:history.go(-1);">Annulla</button>';
    $form .='<button type="submit" class="btn btn-primary pull-right" name="cmd_invia">Salva</button>';
  }

  $html .= "<input type=hidden name='frm_multikey' value='$multikey'> \n";
  $html .= "<input type=hidden name='frm_dadove' value='upd'> \n";
  $html .= "<input type=hidden name='fld_action' value='$ViewType'> \n";
  $html .= "<input type=hidden name='fld_id' value='$_POST[fld_id]'> \n";
  $html .= "</form> \n";

  $form .= "</div>";
  $form .= "<input type=hidden name='frm_multikey' value='$multikey'> \n";
  $form .= "<input type=hidden name='frm_dadove' value='upd'> \n";
  $form .= "<input type=hidden name='fld_action' value='$ViewType'> \n";
  $form .= "<input type=hidden name='fld_id' value='$_POST[fld_id]'> \n";
  $form .= "</form> \n";

  return ($form);
}

/*****************************************************************************
/ Funzione 		: wf_DeleteTable()
/ Descrizione : Gestione cancellazione
/ Parametri :
/ 	$par1				paramentro 1
/*****************************************************************************/
function wf_DeleteTable($asqldelete, $apostprocess, $schema, $multikey) {
	foreach ($asqldelete as $sql) {
    if (substr($sql, 0, 1)=='"') {
			If (FALSE === @eval("\$field = $sql;")) {
				$errmsg= 'MULTIMPUT - Errore nella descrizione del valore $sql - eval fallita :  <['.$sql.']>';
				wf_error($errmsg);
			}
  	} else {
      $field = $sql;
  	}		
		$ris = wf_getset($field, "Errore MULINP05");

    /* rilascio risultati select */
    if (strtolower(substr(trim($field),0,6))=="select") {
      $row = wf_set2row($ris);
      $wf_row[] = $row;
    } else {
      $wf_row[] = "";
    }
	}
	
	$href=getCurrentURL("wftkn$multikey,wfact$multikey,wfid$multikey","&");
	Header("Location: ".$href);
	exit;
}

// ******* INIZIALIZZAZIONE PAGING *******
/*****************************************************************************
/ Funzione 		: wf_init_paging()
/ Descrizione : INIZIALIZZAZIONE PAGING
/*****************************************************************************/
function wf_init_paging($sql, $rowpaging, $pag, $all, $schema="default") {
	//! TODO: trovare metodo più efficiente di cacolare il numero complessivo di record
	if($rowpaging>0){
		$rows 							= wf_getset($sql, "Errore MULINP06");
		$all_rows           = mysqli_num_rows($rows);
		$all                = ceil($all_rows / $rowpaging);

		$pag                = ($_POST['fld_pag']?$_POST['fld_pag']:1);
		$pag                = ($_GET['wfpag']?$_GET['wfpag']:1);
		$pag                = ($pag>$all?1:$pag);

		$first              = ($pag - 1) * $rowpaging;
		$sql  						 .= " limit $first, $rowpaging";
		$ris 								= wf_getset($sql, "Errore MULINP07");
	} else {
		$ris								= wf_getset($sql, "Errore MULINP06");
		$all_rows						= mysqli_num_rows($ris);
		$pag=$all=1;
	}

  $ret["ris"]=$ris;
  $ret["sql"]=$sql;
  $ret["pag"]=$pag;
  $ret["all"]=$all;
  $ret["row"]=$all_rows;
  $ret["rxp"]=$rowpaging;
	return ($ret);
}

/*****************************************************************************
/ Funzione 		: wf_head()
/ Descrizione : Intestazione Browse
/*****************************************************************************/
function wf_head($paging, $nsearch, $beforepaging, $schema, $viewric=true, $urlinsert="") {
	$pag      =$paging["pag"];
	$allpag   =$paging["all"];

	if($urlinsert<>"") {
		$buttonInsert='<a href="'.$urlinsert.'" class="btn btn-warning btn-left" title="Inserisci"><i class="fa fa-plus"></i> Crea elemento</a>';
	}
	// *** FROM RICERCA ***
	if ($viewric) {
		$formric='<input type="text" name="wfsearch" class="form-control input-sm pull-right" placeholder="Ricerca" value="'.$_GET[wfsearch].'">';
		if ($nsearch>1) {
			$formric .= '<input type="text" name="wfsearch2" class="form-control input-sm pull-right" placeholder="Ricerca" value="' . $_GET[wfsearch2] . '">';
		}
		$formric .= '
          <div class="input-group-btn">
            <button class="btn btn-sm btn-default"><i class="fa fa-search"></i></button>
          </div>
		';
	}

	// *** PAGINAZIONE SINTETICA ***
	$url=getCurrentURL("wfpag")."&wfpag=";
	$urlb=$url.($pag-1);
	$urlf=$url.($pag+1);
	if ($allpag > 1) {
		if ($pag > 1) {
			$bottoni='<a class="btn btn-success" title="Indietro" href="'.$urlb.'"><i class="fa fa-chevron-left"></i></a>
								<a class="btn btn-success" title="Avanti" href="'.$urlf.'"><i class="fa fa-chevron-right"></i></a>';
		} else {
			$bottoni='<a class="btn btn-success" title="Avanti" href="'.$urlf.'"><i class="fa fa-chevron-right"></i></a>';
		}
	}

	// *** INTESTAZIONE ***
	if($beforepaging or $buttonInsert or $formric or $bottoni) {
		$html = '
	<form id=paging method=get action=>
	<div class="row">
		<div class="col-sm-12">
			<div class="box-body ">
				' . $beforepaging . '
			</div>
		</div>
	</div>';
		if ($buttonInsert or $formric or $bottoni) {
			$html .= '
		<div class="form-inline row">
			<div class="col-sm-6">
				<div>
					' . $buttonInsert . '
				</div>
			</div>
			<div class="col-sm-6 text-right">
				<div id="filter">
						' . URL2Input() . '
						<div class="input-group" style="width: 150px;">
							' . $formric . '
						</div>
						<div class="input-group ajax-load">
							<div class="btn-group">
							' . $bottoni . '
							</div>
						</div>
				</div>
			</div>
		</div>';
		}
		$html .= '
	</form>
';
	}
	return $html;
}

/*****************************************************************************
/ Funzione 		: wf_foot()
/ Descrizione : Piede Browse
/*****************************************************************************/
function wf_foot($paging) {
	$pag      =$paging["pag"];
	$all   		=$paging["all"];
	$tot   		=$paging["row"];
	$rowpaging=$paging["rxp"];

	$fin=$pag*$rowpaging;
	$fin=($fin>$tot?$tot:$fin);
	$ini=$fin-($rowpaging>$tot?$tot:$rowpaging)+1;
	$fin=($fin==0?$tot:$fin);

/*
	var_dump($paging); echo "-<br>";
	var_dump($pag); echo "-<br>";
	var_dump($all); echo "-<br>";
	var_dump($ini); echo "-<br>";
	var_dump($fin); echo "-<br>";
	var_dump($tot); echo "-<br>";
	exit;
	*/
	$html='
<div class="row">
  <div class="col-sm-5">
    <div class="dataTables_info" id="footer-info2" role="status" aria-live="polite">
      Record da '.($ini).' a '.($fin).' di '.($tot).'
		</div>
  </div>
	';

	$url=getCurrentURL("wfpag")."&wfpag=";
	$urlb=$url.($pag-1);
	$urlf=$url.($pag+1);
	if($all>1) {
		$html.='
		<div class="col-sm-7">
			<div class="footer_paginate">
					<ul class="pagination">
						<li><a href="'.$urlb.'" rel="prev">«</a></li>
		';
		if($all<10) {
			for($i=1;$i<=$all;$i++) {
				if($i==$pag) {
					$html.='<li class="active"><span>'.$i.'</span></li>';
				} else {
					$html.='<li><a href="'.$url.$i.'">'.$i.'</a></li>';
				}
			}
		} elseif($pag<7) {
			for($i=1;$i<=8 and $i<=$all;$i++) {
				if($i==$pag) {
					$html.='<li class="active"><span>'.$i.'</span></li>';
				} else {
					$html.='<li><a href="'.$url.$i.'">'.$i.'</a></li>';
				}
			}
			$html.='<li class="disabled"><span>...</span></li>';
			$html.='<li><a href="'.$url.($all-1).'">'.($all-1).'</a></li>';
			$html.='<li><a href="'.$url.$all.'">'.$all.'</a></li>';
		} elseif($pag>=7 and $pag<=$all-7) {
			$html.='<li><a href="'.$url.(1).'">'.(1).'</a></li>';
			$html.='<li><a href="'.$url.(2).'">'.(2).'</a></li>';
			$html.='<li class="disabled"><span>...</span></li>';
			for($i=$pag-3;$i<=$pag+3;$i++) {
				if($i==$pag) {
					$html.='<li class="active"><span>'.$i.'</span></li>';
				} else {
					$html.='<li><a href="'.$url.$i.'">'.$i.'</a></li>';
				}
			}
			$html.='<li class="disabled"><span>...</span></li>';
			$html.='<li><a href="'.$url.($all-1).'">'.($all-1).'</a></li>';
			$html.='<li><a href="'.$url.$all.'">'.$all.'</a></li>';
		} elseif($pag>$all-7) {
			$html.='<li><a href="'.$url.(1).'">'.(1).'</a></li>';
			$html.='<li><a href="'.$url.(2).'">'.(2).'</a></li>';
			$html.='<li class="disabled"><span>...</span></li>';
			for($i=$all-7;$i<=$all;$i++) {
				if($i==$pag) {
					$html.='<li class="active"><span>'.$i.'</span></li>';
				} else {
					$html.='<li><a href="'.$url.$i.'">'.$i.'</a></li>';
				}
			}
		}

		$html.='
							<li><a href="'.$urlf.'" rel="next">»</a></li>
						</ul>
				</div>
			</div>
		</div>
		';
	}

	// TODO: DA VERIFICARE DOVE MANCA CHIUSURA DIV
	return $html."</div>";
}


function xx_wf_paging($pag, $all_pages, $tot_rows, $nsearch, $beforepaging, $schema, $viewric=true) {
	$html ='<div class="row">';
	$html .="<form id=paging method=get action=$url>";
	$html .=$beforepaging;
	$html .=URL2Input();
	$html .="<table class=paging width=100%><tr>";
	
	$html .="<td width=70%>";
	if ($viewric) {
		$html .="Ricerca<b>*</b> : "; 
		if ($nsearch==1) {
			$html .="<input type=text name='wfsearch'  size=28  value=".$_GET[wfsearch]."> ";
			} else {
			$html .="<input type=text name='wfsearch'  size=13  value=".$_GET[wfsearch]."> ";
			$html .="<input type=text name='wfsearch2' size=13  value=".$_GET[wfsearch2].">";
			}
		$html .= "<input class='wf_bottone' type='submit' title='Ricerca' value='Ricerca' 	name=cmd_cerca  onClick='javascript:paging.submit()'> ";
	}	else {
//		$html .="&nbsp;"; 
	}
	$html .="</td>";

	// visualizzazione paginazione
	// if ($all_pages > 1) {
		$html .="<td width=50>&nbsp;</td>";
		
		$url=getCurrentURL("wfpag")."&wfpag=";
		$urlb=$url.($pag-1);
		$urlf=$url.($pag+1);
		
		if ($pag > 1) {         $html .="<td style='white-space: nowrap;'><a href='$urlb'><img src='".WF_DIR."pgindietro.gif' alt='Indietro'>&nbsp;Indietro</a></td>";
		} else {                $html .="<td style='white-space: nowrap;'><img src='".WF_DIR."pgindietro.gif' alt='Indietro'>&nbsp;Indietro</td>";      }
		if ($all_pages>10) {
			$html .="<td style='white-space: nowrap;'>&nbsp;|&nbsp;</td>";
			if ($pag > 1) {       $html .="<td style='white-space: nowrap;'><a href='".$url."1'>Prima</a></td>";
			} else {              $html .="<td style='white-space: nowrap;'>Prima</td>";         }
		}

		$html .="<td style='white-space: nowrap;'>&nbsp;| $pag/$all_pages ($tot_rows) |&nbsp;</td>";

		if ($all_pages>10) {
			if ($all_pages>$pag) {$html .="<td style='white-space: nowrap;'><a href='$url".$all_pages."'>Ultima</a></td>";
			} else {              $html .="<td style='white-space: nowrap;'>Ultima</td>";        }
			$html .="<td>&nbsp;|&nbsp;</td>";
		}
		if ($all_pages>$pag) {  $html .="<td style='white-space: nowrap;'><a href='$urlf'>Avanti&nbsp;<img src='".WF_DIR."pgavanti.gif' alt='Avanti'></a></td>";
		} else {                $html .="<td style='white-space: nowrap;'>Avanti&nbsp;<img src='".WF_DIR."pgavanti.gif' alt='Avanti'></td>";        }

  // }
  $html .="</tr></table>";
	$html .="</form>";
	$html .="</div>";




	if ($viewric) {
		$formric='
					<input type="text" name="table_search" class="form-control input-sm pull-right" placeholder="Ricerca" value="">
          <div class="input-group-btn">
            <button class="btn btn-sm btn-default"><i class="fa fa-search"></i></button>
          </div>
		';
	}
	if ($pag > 1) {
		$bottoni='<a class="btn btn-success" title="Indietro" href="'.$urlb.'"><i class="fa fa-chevron-left"></i></a>
							<a class="btn btn-success" title="Avanti" href="'.$urlf.'"><i class="fa fa-chevron-right"></i></a>';
	} else {
		$bottoni='<a class="btn btn-success" title="Avanti" href="'.$urlf.'"><i class="fa fa-chevron-right"></i></a>';
	}
	$html ='
<div class="form-inline row">
  <div class="col-sm-6">
    <div>
      <a href="http://localhost/didasco/public/admin/pazienti/add?page=2" class="btn btn-warning btn-left" title="Inserisci"><i class="fa fa-plus"></i> Crea elemento</a>
    </div>
  </div>
  <div class="col-sm-6 text-right">
    <div id="filter">
      <form>
        <div class="input-group" style="width: 150px;">
        	'.$formric.'
        </div>
        <div class="input-group ajax-load">
          <div class="btn-group">
          '.$bottoni.'
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
';
  return $html;
}

/*****************************************************************************
/ Funzione : getCurrentURL
/ Descrizione : fornisce l'URL ripulita dalle variabili specifice di multinput
/ Parametri :
/ 	$par1							paramentro 1
/*****************************************************************************/
function getCurrentURL($exlude="", $div="&amp;") {
	$currentURL = basename($_SERVER["PHP_SELF"]); 
	$i = 0;
	foreach($_GET as $key => $value) {
		$aexlude=preg_split("[,]", $exlude);
		if (! in_array($key, $aexlude)) {
			$i++; 
			if($i == 1) { $currentURL .= "?"; } 
			else { $currentURL .= $div; } 
			$currentURL .= $key."=".$value; 
		}
	} 
	return $currentURL; 
} 

/*****************************************************************************
/ Funzione : URL2Input
/ Descrizione : 
/*****************************************************************************/
function URL2Input() { 
	$currentURL = basename($_SERVER["PHP_SELF"]); 
	foreach($_GET as $key => $value) {
		if ($key!="wfsearch" and $key!="wfsearch2") {
			if ($key=="wfpag") {
				$html.="<input type=hidden name='$key' value='1'>";
			} else {
				if (substr($key,0,2)!="wf" or $key=="wfp1" or $key=="wfp2")
					$html.="<input type=hidden name='$key' value='$value'>";
			}
		}
	} 
	return $html; 
}

/*****************************************************************************
/ Funzione : wf_OptionTable
/ Descrizione : prepara stringa per option della multinput
/ Parametri :
/ 	$Tab							tabella di estrazione
/		$Campi						campi da estrarre nel formato campo1:campo2:campo3 il campo1 viene utilizzato come id gli altri come descrizione 
/		$order=null				campo di ordinamento 
/		$where=null				condizione di selezione 
/		$schema="default"	schema db
/*****************************************************************************/
function wf_OptionTable($Tab, $Campi, $order=null, $where=null, $schema="default") {
  $sql="select * from $Tab";
	if ($where<>"") {
    $sql.=" WHERE ".$where;
  }

  if ($order<>"") {
    $sql.=" ORDER BY $order";
  }

	$ris = wf_getset($sql, "Errore MULINP08");

	$aCampi=explode(":",$Campi);
	
	$sep1="";
	while ($row = wf_set2row($ris)) {
		$opt .=$sep1; 
		$sep2="";
		foreach ($aCampi as $campo) {
			if ($campo) {
				if (substr($campo,0,1)=="'") {
					$campo=str_replace("'", "", $campo);
					$opt .=$sep2.$campo;					
				} else {
					$str="\$row[".$campo."]";
					If (FALSE === @eval("\$field = $str;")) {
						$errmsg= 'MULTIMPUT - Errore nella descrizione del valore $str - eval fallita :  <['.$str.']>';
						wf_error($errmsg);
					}
					$opt .=$sep2.$field;
				}
				$sep2=($sep2==""?":":" ");
			}
		}
		$sep1=";";
	}
	return $opt;
}

function apici($stringa) {
  $stringa = str_replace("'", "\'", $stringa);
  $stringa = str_replace('"', '\"', $stringa);
  return $stringa;
}

function wf_eval($str) {
  $new=$str."->";
  $nchr=stripos($str, "{");
  $new.=$nchr;
  while ($nchr===true) {
    $new.=substr($str, 0, $nchr)."^".substr($str, $nchr+1);
    $nchr=stripos($str, "{");
  }

  return $new;
}

function wf_PrintTable($sqlread, $aCampi, $schema="default") {
	/* preparazione DB */
	global $max, $xCampi, $margini, $ratio;
	$xCampi=$aCampi;
	$margini=2;
	$ris = wf_getset($sqlread, "Errore MULINP09");
	if ($_SESSION[debug]) echo "<br><br> sqlRead ".$sqlread;

	define('FPDF_FONTPATH','./font/');
	include('./class/fpdf.php');
	class bPDF extends FPDF {
	}
	$pdf = new bPDF('L','mm','A4');
	$pdf->SetFont('Arial', '', 10);

	// calcolo ingombrto colonne
	$rig=0;
	while ($row = wf_set2row($ris)) {
		$rig+=1;
		$col=0;
		foreach ($aCampi as $campo) {
			$col+=1;
			If (FALSE === @eval("\$field = $campo[1];")) {
				$errmsg= 'MULTIMPUT - Errore nella descrizione del campo $campo[1] - eval fallita :  <['.$campo[1].']>';
				wf_error($errmsg);
			}
			$tabella[$rig][$col] .=strip_tags($field);
			
			// $len=strlen($field);
			$len=$pdf->GetStringWidth(strip_tags($field));
			$max[$col] = ($max[$col]<$len?$len:$max[$col]);
		}
	}
	$pdf->close();
	
	// calcolo totale
	foreach ($max as $x) {
		$tot = $tot + $x;
	}
	
	// predisposizione parametri generali
	// max : orizzontale 280mm, verticale 190mm
	$ratio=($tot>280 ? 280/$tot : 1);
	
	// $html= implode(",", $max);
	// $html .= "Tot=".$tot;
	// $html .= "Rat=".$ratio;

	class PDF extends FPDF {
		function Header() {
			global $max, $xCampi, $margini, $ratio;
			$this->SetFont('Arial', 'B', 10*$ratio);

			$tot=count($xCampi);
			$col=0;
			foreach ($xCampi as $campo) {
				$col+=1;
				$dim=round($max[$col]*$ratio, 0)+$margini;
				$par=($tot==$col?1:0);

				$this->Cell($dim, 5, strip_tags($campo[0]), 1, $par, 'L', 1);
			}
		}
		function Footer() {
			$this->SetFont('Arial', '', 6);
			$this->Cell(280, 5, 'Pagina: '.$this->PageNo().' di {nb}', 0, 1, 'C');
		}
	}
	$pdf = new PDF('L','mm','A4');
	$pdf->SetFont('Arial', '', 10*$ratio);
	$pdf->SetDisplayMode('default', 'single');
	$pdf->SetFillColor(222, 222, 222);  //grigio chiaro
	$pdf->SetAutoPageBreak(true, 10);
	$pdf->AliasNbPages();
	$pdf->AddPage();

	$tot=count($xCampi);
	foreach ($tabella as $riga) {
		$col=0;
		foreach ($riga as $colonna) {
			$col+=1;
			$dim=round($max[$col]*$ratio, 0)+$margini;
			$par=($tot==$col?1:0);
			$pdf->Cell($dim, 4, strtolower($colonna), 1, $par, 'L' );
		}
	}
	
	$pdf->Output("stampa.pdf", "F");
	$html .="
	<SCRIPT type='text/javascript'>
		window.open('stampa.pdf','_blank').blur();
	</script>";

	return ($html);
}

function wf_ExportTable($sqlread, $aCampi, $schema="default") {
	/* preparazione DB */
	$ris = wf_getset($sqlread, "Errore MULINP10");
	if ($_SESSION[debug]) echo "<br><br> sqlRead ".$sqlread;

	$csv .="";
	$slk .="ID;P\n";
	// intestazioni
	$x=0;
	$y=1;
	foreach ($aCampi as $campo) {
		$x += 1;
		$val =strip_tags($campo[0]); 
		$csv .=$val.";"; 
		$slk .="C;Y".$y.";X".$x.';K"'.$val.'"'."\n"; 
		}
	$xls .="\n";
	
	// lettura db
	while ($row = wf_set2row($ris)) {
		$y += 1;
		$x=0;
		foreach ($aCampi as $campo) {
			$x += 1;
			If (FALSE === @eval("\$field = $campo[1];")) {
				$errmsg= 'MULTIMPUT - Errore nella descrizione del campo $campo[1] - eval fallita :  <['.$campo[1].']>';
				wf_error($errmsg);
			}
			$val .=strip_tags($field); 
			$csv .=$val.";"; 
			$slk .="C;Y".$y.";X".$x.";K\"".$val."\"\n"; 
		}
		$xls .="\n";
	}
	$slk .="E\n";

	// scrittura file di output
	$file="export.slk";
	$fileOut = fopen($file,"w");
	if (!$fileOut){die("Errore nella creazione di $file !");}
	fwrite($fileOut,$slk);

	return;
}

/*****************************************************************************
/ Funzione 		: wf_post
/ Descrizione : preparazione campo da post per scrittura in multimput
/ Parametri :
/ 	$par1				paramentro 1
/*****************************************************************************/
function wf_post($post, $default="null") {
	if($_POST[$post]) {
		if($_POST[$post]=="null") {
			return "null";
		}
		return "'".addslashes($_POST[$post])."'";
	}
	return $default;
}
