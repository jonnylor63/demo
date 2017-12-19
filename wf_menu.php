<?php
/*************************************************
* modulo.....: wf_menu
* utilizzo...: gestione menu ed accesso a funzioni
**************************************************/

/*****************************************************************************
/ Funzione : wf_readsmenu
/ Descrizione : legge smenu e crea array
/*****************************************************************************/
function wf_readsmenu($grupposmenu="admin") {
	// LETTURA DB MENU
	if (! wf_chklogin()) {
		// menu pubblico
		$sql="select me.*
						from smenu me
					where me.grupposmenu='$grupposmenu'
						and privatesmenu=0
					order by me.livello1smenu, me.livello2smenu, me.livello3smenu,me.livello4smenu,me.livello5smenu,me.livello6smenu";
	} else if ($_SESSION[s_superadmin]) {
		// utenti amministrazione sistema ovvero che NON hanno un ruolo padre -> appartengono al primo ruolo
		$sql="select me.*
						from smenu me
					where me.grupposmenu='$grupposmenu'
					order by me.livello1smenu, me.livello2smenu, me.livello3smenu,me.livello4smenu,me.livello5smenu,me.livello6smenu";
	} else {
		// utenti normali ovvero che hanno un ruolo padre
		$sql="
					select me.*
						from sprme re
							 , smenu me
					 where me.idsmenu = re.idsmenu
						 and flgread=1
						 and idsprof=".$_SESSION['s_idsprof']."
						 and me.grupposmenu='$grupposmenu'
					 order by livello1smenu,livello2smenu,livello3smenu,livello4smenu,livello5smenu,livello6smenu
				 ";
	}
	$ris = wf_getset($sql, "Errore MENU06");
	while ($row = wf_set2row($ris)) {
		$active="";
		if ($_GET[proc]<>"" and $row["procsmenu"]==$_GET[proc]) {
			if($row["flgattivosmenu"]==1) {
				$active=true;
			} else {
				$menu[count($menu)-1]["active"]=true;
			}
		}
		if($row["flgattivosmenu"]==1) {
			$menu[]=[
				"nome"=>$row["nome"],
				"l1"=>$row["livello1smenu"],
				"l2"=>$row["livello2smenu"],
				"l3"=>$row["livello3smenu"],
				"label"=>$row["labelsmenu"],
				"href"=>$row["hrefsmenu"],
				"proc"=>$row["procsmenu"],
				"target"=>$row["targetsmenu"],
				"par1"=>$row["par1smenu"],
				"par2"=>$row["par1smenu"],
				"active"=>$active
			];
		}
	}
	return $menu;
}

function wf_hrefsmenu($ele) {
	$href=$ele["href"];
	$proc=$ele["proc"];
	$par1=$ele["par1"];
	$par2=$ele["par2"];

	if ($href) {
		$newhref=$href;
	} elseif ($proc) {
		if ($par1<>"" and $par2<>"") { $par="&wfp1=".$par1."&wfp2=".$par2;
		} elseif ($par1<>"") 		 { $par="&wfp1=".$par1;
		} elseif ($par2<>"") 		 { $par="&wfp2=".$par2;
		} else 						 { $par="";
		}
		$newhref="index.php?proc=".$proc.$par;

	} else {
		//$newhref="index.php?proc=listmenu&id=".$row[livello1smenu];
		$newhref="#";
	}

	return $newhref;
}

