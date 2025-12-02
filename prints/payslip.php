<?php 
// Extend the TCPDF class to create a custom footer
class MYPDF extends TCPDF {
    // Page footer
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 
            0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$payrollDetId = $_GET['rec_id'] ?? 0;

// Always get payroll record
$rec = [];
$query = $GLOBALS['conn']->query("SELECT * FROM `payroll_details` WHERE `id` = '$payrollDetId'");
if (!$query || $query->num_rows == 0) {
    die("Invalid payroll record");
}
$rec = $query->fetch_assoc();

$emp_id = $rec['emp_id'];
$month1 = $rec['month'];
$monthFormatted = date('F Y', strtotime($rec['month']));
$added_date = date('F d, Y', strtotime($rec['added_date']));

// Get employee info (from employees table)
$employee = $employee = $GLOBALS['employeeClass']->read($emp_id);
if (!$employee) {
    die("Employee not found");
}

// var_dump($employee);

$state_id = $employee['state_id'];
$taxPercentage = getTaxPercentage($rec['base_salary'], $state_id);

if (!$employee['avatar']) {
    $employee['avatar'] = (strtolower($employee['gender']) == 'female') 
        ? 'female_avatar.png' 
        : 'male_avatar.png';
}

$attenInfo = calculateAttendanceStats($emp_id, $month1);

// Create new PDF document
$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$companyInfo = get_data('company', ['id' => 1])[0];

// Document settings
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Hawlkar IT Solutions');
$pdf->SetTitle('Payslip');
$pdf->SetSubject('Payslip');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(10, 10, 10);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(20);
$pdf->SetAutoPageBreak(TRUE, 15);

// Page start
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 12);
$pdf->Image($GLOBALS['logoPath'], 15, 10, 30);

// Company header
$y = 40;
$pdf->SetFillColor(80, 184, 72);
$pdf->Rect(10, $y, 190, 0.8, "F");

$pdf->SetFont('dejavusans', 'B', 20);
$pdf->SetXY(35, $y-25);
$pdf->Cell(0, 10, strtoupper($companyInfo['name']), 0, 1, 'C');

$pdf->SetFont('dejavusans', 'B', 10);
$pdf->SetXY(15, $y-17);
$pdf->Cell(0, 10, strtoupper($employee['location_name']), 0, 1, 'C');

// Payslip title
$y += 5;
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->SetXY(10, $y);
$pdf->Cell(0, 7, strtoupper("monthly payslip"), "LBRT", 1, 'C');

$y += 7.5;
$pdf->SetFont('dejavusans', '', 9);
$pdf->SetXY(10, $y);
$pdf->Cell(0, 7, ucwords($monthFormatted), "LBRT", 1, 'C');

// ========== Employee Info Block ==========
$y += 7.5;
$Y = $y;
$pdf->Rect(10, $y, 190, 60);

$infoMap = [
    "STAFF ID CODE" => $employee['staff_no'],
    "NAME" => $employee['full_name'],
    "ROLE" => $employee['position'],
    "GRADE" => $employee['grade'],
    "TAX EXEMPTION" => $employee['tax_exempt'],
    "STATE" => $employee['state'],
    "DEPARTMENT" => $employee['branch'],
    "SENIORITY IN YEARS" => $employee['seniority'],
];
foreach ($infoMap as $label => $value) {
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->SetXY(13, $y);
    $pdf->Cell(40, 7, $label, "", 1, 'L');

    $pdf->SetFont('dejavusans', '', 9);
    $pdf->SetXY(55, $y);
    $pdf->Cell(40, 7, strtoupper($value), "", 1, 'L');

    $y += 7.5;
}

// Right side stats
$statsMap = [
    "NO. WORKING DAYS" => $rec['required_days'],
    "NO. MONTHLY HOURS" => ($rec['required_days'] * $employee['work_hours']),
    "NO. DAYS WORKED" => $rec['days_worked'],
    "MONTHLY HOURS WORKED" => ($rec['days_worked'] * $employee['work_hours']),
    "ABSENCE DAYS" => ($attenInfo['unpaid_leave_days'] + $attenInfo['paid_leave_days'] + $attenInfo['sick_days'] + $attenInfo['no_show_days']),
    "DAYS NOT HIRED" => $attenInfo['not_hired_days']
];
foreach ($statsMap as $label => $value) {
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->SetXY(130, $Y);
    $pdf->Cell(40, 7, $label, "", 1, 'L');
    $pdf->SetXY(180, $Y);
    $pdf->Cell(40, 7, strtoupper($value), "", 1, 'L');
    $Y += 7.5;
}

