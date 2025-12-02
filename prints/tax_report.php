<?php
// require('fpdf/fpdf.php');
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

    public function AliasNbPages($alias = '{nb}') {
        // This is a placeholder and should be replaced with the modern approach.
        // It simply sets a property, which is how the old library handled it.
        // Modern TCPDF handles this placeholder internally.
        $this->alias_nb_pages = $alias;
    }
}

$month = $_GET['month'] ?? '';
$state = $_GET['state'] ?? '';
$department = $_GET['department'] ?? '';
$location = $_GET['location'] ?? '';
$salary = $_GET['salary'] ?? '';
$salary_up = $_GET['salary_up'] ?? '';

$where = "WHERE pd.payroll_id = ?";

if (!empty($month)) {
    $where .= " AND pd.month LIKE ?";
    $monthLike = "%$month%";
} else {
    $monthLike = '%';
}

if (!empty($state)) {
    $where .= " AND e.state = '" . escapeStr($state) . "'";
}
if (!empty($department)) {
    $where .= " AND e.department = '" . escapeStr($department) . "'";
}
if (!empty($location)) {
    $where .= " AND e.location = '" . escapeStr($location) . "'";
}
if (!empty($salary)) {
    $where .= " AND pd.base_salary >= " . floatval($salary);
}
if (!empty($salary_up)) {
    $where .= " AND pd.base_salary <= " . floatval($salary_up);
}


$payroll_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$allColumns = [
    'employee_id' => 'Employee ID', 'staff_no' => 'Staff No', 'full_name' => 'Full Name', 'email' => 'Email',
    'contract_type' => 'Contract Type', 'job_title' => 'Job Title', 'month' => 'Payroll Month',
    'required_days' => 'Required Days', 'days_worked' => 'Days Worked', 'gross_salary' => 'Gross Salary',
    'net_salary' => 'Net Salary', 'earnings' => 'Earnings', 'deductions' => 'Deductions', 'tax' => 'Tax',
    'unpaid_days' => 'Unpaid Days', 'unpaid_hours' => 'Unpaid Hours', 'status' => 'Status',
    'bank_name' => 'Bank Name', 'bank_number' => 'Bank Number', 'pay_date' => 'Pay Date',
    'paid_by' => 'Paid By', 'paid_through' => 'Paid Through', 'action' => 'Action'
];
$showColumns = get_columns('payroll_pdf', 'show_columns');
if(isset($_GET['report'])) {
    $get_payroll = "SELECT `id` FROM `payroll` WHERE `month` = ?";
    $stmt = $GLOBALS['conn']->prepare($get_payroll);
    $stmt->bind_param('s', $_GET['month']);
    $stmt->execute();
    $result = $stmt->get_result();
    $payroll_id = $result->fetch_assoc()['id'];

    $showColumns = [
        'staff_no' , 'full_name' , 'net_salary' , 'tax' 
    ];
}

// Create new PDF document
$pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false);





$payrollInfo = get_data('payroll', ['id' => $payroll_id]);
$payrollInfo = $payrollInfo ? $payrollInfo[0] : ['month' => '', 'ref' => '', 'ref_name' => '', 'added_date' => '', 'status' => ''];
$payrollInfo['month'] = explode(",", $payrollInfo['month']);

// You used a hard-coded month in your snippet; keep the same default here.
// If you want the month to come from $payrollInfo, replace this line accordingly.
$month = $_GET['month'] ?? date('Y-m');
try {
    $monthName = (new DateTime($month))->format('F Y');
} catch (Exception $e) {
    $monthName = $month;
}



// --------------------------
// Fetch payroll data (JOIN employees)
// --------------------------
$monthLike = ($month !== '') ? $month : '%';

$sql = "SELECT
    pd.id,
    pd.payroll_id,
    pd.emp_id,
    COALESCE(e.employee_id, pd.emp_id) AS employee_id,
    COALESCE(e.staff_no, pd.staff_no) AS staff_no,
    COALESCE(e.full_name, pd.full_name) AS full_name,
    COALESCE(e.email, pd.email) AS email,
    COALESCE(e.contract_type, pd.contract_type) AS contract_type,
    COALESCE(e.designation, e.position, pd.job_title) AS job_title,
    pd.month,
    pd.required_days,
    pd.days_worked,
    pd.base_salary AS gross_salary,
    (pd.allowance + pd.bonus + pd.commission) AS earnings,
    (pd.loan + pd.advance + pd.deductions) AS deductions,
    pd.tax,
    (pd.base_salary + (pd.allowance + pd.bonus + pd.commission) - (pd.loan + pd.advance + pd.deductions) - pd.tax) AS net_salary,
    COALESCE(e.payment_bank, pd.bank_name) AS bank_name,
    COALESCE(e.payment_account, pd.bank_number) AS bank_number,
    pd.pay_date,
    pd.paid_by,
    pd.paid_through,
    pd.status
FROM payroll_details pd
LEFT JOIN employees e ON pd.emp_id = e.employee_id
$where
ORDER BY COALESCE(e.full_name, pd.full_name) ASC";

