<?php
/***************************************************************************
* GESTIONE DATE IN PHP
***************************************************************************/
// ******* GESTIONE DATE *******	

function wf_fdate($dtstr, $format="d/m/Y") {
  // trasformazione data in formato specifico  
  if (! $dtstr) return "";
  $dtstr=str_replace("/", "-", $dtstr);
  
  if (($timestamp = strtotime($dtstr)) === false) {
    $ret = "Error in string ($dtstr)";
	} else {
	  $ret = date($format, $timestamp);
	}
  return $ret;
}


function wf_dtoc($dtstr) {
  // IN yyyy/mm/dd OUT gg/mm/yyyy
  if (! $dtstr) return "";
  return substr($dtstr,8,2)."/".substr($dtstr,5,2)."/".substr($dtstr,0,4);
}

function wf_dtoh($dtstr) {
  /*** 2009-04-08 14:54:58  ***/
  if (! $dtstr) return "";
  return substr($dtstr,11,5);
}
function wf_dtoch($dtstr) {
  /*** 2009-04-08 14:54:58  ***/
  if (! $dtstr) return "";
  return substr($dtstr,8,2)."/".substr($dtstr,5,2)."/".substr($dtstr,0,4)." ".substr($dtstr,11,5);
}
function wf_dtod($dtstr) {
  if (! $dtstr) return "";
  return substr($dtstr,8,2)."/".substr($dtstr,5,2)."/".substr($dtstr,0,4)." ".substr($dtstr,11,8);
}
function wf_ctod($dtstr, $sep="/") {
  // IN gg/mm/yyyy OUT yyyy/mm/dd
  if (! $dtstr) return "";
  return substr($dtstr,6,4).$sep.substr($dtstr,3,2).$sep.substr($dtstr,0,2);
}
function wf_ctodh($dtstr) {
  // IN gg/mm/yyyy OUT yyyy/mm/dd
  if (! $dtstr) return "";
  return substr($dtstr,6,4)."/".substr($dtstr,3,2)."/".substr($dtstr,0,2)." ".substr($dtstr,11,8);
}
function wf_ctom($dtstr) {
  // IN gg/mm/yyyy OUT yyyy-mm-dd
  if (! $dtstr) return "";
  return substr($dtstr,6,4)."-".substr($dtstr,3,2)."-".substr($dtstr,0,2);
}
function wf_dtos($dtstr) {
  if (! $dtstr) return "";
  return substr($dtstr,0,4).substr($dtstr,5,2).substr($dtstr,8,2);
}
function wf_stod($dtstr) {
  if (! $dtstr) return "";
  return substr($dtstr,0,4)."/".substr($dtstr,4,2)."/".substr($dtstr,6,2);
}

// ******* GESTIONE DATE *******	
function wf_mtommm($mese) {
  if (! $mese) return "";
  $amesi = array(1=>'gennaio', 'febbraio', 'marzo', 'aprile',
                'maggio', 'giugno', 'luglio', 'agosto',
                'settembre', 'ottobre', 'novembre','dicembre');
  return $amesi['$mese'];
}

function wf_dtoddmmm($dtstr) {
  // IN yyyy/mm/dd OUT gg-mmm
  if (! $dtstr) return "";
  $mese = substr($dtstr,5,2) * 1;
  $amesi = array("zero", "gen", "feb", "mar", "apr", "mag", "giu", "lug", "ago", "set", "ott", "nov", "dic");  
  return substr($dtstr,8,2) ."-" . $amesi[$mese];
}
function wf_dtoddmmmhhss($dtstr) {
  // IN yyyy/mm/dd OUT gg-mmm hh:ss
  if (! $dtstr) return "";
  $mese = substr($dtstr,5,2) * 1;
  $amesi = array("zero", "gen", "feb", "mar", "apr", "mag", "giu", "lug", "ago", "set", "ott", "nov", "dic");  
  return substr($dtstr,8,2) ."-" . $amesi[$mese]." ".substr($dtstr,11,5);;
}

function wf_diffdate($dt1,$dt2) {
  // calcola la differenza tra due date : data2 - data1
  if (substr($dt1,2,1)=="/") {
    $mkt1=mktime(0,0,0,substr($dt1,3,2),substr($dt1,0,2),substr($dt1,6,4));
    $mkt2=mktime(0,0,0,substr($dt2,3,2),substr($dt2,0,2),substr($dt2,6,4));
  } else {
    $mkt1=mktime(0,0,0,substr($dt1,5,2),substr($dt1,8,2),substr($dt1,0,4));
    $mkt2=mktime(0,0,0,substr($dt2,5,2),substr($dt2,8,2),substr($dt2,0,4));
  }
  return ($mkt2-$mkt1)/86400;
}

function wf_dateadd($interval, $number, $date) {
  // aggiunge un intervallo alla data
  // interval : yyyy anno, q quadrimestre, m mese, y/d/w giorno, ww settimana, h ora, n minuto, s secondo
  $date_time_array = getdate(strtotime(wf_ctod($date)));
  $hours = $date_time_array['hours'];
  $minutes = $date_time_array['minutes'];
  $seconds = $date_time_array['seconds'];
  $month = $date_time_array['mon'];
  $day = $date_time_array['mday'];
  $year = $date_time_array['year'];

  switch ($interval) {
      case 'yyyy':
          $year+=$number;
          break;
      case 'q':
          $year+=($number*3);
          break;
      case 'm':
          $month+=$number;
          break;
      case 'y':
      case 'd':
      case 'w':
          $day+=$number;
          break;
      case 'ww':
          $day+=($number*7);
          break;
      case 'h':
          $hours+=$number;
          break;
      case 'n':
          $minutes+=$number;
          break;
      case 's':
          $seconds+=$number; 
          break;            
  }
  $timestamp= mktime($hours,$minutes,$seconds,$month,$day,$year);
  return date("d/m/Y", $timestamp);
}

function wf_chkdate($dt) {
	if (!$dt) return true;
	if ($dt=="00/00/0000") return false;

	list($dd,$mm,$yyyy) = explode('/',$dt);
	if (!checkdate($mm,$dd,$yyyy)) {
		return false;
	}
	return true;
}