// ========== Salary Breakdown ==========
$y += 1;
$Y = $y;
$pdf->Rect(10, $y, 190, 150);

$gross_salary = $rec['base_salary'];

$items = [
    ["Allowance", $rec['allowance'], "+"],
    ["Bonus", $rec['bonus'], "+"],
    ["Extra Hours", $rec['extra_hours'], "+"],
    ["Commission", $rec['commission'], "+"],
];
foreach ($items as [$label, $amount, $sign]) {
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->SetXY(13, $y);
    $pdf->Cell(40, 7, $label, "", 1, 'L');
    $pdf->SetXY(110, $y);
    $pdf->Cell(40, 7, $sign, "", 1, 'L');
    $pdf->SetXY(120, $y);
    $pdf->Cell(40, 7, formatMoney($amount), "", 1, 'L');
    $gross_salary += ($sign === "+") ? $amount : 0;
    $y += 7.5;
}

// Gross salary
$pdf->SetFillColor(200, 200, 200);
$pdf->Rect(13, $y-0.7, 186, 8, "F");
$pdf->SetFont('dejavusans', 'B', 9);
$pdf->SetXY(13, $y);
$pdf->Cell(40, 7, "Gross salary", "", 1, 'L');
$pdf->SetXY(110, $y);
$pdf->Cell(40, 7, " = ", "", 1, 'L');
$pdf->SetXY(120, $y);
$pdf->Cell(40, 7, formatMoney($gross_salary), "", 1, 'L');

$y += 7.5;

// Deductions
$total_deductions = $rec['advance'] + $rec['loan'] + $rec['deductions'] + 
    $rec['unpaid_days'] + $rec['unpaid_hours'] + $rec['tax'];

$deductions = [
    ["Taxes", $rec['tax']],
    ["Salary advance", $rec['advance']],
    ["Loan", $rec['loan']],
    ["Other Deductions", $rec['deductions']],
    ["Unpaid Days", $rec['unpaid_days']],
    ["Unpaid Hours", $rec['unpaid_hours']],
];
foreach ($deductions as [$label, $amount]) {
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->SetXY(13, $y);
    $pdf->Cell(40, 7, $label, "", 1, 'L');
    $pdf->SetXY(110, $y);
    $pdf->Cell(40, 7, "-", "", 1, 'L');
    $pdf->SetXY(120, $y);
    $pdf->Cell(40, 7, formatMoney($amount), "", 1, 'L');
    $y += 7.5;
}

// Total deductions
$pdf->SetFillColor(200, 200, 200);
$pdf->Rect(13, $y-0.7, 186, 8, "F");
$pdf->SetFont('dejavusans', 'B', 9);
$pdf->SetXY(13, $y);
$pdf->Cell(40, 7, "Total deductions", "", 1, 'L');
$pdf->SetXY(110, $y);
$pdf->Cell(40, 7, " = ", "", 1, 'L');
$pdf->SetXY(120, $y);
$pdf->Cell(40, 7, formatMoney($total_deductions), "", 1, 'L');

$y += 7.5;

// Net salary
$net_salary = $gross_salary - $total_deductions;
$pdf->SetFillColor(200, 200, 200);
$pdf->Rect(13, $y-0.7, 186, 8, "F");
$pdf->SetFont('dejavusans', 'B', 9);
$pdf->SetXY(13, $y);
$pdf->Cell(40, 7, "Net salary", "", 1, 'L');
$pdf->SetXY(110, $y);
$pdf->Cell(40, 7, " = ", "", 1, 'L');
$pdf->SetXY(120, $y);
$pdf->Cell(40, 7, formatMoney($net_salary), "", 1, 'L');

$y += 10;

// Total to be paid
$pdf->SetFillColor(200, 200, 200);
$pdf->Rect(13, $y-0.7, 186, 8, "F");
$pdf->SetFont('dejavusans', 'B', 9);
$pdf->SetXY(13, $y);
$pdf->Cell(40, 7, "Total to be paid", "", 1, 'L');
$pdf->SetXY(110, $y);
$pdf->Cell(40, 7, " = ", "", 1, 'L');
$pdf->SetXY(120, $y);
$pdf->Cell(40, 7, formatMoney($net_salary), "", 1, 'L');

// Output the PDF
$pdf->Output("Payslip.pdf", 'I');
?>
