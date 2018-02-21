<?php
require "wf_index.php";	  	  // gestione applicazione
require "wf_connect.php";	    // gestione connesioni al DB
require "wf_date.php";		    // gestione date
require "wf_string.php";		  // gestione stringhe
require "wf_multinput.php";   // gestione browse, edit, insert, delete
require "wf_multiform.php";   // gestione form browse
require "wf_menu.php";			  // gestione menu db based
require "wf_login.php";		    // gestione login
require "wf_num2str.php";	    // converte numero in lettere
require "wf_dir.php";	        //
require "wf_sdati.php";			  // legge e scrive parametro su tabella sdati
require "wf_mail.php";				// invio email
require "wf_upload.php";			// upload documenti
require "wf_lov.php";				  // generazione LOV select/option
require "wf_getrow.php";			// Acquisisce un record dal database
require "wf_scron.php";			  // scrive nella scron
require "wf_encrypt.php";		  // codifica e decodifica stringhe
require "wf_template.php";		// crea html dal template
require "wf_pdf.php";				  // crea html dal template
require "wf_utility.php";		  // utility
require "wf_error.php";			  // gestione errori
require "wf_import.php";			// import file csv
require "wf_security.php";		// sicurezza da sql injection

return 'OK';