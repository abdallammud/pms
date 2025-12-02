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
        'staff_no' , 'full_name' ,  'gross_salary', 'earnings' , 'deductions' , 'tax' , 'net_salary' 
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
    pd.remarks,
    pd.out_of_contract,
    (pd.base_salary + (pd.allowance + pd.bonus + pd.commission) - (pd.loan + pd.advance + pd.deductions) - pd.tax) AS net_salary,
    COALESCE(e.payment_bank, pd.bank_name) AS bank_name,
    COALESCE(e.payment_account, pd.bank_number) AS bank_number,
    pd.pay_date,
    pd.paid_by,
    pd.paid_through,
    pd.status
FROM payroll_details pd
LEFT JOIN employees e ON pd.emp_id = e.employee_id
WHERE pd.payroll_id = ? AND pd.month LIKE ?
ORDER BY COALESCE(e.full_name, pd.full_name) ASC
";

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
$pdf->Cell(130, 7, strtoupper($monthName . " payroll report"), 0, 0, 'R');

$pdf->SetFont('aefurat','',10);
$pdf->SetXY(158, 36);
$pdf->Cell(130, 7, "Print date " . date("F d Y h:i:s A"), 0, 0, 'R');

$pdf->SetDrawColor($p0, $p1, $p2);
$pdf->Line(10, 45, 287, 45);
$pdf->Ln(15);


$yy = $pdf->getY();

$pdf->SetXY(10, $yy);
// Table header
$pdf->SetFont('aefurat','B',10);
$pdf->SetFillColor($p0, $p1, $p2);
$pdf->SetTextColor(255,255,255);
$pdf->SetDrawColor(0,0,0);


$pageWidth = 278;  // width for A4 landscape content area
$fullNameWidth = 70;
$remainingColumns = array_diff($showColumns, ['full_name']);
$remainingWidth = $pageWidth - $fullNameWidth;
$dynamicWidth = count($remainingColumns) > 0 ? floor($remainingWidth / count($remainingColumns)) : $remainingWidth;

foreach ($showColumns as $col) {
    // echo $col;
    // Set fill color if out of contract
    $fillColor = false;
    if ($col == 'out_of_contract') {
        $pdf->SetFillColor(23, 55, 103);
        $fillColor = true;
    }
    $width = ($col == 'full_name') ? $fullNameWidth : $dynamicWidth;
    $label = isset($allColumns[$col]) ? $allColumns[$col] : $col;
    $pdf->Cell($width, 8, $label, 1, 0, 'L', $fillColor);
}
$pdf->Ln();

$pdf->SetFont('aefurat','',9);
$pdf->SetTextColor(0,0,0);

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
    $pdf->Ln();
}

$y = $pdf->getY()+10;

// Add mini table for payroll work flow
$workflow = json_decode($payrollInfo['workflow'] ?? '[]', true) ?: [];
$yy = $y;
$x = 10;
if(count($workflow) > 0) {
    $reversed_workflow = array_reverse($workflow);
    foreach ($reversed_workflow as $step) {
        $workflowStatus = $step['status'] ?? 'Unknown';
        $pdf->SetFont('aefurat','B',12);
        $pdf->SetXY($x, $yy);
        $pdf->Cell(30, 7, "$workflowStatus by", 0, 0, 'L');
        $pdf->SetFont('aefurat','B',10);
        $action_text = $step['action'] ?? '';
        $action_parts = explode(' by ', $action_text, 2);
        $roleName = $GLOBALS['userClass']->get_roleName($step['user_id']);
        $user_name = isset($action_parts[1]) ? htmlspecialchars($action_parts[1]) : '';
        $signature = $GLOBALS['userClass']->get_signature($step['user_id']);
        if ($user_name) {
            $pdf->SetXY($x, $yy+7);
            $pdf->Cell(60, 7, "$user_name ($roleName)", 0, 0, 'L');

            $pdf->SetXY($x, $yy+12);
            $pdf->Cell(60, 7, "On ". (isset($step['date']) ? date("M d, Y h:i A", strtotime($step['date'])) : ''), 0, 0, 'L');
        }
        $pdf->SetDrawColor(0,0,0);
        // echo "assets/docs/signature/".$signature;
        // if(file_exists("./assets/docs/signature/".$signature)) {
            $pdf->Image("assets/docs/signature/".$signature, $x, $yy+17, 50);
        // }
        $pdf->Rect($x, $yy+27, 50, 0.1, "F");
        $x += 60;
    }
}


$pdf->Output("$monthName payroll report.pdf","I");
?>