/*****************************************************************************
/ Funzione : wf_megamenu
/ Descrizione : MEGAMENU con drop-down multicolonna - derivazione jQuery MegaMenu Plugin http://www.geektantra.com
/*****************************************************************************/
function wf_menu($grupposmenu="admin", $class=null) {
	$menu=wf_readsmenu($grupposmenu);

	$html = '<ul class="sidebar-menu">';
	foreach($menu as $key=>$ele) {
		$target=($ele["target"]=="b"?"target='_blank'":"");

		$href=$ele["href"];
		$proc=$ele["proc"];
		$par1=$ele["par1"];
		$par2=$ele["par2"];
		$active=$ele["active"];
		$newhref=wf_hrefsmenu($ele);

		$liv=($ele["l3"]<>0?3:($ele["l2"]<>0?2:1));
		$suc=($menu[$key+1]["l3"]<>0?3:($menu[$key+1]["l2"]<>0?2:1));

		$attiva=($active?"active":"");
		if($liv==1 and $suc==$liv) {
			// Mono livello
			$html.='
			<li class="'.$attiva.'"">
				<a href="'.$newhref.'" '.$target.'>
					<i class="fa fa-book"></i>
					<span>'.$ele["label"].'</span>
				</a>
			</li>';
		}

		// SECONDO LIVELLO
		if($liv==1 and $suc>$liv) {
			// Apri Primo Sub Livello
			$active2=$menuopen="";
			$display="none";
			foreach($menu as $key2=>$ele2) {
				if ($_GET[proc]<>"" and $ele["l1"]==$ele2["l1"] and $ele2["active"]) {
					$active2="active";
					$menuopen="menu-open";
					$display="block";
				}
			}

			$html.='
				<li class="treeview '.$active2.'">
				  <a href="#">
					<i class="fa fa-dashboard"></i> <span> '.$ele["label"].'</span>
					<span class="pull-right-container">
					  <i class="fa fa-angle-left pull-right"></i>
					</span>
				  </a>
				  <ul class="treeview-menu '.$menuopen.'" style="display: '.$display.';">
			';
		}
		if($liv==2 and $suc==$liv) {
			// Mono Sub Livello
			$html.='
				<li class="'.$attiva.'"">
					<a href="'.$newhref.'">
						<i class="fa fa-circle-o"></i>
						'.$ele["label"].'
					</a>
				</li>
			';
		}
		if($liv==2 and $suc<$liv and $suc==1) {
			// Chiudi Primo Sub Livello
			$html.="</ul></li>";
		}

		// TERZO LIVELLO
		if($liv==2 and $suc>$liv) {
			// Apri Primo Sub Livello
			$active2=$menuopen="";
			$display="none";
			foreach($menu as $key2=>$ele2) {
				if ($_GET[proc]<>"" and $ele["l2"]==$ele2["l2"] and $ele2["active"]) {
					$active2="active";
					$menuopen="menu-open";
					$display="block";
				}
			}
			$html.='
				<li class="'.$active2.'">
				  <a href="#"><i class="fa fa-circle-o"></i>  '.$ele["label"].'
					<span class="pull-right-container">
					  <i class="fa fa-angle-left pull-right"></i>
					</span>
				  </a>
				  <ul class="treeview-menu '.$menuopen.'" style="display: '.$display.';">
			';
		}
		if($liv==3 and $suc<=$liv) {
			// Mono Sub Livello
			$html.='
				<li class="'.$attiva.'"">
					<a href="'.$newhref.'">
						<i class="fa fa-circle-o"></i>
						'.$ele["label"].'
					</a>
				</li>
			';
		}
		if($liv==3 and $suc<$liv) {
			// Chiudi Primo Sub Livello
			$html.="</ul></li>";
			if($suc==1) {
				// Chiudi Primo Sub Livello
				$html.="</ul></li>";
			}
		}

	}
	$html.="</ul>";

	return ($html);
}