// prepare and execute
$stmt = $GLOBALS['conn']->prepare($sql);
if (!$stmt) {
    // If prepare fails, show a helpful error in plain text (PDF can't be generated reliably)
    echo "Database error: " . $GLOBALS['conn']->error;
    exit;
}
$stmt->bind_param('is', $payroll_id, $monthLike);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}
// var_dump($data);
$stmt->close();

$companyInfo = get_data('company', ['id' => 1])[0];

// --------------------------
// FPDF output (kept your layout)
// --------------------------
class PDF extends MYPDF {
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('aefurat','I',8);
        // $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF('L','mm','A4');
$pdf->AliasNbPages();
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

$primary_color = return_setting('primary_color');
$secondary_color = return_setting('secondary_color');

$primary_color = explode(",", hexToRgb($primary_color));
$secondary_color = explode(",", hexToRgb($secondary_color));

// ensure integer color values
$p0 = intval($primary_color[0] ?? 0);
$p1 = intval($primary_color[1] ?? 0);
$p2 = intval($primary_color[2] ?? 0);

$logo = get_logo_name_from_url();
$pdf->Image($GLOBALS['logoPath'], 10, 10, 30);

// Company header
$pdf->SetFont('aefurat','B',16);
$pdf->SetTextColor($p0, $p1, $p2);
$pdf->SetXY(158, 10);
$pdf->Cell(130, 7, strtoupper($companyInfo['name']), 0, 0, 'R');

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('aefurat','',10);
$pdf->SetXY(158, 18);
$pdf->Cell(130, 7, $companyInfo['contact_phone'], 0, 0, 'R');
$pdf->SetXY(158, 23);
$pdf->Cell(130, 7, $companyInfo['contact_email'], 0, 0, 'R');

$pdf->SetFont('aefurat','B',12);
$pdf->SetXY(158, 30);
$pdf->Cell(130, 7, strtoupper($monthName . " Tax report"), 0, 0, 'R');

$pdf->SetFont('aefurat','',10);
$pdf->SetXY(158, 36);
$pdf->Cell(130, 7, "Print date " . date("F d Y h:i:s A"), 0, 0, 'R');

$pdf->SetDrawColor($p0, $p1, $p2);
$pdf->Line(10, 45, 287, 45);
$pdf->Ln(15);

$yy = 45;
$pdf->SetXY(10, $yy + 7);
// Table header
$pdf->SetFont('aefurat','B',10);
$pdf->SetFillColor($p0, $p1, $p2);
$pdf->SetTextColor(255,255,255);
$pdf->SetDrawColor(0,0,0);


$pageWidth = 278;  // width for A4 landscape content area
$fullNameWidth = 140;
$remainingColumns = array_diff($showColumns, ['full_name']);
$remainingWidth = $pageWidth - $fullNameWidth;
$dynamicWidth = count($remainingColumns) > 0 ? floor($remainingWidth / count($remainingColumns)) : $remainingWidth;

foreach ($showColumns as $col) {
    // echo $col;
    $width = ($col == 'full_name') ? $fullNameWidth : $dynamicWidth;
    $label = isset($allColumns[$col]) ? $allColumns[$col] : $col;
    $pdf->Cell($width, 8, $label, 1, 0, 'L', true);
}
$pdf->Ln();

$pdf->SetFont('aefurat','',9);
$pdf->SetTextColor(0,0,0);

$total_salary = $total_tax = 0;
// Table Data
foreach ($data as $row) {
    foreach ($showColumns as $col) {
        $width = ($col == 'full_name') ? $fullNameWidth : $dynamicWidth;
        $moneyColumns = ['gross_salary', 'net_salary', 'earnings', 'deductions', 'tax'];
        if(in_array($col, $moneyColumns)) {
            $val = isset($row[$col]) ? $row[$col] : 0;
            $pdf->Cell($width, 7, function_exists('formatMoney') ? formatMoney($val) : number_format((float)$val,2), 1, 0, 'L');
        } else {
            $text = isset($row[$col]) ? $row[$col] : '-';
            if ($col == 'full_name') $text = ucwords(strtolower($text));
            // ensure no binary/control chars break PDF
            $text = preg_replace('/[\x00-\x1F\x7F]/', '', (string)$text);
            $pdf->Cell($width, 7, $text, 1, 0, 'L');
        }
    }

    $total_tax += $row['tax'];
    $total_salary += $row['net_salary'];
    $pdf->Ln();
}

$y = $pdf->GetY();
$pdf->setXY(150, $y);
$pdf->SetFont('aefurat','B',10);
$pdf->SetFillColor($p0, $p1, $p2);
// $pdf->SetTextColor(255,255,255);
$pdf->SetDrawColor(0,0,0);

// $pdf->Cell($fullNameWidth, 8, 'Total', 1, 0, 'L', true);
$pdf->Cell($dynamicWidth, 8, "Total", 1, 0, 'L');
$pdf->Cell($dynamicWidth, 8, function_exists('formatMoney') ? formatMoney($total_salary) : number_format((float)$total_salary,2), 1, 0, 'L');
$pdf->Cell($dynamicWidth, 8, function_exists('formatMoney') ? formatMoney($total_tax) : number_format((float)$total_tax,2), 1, 0, 'L');
$pdf->Ln();



$pdf->Output("$monthName Tax report.pdf","I");
?>
