<?
/*************************************************
' function...: wf_calendar
' utilizzo...: gestione calendario
' autore.....: gl
' progetto...: webfunc v1
'
' Data     Au Note
' 24.11.09 GL WF_CALENDAR - in sviluppo non utilizzare
**************************************************/

function wf_calendar($mese, $anno, $fSposta) {
  $dt=strtotime($mese."/1/".$anno); 
  
  $meseprec=($mese==1?12:$mese-1);
  $annoprec=($mese==1?$anno-1:$anno);
  $dp=strtotime($meseprec."/1/".$annoprec); 

  $mesesucc=($mese==12?1:$mese+1);
  $annosucc=($mese==12?$anno+1:$anno);
  $ds=strtotime($mesesucc."/1/".$annosucc); 
  
  switch ($mese) {
    case 0: $descmese="booooo";   break;
    case 1: $descmese="Gennaio";  break;
    case 2: $descmese="Febbraio"; break;
    case 3: $descmese="Marzo";    break;
    case 4: $descmese="Aprile";   break;
    case 5: $descmese="Maggio";   break;
    case 6: $descmese="Giugno";   break;
    case 7: $descmese="Luglio";   break;
    case 8: $descmese="Agosto";   break;
    case 9: $descmese="Settembre";break;
    case 10: $descmese="Ottobre"; break;
    case 11: $descmese="Novembre";break;
    case 12: $descmese="Dicembre";break;
  }


  // intestazione
  $ts=($fSposta==true?"<a href=\"calendario.php?mese=".$meseprec."&anno=".$annoprec."\" class='semplice' style='color:White' title='Vai al mese precedente'>&lt;</a>":"");
  $td=($fSposta==true?"<a href=\"calendario.php?mese=".$mesesucc."&anno=".$annosucc."\" class='semplice' style='color:White' title='Vai al mese successivo'>&gt;</a>":"");
  
  $html.= "<table id='Calendar1_CalendarEvents' class=tCalendar border='1' bordercolor='#7AAEE7' cellspacing='0' cellpadding='0' rules='all' border='0'> \n
            <tr> \n
              <td colspan='7'> \n
                <table class='tCalendar' cellspacing='0' border='0' > \n
                  <tr> \n
                    <td align='left'   class=tCaleArrow > ".$ts."</td> \n
                    <td align='center' class=tCaleMonth >".$descmese." ".$anno."</td> \n
                    <td align='right'  class=tCaleArrow >".$td."</td> \n
                  </tr> \n
                </table> \n
          	  </td> \n
          	</tr> \n
          	<tr > \n
          	  <th class='Calendargiorni' align='center' abbr='luned�' scope='col'>Lunedi</th> \n
          	  <th class='Calendargiorni' align='center' abbr='marted�' scope='col'>Martedi</th> \n
          	  <th class='Calendargiorni' align='center' abbr='mercoled�' scope='col'>Mercoledi</th> \n
          	  <th class='Calendargiorni' align='center' abbr='gioved�' scope='col'>Giovedi</th> \n
          	  <th class='Calendargiorni' align='center' abbr='venerd�' scope='col'>Venerdi</th> \n
          	  <th class='Calendargiorni' align='center' abbr='sabato' scope='col'>Sabato</th> \n
          	  <th class='Calendargiorni' align='center' abbr='domenica' scope='col'>Domenica</th> \n
        	  </tr> \n
          	";

  /* settimana inizio mese */
  $dow = (strval(strftime("%w", $dt))==0?7:strval(strftime ("%w", $dt)))-1;    // giorno della settimana del 1� del mese
  $ggini=round(($dt-$dp)/86400-$dow+1);                                          // giorno iniziale del mese precedente, della prima settimana 

  // giorni mese precedente
  $html.="<tr>";
  for($i=$ggini; $i<($ggini+$dow); $i++) {
    $dw=strftime("%w", strtotime($meseprec."/".$i."/".$annoprec));
    $sfondo=(($dw==0 or $dw==6)?"CCCCFF":"DFEEFF");
	 $g = (int) round($i);
    $html .= "<td class='CalendarTD' align='left' valign='top' style='color:#999999;background-color:#".$sfondo.";width:14%;'>".$g."</td> \n";
  }

  // giorni mese in corso
  $ggdt=round(($ds-$dt)/86400);
  for($i=1; $i<=$ggdt; $i++) {
    $dw=strftime("%w", strtotime($mese."/".$i."/".$anno));
    $sfondo=(($dw==0 or $dw==6)?"CCCCFF":"DFEEFF"); //DFEEFF
    $sfondo=((date('j')==$i AND date('n')==$mese AND date('Y')==$anno )?"ffba71":$sfondo);
    $giorno="";

    /* eventi */
    $sql = "
    SELECT idaeven, oggettoaeven, tipoaeven, hrefaeven
	 FROM aeven
	 WHERE dtiniaeven<='".$anno."/".$mese."/".$i."' and dtfinaeven>='".$anno."/".$mese."/".$i."'
    ";
 		$ris = wf_getset($sql, "Errore CALENDAR02");
 	while ($row = wf_set2row($ris)) {
  	  if ($row[tipoaeven]=='I') {
  	    $giorno.="<br><a href='".$row[hrefaeven]."?id=".$row[idaeven]."' title='Immersioni - ".$row[oggettoaeven]."'><img src='image/diveflag.png' WIDTH=12 alt='Immersione Ricreativa'>".$row[oggettoaeven]."</a>";
  	  } else {
  	    $giorno.="<br><a href='".$row[hrefaeven]."?id=".$row[idaeven]."' title='Eventi - ".$row[oggettoaeven]."'><img src='image/gruppo.png' WIDTH=12 alt='Evento Sociale'>".$row[oggettoaeven]."</a>";
  	  }
    }
    
    /* corsi */
    $sql = "
    SELECT rcotl.idacors, descraorga, tipoabrev, descrabrev, hrefabrev, dtlezione, luogorcotl, descrrcotl, idatile
  	FROM ((rcotl
  	  LEFT JOIN acors ON rcotl.idacors=acors.idacors)
  	  LEFT JOIN abrev ON acors.idabrev=abrev.idabrev)
  	  LEFT JOIN aorga ON abrev.idaorga=aorga.idaorga
  	WHERE dtlezione='".$anno."/".$mese."/".$i."'
    ";
    $ris = wf_getset($sql);
  	while ($row = wf_set2row($ris)) {
  	  if ($row[idatile]==3 or $row[idatile]==4 or $row[idatile]==8) {
  	    $giorno.="<br><a href='".$row[hrefabrev]."?id=".$row[idacors]."' title='Corso ".$row[descrabrev]." - ".$row[descrrcotl]." (".$row[luogorcotl].")'>
  	      <img src='image/ciambella.ico' WIDTH=12 alt='Immersione di corso'>".$row[descrabrev]."</a>";
  	  } else {
  	    $giorno.="<br><a href='".$row[hrefabrev]."?id=".$row[idacors]."' title='Corso ".$row[descrabrev]." - ".$row[descrrcotl]." (".$row[luogorcotl].")'>
  	      <img src='image/libro.png' WIDTH=12 alt='Lezione di corso'>".$row[descrabrev]."</a>";
  	  }
    }
    
    $html .= "<td class='CalendarTD' align='left' valign='top' style='color:#0060A0;background-color:#".$sfondo.";width:14%;height:55px;'>".$i.$giorno."</td> \n";
    $html.=($dw==0?"</tr><tr>":"");
  }
  
  // giorni mese successivo
  if ($dw<>0) {
    for($i=1; $i<=(7-$dw); $i++) {
      $sfondo=($i>(5-$dw)?"CCCCFF":"DFEEFF");
      $html .= "<td class='CalendarTD' align='left' valign='top' style='color:#999999;background-color:#".$sfondo.";width:14%;'>".$i."</td> \n";
    }
  }
  $html.="</tr> \n
          </table> \n";

  /* intestazione */
  return ($html);
}


?>