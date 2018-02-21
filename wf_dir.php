<?php
// ******* GETIONE file *******
function wf_dir() {
  $ldir="c:\\www\\LoTo\\Asm\\new\\doc\\DVD3Stelle\\";
  $host="http://localhost";
  $hdir="LoTo/Asm/new/doc/DVD3Stelle/";
  
  echo "
	<script type='text/javascript'>
		function wf_showEle(ele){
			if(ele.style.display != 'none')	{ele.style.display='none';}
			else {ele.style.display='block';}
		}
	</script>
	";

  echo "<ul>";
  wf_dir_list($ldir, $hdir, $host, 24, 0);
  echo "</ul>";
}

function wf_dir_list($ldir,$hdir,$host,$nchr,$deep) {
  if ($dh = opendir($ldir)) {
    $deep=$deep+1;
    $name=substr($ldir,$nchr);
    $name=str_replace("\\", "-", $name);
    
    $st=($deep>3?"display: none;":"");
    echo "<li class=xx onclick='wf_showEle(document.getElementById(\"".$name."\"));' style='cursor:pointer; text-decoration:underline;'>".substr($ldir,$nchr,strlen($ldir)-$nchr-1)."</li>";
    echo "<ul id='".$name."' style='$st'>";
    while (($file = readdir($dh)) !== false) {
      if (filetype($ldir . $file)=="dir"){
        if ($file!="." and $file!=".."){
          $nchr=strlen($ldir);
          wf_dir_list($ldir .$file."\\",$hdir .$file."\\",$host,$nchr,$deep);
        }
      } else {
       $xdir=str_replace("\\", "/", $hdir);
       echo "<li><a href='".$host.$xdir.$file."'>$file</a></li>";
      }
    }
    closedir($dh);
    echo "</ul>";
  }

}