/*****************************************************************************
/ Funzione : wf_submenu
/ Descrizione : generazione di sub menu con semplice struttura <ul> <li>
/*****************************************************************************/
function wf_submenu($grupposmenu="admin", $liv1, $liv2=null) {
	$key=$livello+1;

	if ($liv2==null) {
			$where="and me.livello2smenu>0 and me.livello3smenu=0";
	} else {
			$where="and me.livello2smenu=$liv2 and me.livello3smenu>0 and me.livello4smenu=0";
	}
	$sql="
			select me.*
				from smenu me
			 where me.grupposmenu='$grupposmenu'
				 and me.livello1smenu=$liv1
				 $where
			 order by livello1smenu,livello2smenu,livello3smenu,livello4smenu,livello5smenu,livello6smenu
		 ";
	$ris = wf_getset($sql, "Errore MENU07");
	$html.="<ul>\n";
	while ($row = wf_set2row($ris)) {
		if (basename($_SERVER['REQUEST_URI'])==$row[hrefsmenu]) {
			$html.="<li><a href='$row[hrefsmenu]' $target class='select'>$row[labelsmenu]</a></li> \n";
		} else {
			$html.="<li><a href='$row[hrefsmenu]' $target>$row[labelsmenu]</a></li> \n";
		}
	}
	$html.="</ul>\n";
	return ($html);
}

/*****************************************************************************
/ Funzione : wf_proc
/ Descrizione : elaborazione procedura
/*****************************************************************************/
function wf_proc($proc, $grupposmenu="admin", $mostraproc=false) {  
	if (!$proc) {
		$start=true;
		$proc="home";
	}
	
	/* !ricerca procedura e verifica autorizzazione accesso */
	$public=false;
	if (! wf_chklogin()) {
		// menu pubblico
		$public=true;
		$sql="select * 
						from smenu me 
					where me.grupposmenu='$grupposmenu'
						and privatesmenu=0
						and procsmenu='$proc'
					";
	} else if ($_SESSION["s_superadmin"]) {
		// utenti di SuperAmministrazione
		$sql="select * 
						from smenu me 
					where me.grupposmenu='$grupposmenu'
						and procsmenu='$proc'
					";
	} else {
		// utenti normali
		$sql="
					select *, me.idsmenu idsmenu 
						from sprme re
							 , smenu me
					 where me.idsmenu = re.idsmenu
						 and me.grupposmenu='$grupposmenu'
						 and flgread=1
						 and idsprof=".$_SESSION['s_idsprof']."
						 and procsmenu='$proc'
					";
	}
  $row = wf_getrow($sql, "wf_proc 001");
  if ($row["procsmenu"]<>$proc) {
  	if ($proc=="home") {
	    Header("Location: login.php");
			exit;
  	} else {
  		wf_error("Accesso non consentito. Proc:$proc");
  	}
  }
  
	$aret[TITLE]		=$row[titolosmenu];
	$aret[TEMPLATE]	=$row[templatesmenu];
	$aret[ISPRIVATE]=$row[privatesmenu];
	$aret[HTITLE]		=$row[titolosmenu];
	$aret[HDESCR]		=$row[titolosmenu];
	$includesmenu		=$row[includesmenu];
	$funcsmenu			=$row[funcsmenu];
	$par1smenu			=$row[par1smenu];
	$par2smenu			=$row[par2smenu];
	$livello1smenu	=$row[livello1smenu];
	$livello2smenu	=$row[livello2smenu];
	$livello3smenu	=$row[livello3smenu];
	$livello4smenu	=$row[livello4smenu];
	$livello5smenu	=$row[livello5smenu];
	$livello6smenu	=$row[livello6smenu];


	/* !inclusone modulo */
	if ($includesmenu) {
		If (FALSE === @include($includesmenu)) {
    	$errmsg="Modulo >$includesmenu< non trovato.";
    	wf_error($errmsg);
		} 
	} 
  if ($funcsmenu=="") {
  	$funcsmenu="wf_listmenu";
  	$par1smenu=$livello1smenu;
  }

  /* !rimozione parentesi ed eventuali parametri */
  if (strpos($funcsmenu, "(")) {
  	$funcsmenu=substr($funcsmenu, 0, strpos($funcsmenu, "("));
  }

	/* !lancio funzione */
	if (function_exists($funcsmenu)) {
		$_SESSION["s_xxxxx"]			=$row["procsmenu"];	
		$_SESSION["s_yyyyy"]			=$sql;	
		if ($public or $_SESSION[s_superadmin]) {
			$_SESSION["s_flgcreate"]	=true;	
			$_SESSION["s_flgread"]		=true;
			$_SESSION["s_flgupdate"]	=true;	
			$_SESSION["s_flgdelete"]	=true;	
			$_SESSION["s_flgprint"]		=true;
			$_SESSION["s_flgexport"]	=true;	
			$_SESSION["s_flgnew"]			=true;
			
		} else {
			$_SESSION["s_flgcreate"]	=($row["flgcreate"]?true:false);	
			$_SESSION["s_flgread"]		=($row["flgread"]  ?true:false);
			$_SESSION["s_flgupdate"]	=($row["flgupdate"]?true:false);	
			$_SESSION["s_flgdelete"]	=($row["flgdelete"]?true:false);	
			$_SESSION["s_flgprint"]		=($row["flgprint"] ?true:false);
			$_SESSION["s_flgexport"]	=($row["flgexport"]?true:false);	
			$_SESSION["s_flgnew"]			=($row["flgnew"] 	 ?true:false);
		}
		
		$aret[CONTENT]=call_user_func($funcsmenu, $par1smenu, $par2smenu);
	} else {
    $errmsg="Funzione >$funcsmenu< non trovata. Procedura >$proc< include modulo >$includesmenu<";
    wf_error($errmsg);
	}
	
	/* costruzione breadcrumb */
	$aret[BREAD]=wf_breadcrumb($row);
	
	return ($aret);
}

