<?php
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

$gender = isset($_GET['gender']) ? $_GET['gender'] : '';
$state = isset($_GET['state']) ? $_GET['state'] : '';
$department = isset($_GET['department']) ? $_GET['department'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';
$salary = isset($_GET['salary']) ? $_GET['salary'] : '';
$salary_up = isset($_GET['salary_up']) ? $_GET['salary_up'] : '';

// Create new PDF document
$pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Author Name');
$pdf->SetTitle('All Employees Report');
$pdf->SetSubject('Employees Report');

// Disable default header and footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins(10, 10, 10); // left, top, right
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(20);

$pdf->SetAutoPageBreak(TRUE, 15);

// Add a page
$pdf->AddPage();
$companyInfo = get_data('company', ['id' => 1])[0];
$pdf->SetFont('aefurat', '', 12);
// $pdf->Image('./assets/images/banner.png', 0, 0, 280, 40); // Adjust size as needed
$primary_color = return_setting('primary_color');
$secondary_color = return_setting('secondary_color');

$primary_color = explode(",", hexToRgb($primary_color));
$secondary_color =explode(",", hexToRgb($secondary_color));

$logo = get_logo_name_from_url();
$pdf->Image($GLOBALS['logoPath'], 10, 10, 30);

$pdf->SetFont('helvetica', 'B', 16);
$y = 10;
$pdf->SetTextColor($primary_color[0], $primary_color[1], $primary_color[2]);
$pdf->SetXY(158, $y);
$pdf->Cell(130, 7, strtoupper($companyInfo['name']), 0, 0, 'R');
$pdf->SetTextColor(000, 000, 000);

$pdf->SetFont('helvetica', '', 10);
$y += 8;
$pdf->SetXY(158, $y);
$pdf->Cell(130, 7, $companyInfo['contact_phone'], 0, 0, 'R');

$y += 5;
$pdf->SetXY(158, $y);
$pdf->Cell(130, 7, $companyInfo['contact_email'], 0, 0, 'R');

$pdf->SetFont('helvetica', 'B', 12);
$y += 6;
$pdf->SetXY(158, $y);
$pdf->Cell(130, 7, strtoupper("List of all employees"), 0, 0, 'R');

$pdf->SetFont('helvetica', '', 10);
$y += 6;
$pdf->SetXY(158, $y);
$pdf->Cell(130, 7, "Print date " . date("F d Y h:i:s A"), 0, 0, 'R');

$pdf->SetFillColor($primary_color[0], $primary_color[1], $primary_color[2]);
$pdf->SetDrawColor($primary_color[0], $primary_color[1], $primary_color[2]);
$pdf->Rect(10, 45, 278, 0.2);
$y = 50;

$pdf->SetDrawColor(000, 000, 000);

// Table Header
$pdf->SetFont('aefurat', 'B', 10);
$pdf->SetXY(10, $y);
$pdf->Cell(15, 8, "Staff No.", 1, 0, 'L', true);
$pdf->Cell(70, 8, "Full name", 1, 0, 'L', true);
$pdf->Cell(40, 8, "Phone number", 1, 0, 'L', true);
$pdf->Cell(60, 8, "Email", 1, 0, 'L', true);
$pdf->Cell(50, 8, "Department", 1, 0, 'L', true);
$pdf->Cell(42, 8, "Location", 1, 1, 'L', true);

$y += 8;

$get_employees = "SELECT * FROM `employees` WHERE `status` = 'Active'";
if($gender) $get_employees .= " AND `gender` = '$gender'";
if($state) $get_employees .= " AND `state_id` = '$state'";
if($department) $get_employees .= " AND `branch_id` = '$department'";
if($location) $get_employees .= " AND `location_id` = '$location'";
if($salary) $get_employees .= " AND `salary` >= '$salary'";
if($salary_up) $get_employees .= " AND `salary` <= '$salary_up'";
$employees = $GLOBALS['conn']->query($get_employees);
$num = 1;



$pdf->SetFillColor(120, 120, 120);
$pdf->SetDrawColor(120, 120, 120);
if ($employees->num_rows > 0) {
    while ($row = $employees->fetch_assoc()) {
        $staff_no 	= $row['staff_no'];
        $full_name 	= $row['full_name'];
        $email 		= $row['email'];
        $phone_number = $row['phone_number'];
        $branch 	= $row['branch'];
        $location_id = $row['location_id'];
        $location_name = $row['location_name'];
        $location_name = "No specified";
        if($location_id != 0) $location_name = get_data('locations', ['id' => $location_id])[0]['name'];
        $added_date = new DateTime($row['added_date']);
        $added_date = $added_date->format('F d Y');

        // Check if we need to add a new page
        if ($y + 7 > 180) { // Adjust this value based on your layout
            $pdf->AddPage();
            $pdf->SetFont('aefurat', '', 10);
            $y = 10; // Reset Y position after adding a new page

            // Re-add table header on the new page
            $pdf->SetFont('aefurat', 'B', 10);
            $pdf->SetXY(10, $y);
			$pdf->Cell(15, 7, "Staff No.", 1, 0, 'L', true);
			$pdf->Cell(70, 7, "Full name", 1, 0, 'L', true);
			$pdf->Cell(40, 7, "Phone number", 1, 0, 'L', true);
			$pdf->Cell(60, 7, "Email", 1, 0, 'L', true);
			$pdf->Cell(50, 7, "Department", 1, 0, 'L', true);
			$pdf->Cell(42, 7, "Location", 1, 1, 'L', true);

            $y += 7;
        }

        // Add row
        $pdf->SetFont('aefurat', '', 10);
        $pdf->SetXY(10, $y);
        $pdf->Cell(15, 7, $staff_no, 1, 0, 'L');
        $pdf->Cell(70, 7, $full_name, 1, 0, 'L');
        $pdf->Cell(40, 7, $phone_number, 1, 0, 'L');
        $pdf->Cell(60, 7, $email, 1, 0, 'L');
        $pdf->Cell(50, 7, $branch, 1, 0, 'L');
        $pdf->Cell(42, 7, $location_name, 1, 1, 'L');

        $num++;
        $y += 7;
    }

    // Draw footer line on the last page
    $pdf->Rect(15, $y, 265, 0.1);
}

// Output the PDF
$pdf->Output("All Employees Report" . ".pdf", 'I');
?>
