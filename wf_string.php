<?php
/*************************************************
' function...: wf_calendar
' utilizzo...: gestione calendario
' autore.....: pt
' progetto...: webfunc v1
'
' Data     Au Note
' 20.11.09 PT Creata funzione WF_NTOC
**************************************************/

function wf_instr($chr, $str) {
  if ($chr==null) return false;
  $res=strpos($str, $chr);
  if ($res===false) return false;
  else              return true;
}

function wf_ntoc($par1_num, $par2_ll) {
  $retval = "";
  $ll = strlen(trim($par1_num));
  if ($ll>=$par2_ll) {
    $retval = $par1_num;
  } else {
    $delta_ll = $par2_ll - $ll;
    $retval = substr("00000000000000000000000", 0, $delta_ll) . trim($par1_num);
  }
  return ($retval);
}