/*****************************************************************************
/ Funzione : wf_breadcrumb
/ Descrizione : costruzione breadcrumb
/*****************************************************************************/
function wf_breadcrumb($row) {
	$bread="";
	$menu=wf_readsmenu();
	foreach($menu as $key=>$ele) {
		if($ele["active"]){
			$l1=$ele["l1"];
			$l2=$ele["l2"];
			$l3=$ele["l3"];
			$l4=$ele["l4"];
			break;
		}
	}
	if($l1<>0) {
		foreach($menu as $key=>$ele) {
			if($ele["l1"]==$l1) {
				if($l2<>0) {
					$href=wf_hrefsmenu($ele);
					$bread.="<li><a href='".$href."'> ".ucfirst(strtolower($ele["label"]))."</a></li>";
				} else {
					$bread.="<li>".ucfirst(strtolower($ele["label"]))."</li>";
				}
				break;
			}
		}
	}
	if($l2<>0) {
		foreach($menu as $key=>$ele) {
			if($ele["l2"]==$l2) {
				$href=wf_hrefsmenu($ele);
				$bread.="<li><a href='".$href."'> ".ucfirst(strtolower($ele["label"]))."</a></li>";
				break;
			}
		}
	}
	if($l3<>0) {
		foreach($menu as $key=>$ele) {
			if($ele["l3"]==$l3) {
				$href=wf_hrefsmenu($ele);
				$bread.="<li><a href='".$href."'> ".ucfirst(strtolower($ele["label"]))."</a></li>";
				break;
			}
		}
	}
	if($l4<>0) {
		foreach($menu as $key=>$ele) {
			if($ele["l4"]==$l4) {
				$href=wf_hrefsmenu($ele);
				$bread.="<li><a href='".$href."'> ".ucfirst(strtolower($ele["label"]))."</a></li>";
				break;
			}
		}
	}


		$href=getCurrentURL("wfact,wfid,wfpag,wfsearch", "&");
	//$bread=" &raquo; <a href='$href'>".($row[labelsmenu])."</a>";
	
	$aret='<li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>'.$bread;
	return $aret;
}

