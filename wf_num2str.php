<?php
function wf_num2str($numero) {  
  $unita          = array("","uno","due","tre","quattro","cinque","sei","sette","otto","nove");  
  $decina1        = array("dieci","undici","dodici","tredici","quattordici","quindici","sedici","diciassette","diciotto","diciannove");  
  $decine         = array("","dieci","venti","trenta","quaranta","cinquanta","sessanta","settanta","ottanta","novanta");  
  $decineTroncate = array("","","vent","trent","quarant","cinquant","sessant","settant","ottant","novant");  
  $centinaia      = array("","cento","duecento","trecento","quattrocento","cinquecento","seicento","settecento","ottocento","novecento");  

  // Inizializzo variabile contenente il risultato  
  $risultato = "";  

  // Faccio padding a 9 cifre  
  $stringa = str_pad($numero, 9, "0", STR_PAD_LEFT);  

  // Per ogni gruppo di tre cifre faccio il conto  
  for($i=0;$i<9;$i=$i+3) {  
    // Uso una variabile temporanea  
    $tmp = "";  

    // Centinaia  
    $tmp .= $centinaia[$stringa[$i]];         

    // Decine da 2 a 9  
    if($stringa[$i+1] != "1") {  
      if($stringa[$i+2] == "1" || $stringa[$i+2] == "8")  
        $tmp = $tmp . $decineTroncate[$stringa[$i+1]];  
      else  
        $tmp = $tmp . $decine[$stringa[$i+1]];        

      $tmp = $tmp . $unita[$stringa[$i+2]];  
    } else // Undici, dodici, tredici, ecc...  
    {  
      $tmp .= $decina1[$stringa[$i+2]];  
    }  

    // Aggiungo suffissi quando necessario  
    if($tmp != "" && $i==0)  
      $tmp .= "milioni";  

    if($tmp != "" && $i==3)  
      $tmp .= "mila";  

    // Aggiungo a risultato finale  
    $risultato .= $tmp;  

    // Caso speciale "mille" / "un milione" -> RISOLVE BUG "unmilioneunomilauno"  
    if($i == 0 && $stringa[$i] == "0" && $stringa[$i+1] == "0")  
      $risultato = str_replace("unomilioni","unmilione",$risultato);  
    if($i == 3 && $stringa[$i] == "0" && $stringa[$i+1] == "0")  
      $risultato = str_replace("unomila","mille",$risultato);  
  }  

  // ZERO!  
  if($risultato == "")  
    return "zero";  
  else  
    return  $risultato;  
}  
?>