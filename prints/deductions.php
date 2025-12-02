<?php
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
// Extend the TCPDF class to create a custom footer
class MYPDF extends TCPDF {
    // Page footer
    public function Footer() {
        // Set position to 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Create new PDF document
$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Author Name');
$pdf->SetTitle('Deductions Reports');
$pdf->SetSubject('Deductions Reports');

$primary_color = return_setting('primary_color');
$secondary_color = return_setting('secondary_color');

$primary_color = explode(",", hexToRgb($primary_color));
$secondary_color =explode(",", hexToRgb($secondary_color));

// Disable default header and footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins(10, 10, 10); // left, top, right
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(20);

$pdf->SetAutoPageBreak(TRUE, 15);

// Add a page
$pdf->AddPage();

$pdf->SetFont('aefurat', '', 12);
// $pdf->Image('./assets/images/banner.png', 0, 0, 280, 40); // Adjust size as needed
$pdf->Image($GLOBALS['logoPath'], 10, 10, 30);
// Set header rectangle
$pdf->SetFillColor($primary_color[0], $primary_color[1], $primary_color[2]);
$pdf->SetDrawColor($primary_color[0], $primary_color[1], $primary_color[2]);

$y = 10;
$companyInfo = get_data('company', ['id' => 1])[0];
$pdf->SetTextColor($primary_color[0], $primary_color[1], $primary_color[2]);
$pdf->SetXY(70, $y);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(130, 7, strtoupper($companyInfo['name']), 0, 0, 'R');
$pdf->SetTextColor(000, 000, 000);

$pdf->SetFont('helvetica', 'B', 10);

$y += 8;
$pdf->SetXY(70, $y);
$pdf->Cell(130, 7, $companyInfo['contact_phone'], 0, 0, 'R');

$y += 5;
$pdf->SetXY(70, $y);
$pdf->Cell(130, 7, $companyInfo['contact_email'], 0, 0, 'R');

$pdf->SetFont('helvetica', 'B', 12);
$y += 6;
$pdf->SetXY(70, $y);
$pdf->Cell(130, 7, strtoupper("Deductions Reports"), 0, 0, 'R');

$pdf->SetFont('helvetica', '', 10);
$y += 6;
$pdf->SetXY(70, $y);
$pdf->Cell(130, 7, date('F Y', strtotime($month)), 0, 0, 'R');
$y += 6;
$pdf->SetXY(70, $y);
$pdf->Cell(130, 7, "Print date " . date("F d Y h:i:s A"), 0, 0, 'R');

$y += 10;

$pdf->Rect(10, $y, 190, 0.2);

$pdf->SetDrawColor(0, 0, 0);
$y += 5;
// Table Header
$pdf->SetFont('aefurat', 'B', 10);
$pdf->SetXY(10, $y);
$pdf->Cell(25, 7, "Staff No.", 1, 0, 'L', true);
$pdf->Cell(70, 7, "Full name", 1, 0, 'L', true);
$pdf->Cell(32, 7, "Earnings", 1, 0, 'L', true);
$pdf->Cell(32, 7, "Deductions", 1, 0, 'L', true);
$pdf->Cell(31, 7, "Net pay", 1, 1, 'L', true);


$y += 7;

$query = "SELECT `staff_no`, `full_name`, (`allowance` + `bonus` + `commission`) AS earnings, (`loan` + `advance` + `deductions`) AS total_deductions, (`base_salary` + (`allowance` + `bonus` + `commission`) - (`loan` + `advance` + `deductions`) - `tax`) AS net_salary FROM `payroll_details` WHERE `month` LIKE '$month' AND (`loan` + `advance` + `deductions`) > 0";
$employees = $GLOBALS['conn']->query($query);
$num = 1;

if ($employees->num_rows > 0) {
    while ($row = $employees->fetch_assoc()) {
        $staff_no = $row['staff_no'];
        $full_name = $row['full_name'];
        $earnings = $row['earnings'];
        $total_deductions = $row['total_deductions'];
        $net_salary = $row['net_salary'];
        // Check if we need to add a new page
        if ($y + 7 > 280) { // Adjust this value based on your layout
            $pdf->AddPage();
            $pdf->SetFont('aefurat', '', 10);
            $y = 10; // Reset Y position after adding a new page

            // Re-add table header on the new page
            $pdf->SetFont('aefurat', 'B', 10);
            $pdf->SetXY(10, $y);
			$pdf->Cell(25, 7, "Staff No.", 1, 0, 'L', true);
			$pdf->Cell(70, 7, "Full name", 1, 0, 'L', true);
			$pdf->Cell(32, 7, "Earnings", 1, 0, 'L', true);
			$pdf->Cell(32, 7, "Deductions", 1, 0, 'L', true);
			$pdf->Cell(31, 7, "Net pay", 1, 1, 'L', true);
            

            $y += 7;
        }

        // Add row
        $pdf->SetFont('aefurat', '', 10);
        $pdf->SetXY(10, $y);
		$pdf->Cell(25, 7, "$staff_no", 1, 0, 'L', 0);
		$pdf->Cell(70, 7, "$full_name", 1, 0, 'L', 0);
		$pdf->Cell(32, 7, formatMoney($earnings), 1, 0, 'L', 0);
		$pdf->Cell(32, 7, formatMoney($total_deductions), 1, 0, 'L', 0);
		$pdf->Cell(31, 7, formatMoney($net_salary), 1, 1, 'L', 0);


        $num++;
        $y += 7;
    }

    // Draw footer line on the last page
    $pdf->Rect(10, $y, 190, 0.1);
} else {
    $pdf->SetXY(10, $y+10);
    $pdf->Cell(190, 7, "No records were found", 0, 0, 'C', 0);
}

// Output the PDF
$pdf->Output("Deductions Reports" . date('F Y', strtotime($month)) .".pdf", 'I');
?>
