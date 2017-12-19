<?
include("wf_index.php");	  	// gestione applicazione
include("wf_connect.php");	  // gestione connesioni al DB
include("wf_date.php");		    // gestione date
include("wf_string.php");		  // gestione stringhe
include("wf_multinput.php");  // gestione browse, edit, insert, delete
include("wf_multiform.php");  // gestione form browse
include("wf_menu.php");			  // gestione menu db based
include("wf_login.php");		  // gestione login
include("wf_num2str.php");	  // converte numero in lettere
include("wf_dir.php");	      // 
include("wf_sdati.php");			// legge e scrive parametro su tabella sdati
include("wf_mail.php");				// invio email
include("wf_upload.php");			// upload documenti
include("wf_lov.php");				// generazione LOV select/option
include("wf_getrow.php");			// Acquisisce un record dal database
include("wf_scron.php");			// scrive nella scron
include("wf_encrypt.php");		// codifica e decodifica stringhe
include("wf_template.php");		// crea html dal template
include("wf_pdf.php");				// crea html dal template
include("wf_utility.php");		// utility
include("wf_error.php");			// gestione errori
include("wf_import.php");			// import file csv
include("wf_security.php");		// sicurezza da sql injection

return 'OK';