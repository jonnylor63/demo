<?php
//======================================================
// Creazione PDF tabellari
// Parametri
// 	$sql 					: sql di estrazione dati
//  $acolonne			: array delle colonne della tabella
//									ele 0 : label intestazione
//									ele 1 : larghezza colonna
//									ele 2 : nome del campo da cui prelevare il valore
//									ele 3 : nome del campo da cui prelevare il valore
//  $title				: titolo report
//  $fontsize			: dimensione font righe
//  $rowsize			: dimensione altezza righe
//  $orientamento : orientamento L o P
//  $flgnumpag		: stampa numero pagina true/false
//	$footer				: pi� di pagina
// 	$template     : template da utilizzare per ogni pagina
// Ritorno
// 	nessuno
//======================================================
// Autore - Giovanni Lorenzini
//======================================================

function wf_pdf($sql, $acolonne, $title="", $fontsize=10, $rowsize=6, $orientamento="P", $flgnumpag=true, $footer="piede", $template=null) {
	$set=wf_getset($sql, "wf_pdf 001");

	define('FPDF_FONTPATH','./font/');
  include_once('./class/fpdf/fpdf.class.php');
	class myPDF extends FPDI {
		var $footer;
		var $acol;
		var $rowsize;
		function Header() {
      $this->SetFillColor(100, 100, 100); 
      $this->SetTextColor(  0,   0,   0);
			$this->SetFont('Arial', 'B', 12);
			$this->Cell(95, 10, $this->title, 0, 0, 'L');
      $this->SetFont('Arial', 'B', 10);
			$this->Cell(95, 10, date('d/m/Y'), 0, 1, 'R');
      $this->SetTextColor(255, 255, 255);
			
			foreach ($this->acol as $colonna) {
				$this->Cell($colonna[1], $this->rowsize, $colonna[0], 1, 0, 'L', 1);
			}
			$this->ln();
		}
		function Footer() {
			$this->SetFont('Arial', '', 8);
      $this->SetFillColor(222, 222, 222);
      $this->SetTextColor(  0,   0,   0);  //nero
			$this->SetY(-12);
			$this->Cell(95, 6, $this->footer, 0, 0, 'L', 1);
			$this->Cell(95, 6, 'Pagina: '.$this->PageNo().' di {nb}', 0, 1, 'R', 1);
		}
	}

	$pdf = new myPDF($orientamento,'mm','A4');
	if ($template) {$pdf->setSourceFile($template);}
	$pdf->SetMargins(10,5);
	$pdf->SetAutoPageBreak(false);
	$pdf->SetFont('Arial','',$fontsize);
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFillColor(222, 222, 222);
	$pdf->AliasNbPages();
	$pdf->title 	= $title;
	$pdf->footer 	=$footer;
	$pdf->acol 	 	=$acolonne;
	$pdf->rowsize	=$rowsize;
	

	// import pagina
	$pdf->AddPage();
	if ($template) {$pdf->useTemplate($pdf->importPage(1), 0, 0);}

  $fill = false;
  while ($row = wf_set2row($set)) {
		foreach ($acolonne as $colonna) {
			$pos=($colonna[3]?$colonna[3]:"L");
			$pdf->Cell($colonna[1], $rowsize, $row[$colonna[2]], 1, 0, $pos, $fill);
		}
    $pdf->ln();
    $fill = !$fill;
  }
	// var_dump($pdf);
	$pdf->Output();
	exit;
}
?>