/*****************************************************************************
/ Funzione : wf_listmenu
/ Descrizione : generazione lista menu
/*****************************************************************************/
function wf_listmenu($id=null) {
	$id=($id?$id:$_GET['id']);
	
	if ($_SESSION['s_idsprof']==1) {
	$sql="
		select * 
		from smenu
		where livello1smenu=".$id." 
			and flgattivosmenu=1
		order by livello1smenu,livello2smenu,livello3smenu,livello4smenu,livello5smenu,livello6smenu";
	} else {
	$sql="
		select * 
		from smenu
		left join sprme on sprme.idsmenu=smenu.idsmenu
		where livello1smenu=".$id." 
			and flgattivosmenu=1
			and flgread=1 
			and idsprof=".$_SESSION['s_idsprof']."
		order by livello1smenu,livello2smenu,livello3smenu,livello4smenu,livello5smenu,livello6smenu";
	}
	$ris = wf_getset($sql, "Errore MENU08");

	$html.="<div id='wf_listmenu'>";
	$livprec=0;
	while ($row = wf_set2row($ris)) {
		$liv=($row[livello6smenu]>0?6:($row[livello5smenu]>0?5:($row[livello4smenu]>0?4:($row[livello3smenu]>0?3:($row[livello2smenu]>0?2:1)))));
		if ($liv>$livprec) { $html.="<ul>"; }
		if ($liv<$livprec) { $html.="</ul>";}
		$livprec=$liv;
		
		if ($row[hrefsmenu]) {
			$newhref=$row[hrefsmenu];
		} elseif ($row[procsmenu]) {
			if ($row[par1smenu]<>"" and $row[par2smenu]<>"") {
				$par="&wfp1=".$row[par1smenu]."&wfp2=".$row[par2smenu];
			} elseif ($row[par1smenu]<>"") {
				$par="&wfp1=".$row[par1smenu];
			} elseif ($row[par2smenu]<>"") {
				$par="&wfp2=".$row[par2smenu];
			} else {
				$par="";
			}
			$newhref="index.php?proc=".$row[procsmenu].$par;
			
			if ($row[procsmenu]==$_GET[proc]) {
				$current="class='current'";
			}
		} else {
			$newhref="index.php?proc=listmenu&id=".$row[livello1smenu];
		}

		$html.="<li><a href='$newhref'>$row[labelsmenu]</a></li>";
	}
	$html.="</ul></div>";
	
	return ($html);
}

/*****************************************************************************
/ Funzione : wf_iconmenu
/ Descrizione : generazione di menu a bottoni con icona
/*****************************************************************************/
function wf_iconmenu($grupposmenu="admin", $liv1, $liv2=null) {
	$key=$livello+1;
	if ($liv2==null) {
			$where="and me.livello2smenu>0 and me.livello3smenu=0";
	} else {
			$where="and me.livello2smenu=$liv2 and me.livello3smenu>0 and me.livello4smenu=0";
	}
	$sql="
			select me.*
				from smenu me
			 where me.grupposmenu='$grupposmenu'
				 and me.livello1smenu=$liv1
				 $where
			 order by livello1smenu,livello2smenu,livello3smenu,livello4smenu,livello5smenu,livello6smenu
		 ";
	$ris = wf_getset($sql, "Errore MENU07");
	$html.="<ul>\n";
	while ($row = wf_set2row($ris)) {
		if (basename($_SERVER['REQUEST_URI'])==$row[hrefsmenu]) {
			// $html.="<li><a href='$row[hrefsmenu]' $target class='select'>$row[labelsmenu]</a></li> \n";
		} else {
			// $html.="<li><a href='$row[hrefsmenu]' $target>$row[labelsmenu]</a></li> \n";
		}
		$html.='
					<div class="icon-wrapper">
						<div class="icon">
							<a href="'.$row[hrefsmenu].'" '.$target.'>
								<img src="'.$row[imgsmenu].'" width="60" alt="">
								<span>'.$row[labelsmenu].'</span></a>
						</div>
					</div>
		';
	}
	$html.="</ul>\n";
	return ($html);
}
