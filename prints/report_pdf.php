<?php
/**
 * Report PDF Export — all 6 report types via TCPDF
 * Called from pdf.php?print={type}&startDate=...&endDate=...
 */

$reportType = $_GET['print']      ?? '';
$startDate  = $_GET['startDate']  ?? date('Y-m-01');
$endDate    = $_GET['endDate']    ?? date('Y-m-d');
$propertyId = $_GET['property_id'] ?? null;
$tenantStatus = $_GET['tenant_status'] ?? 'all';

if (!isset($conn)) { global $conn; }

// ── Org settings ───────────────────────────────────────────────
$orgId = function_exists('current_org_id') ? (int) current_org_id() : 0;
$orgClause = $orgId > 0 ? "AND org_id = $orgId" : '';

$orgName = ''; $orgPhone = ''; $orgCity = '';
$logoLocalPath = 'public/images/logo.png';
$brandHex = '1D3354';

$sRes = $conn->query("SELECT setting_key, setting_value FROM system_settings WHERE 1=1 $orgClause");
while ($sRow = $sRes->fetch_assoc()) {
    switch ($sRow['setting_key']) {
        case 'org_name':             $orgName       = $sRow['setting_value']; break;
        case 'org_phone':            $orgPhone      = $sRow['setting_value']; break;
        case 'org_city':             $orgCity       = $sRow['setting_value']; break;
        case 'doc_logo_path':        if (!empty($sRow['setting_value'])) $logoLocalPath = ltrim($sRow['setting_value'], './'); break;
        case 'logo_path':            if (!empty($sRow['setting_value']) && $logoLocalPath === 'public/images/logo.png') $logoLocalPath = ltrim($sRow['setting_value'], './'); break;
        case 'brand_primary_color':  $brandHex = strtoupper(ltrim($sRow['setting_value'], '#')); break;
    }
}
$logoPath = './' . $logoLocalPath;

// ── Report data ────────────────────────────────────────────────
require_once('./app/report_controller.php');
$report = new ReportController();
$data   = [];

$reportTitles = [
    'rent_collection'    => 'Rent Collection Report',
    'unit_occupancy'     => 'Unit Occupancy Report',
    'tenant_report'      => 'Tenant Report',
    'outstanding_balance'=> 'Outstanding Balance Report',
    'income_expense'     => 'Income vs Expense Report',
    'maintenance_report' => 'Maintenance Report',
    'maintenance_expense'=> 'Maintenance Expense Report',
];
$reportTitle = $reportTitles[$reportType] ?? 'Report';

$params = ['startDate' => $startDate, 'endDate' => $endDate, 'property_id' => $propertyId, 'tenant_status' => $tenantStatus];
switch ($reportType) {
    case 'rent_collection':     $data = $report->getRentCollectionData($params);     break;
    case 'unit_occupancy':      $data = $report->getUnitOccupancyData($params);      break;
    case 'tenant_report':       $data = $report->getTenantReportData($params);       break;
    case 'outstanding_balance': $data = $report->getOutstandingBalanceData($params); break;
    case 'income_expense':      $data = $report->getIncomeExpenseData($params);      break;
    case 'maintenance_report':  $data = $report->getMaintenanceReportData($params);  break;
    case 'maintenance_expense': $data = $report->getMaintenanceExpenseData($params); break;
}

// ── Column definitions per report type ────────────────────────
$columns = [];
switch ($reportType) {
    case 'rent_collection':
        $columns = [
            ['label' => 'Date',        'key' => 'received_date',  'fmt' => 'date',     'w' => 28],
            ['label' => 'Receipt #',   'key' => 'receipt_number', 'fmt' => 'text',     'w' => 32],
            ['label' => 'Tenant',      'key' => 'tenant_name',    'fmt' => 'text',     'w' => 45],
            ['label' => 'Property',    'key' => 'property_name',  'fmt' => 'text',     'w' => 45],
            ['label' => 'Unit',        'key' => 'unit_number',    'fmt' => 'text',     'w' => 18],
            ['label' => 'Amount',      'key' => 'amount_paid',    'fmt' => 'currency', 'w' => 25, 'total' => true],
        ]; break;
    case 'unit_occupancy':
        $columns = [
            ['label' => 'Property',    'key' => 'property_name',  'fmt' => 'text',     'w' => 50],
            ['label' => 'Unit #',      'key' => 'unit_number',    'fmt' => 'text',     'w' => 22],
            ['label' => 'Type',        'key' => 'unit_type',      'fmt' => 'text',     'w' => 30],
            ['label' => 'Status',      'key' => 'status',         'fmt' => 'ucfirst',  'w' => 22],
            ['label' => 'Tenant',      'key' => 'tenant_name',    'fmt' => 'text',     'w' => 45],
        ]; break;
    case 'tenant_report':
        $columns = [
            ['label' => 'Name',        'key' => 'full_name',      'fmt' => 'text',     'w' => 50],
            ['label' => 'Phone',       'key' => 'phone',          'fmt' => 'text',     'w' => 32],
            ['label' => 'Email',       'key' => 'email',          'fmt' => 'text',     'w' => 50],
            ['label' => 'Status',      'key' => 'status',         'fmt' => 'ucfirst',  'w' => 22],
            ['label' => 'Joined',      'key' => 'created_at',     'fmt' => 'date',     'w' => 25],
        ]; break;
    case 'outstanding_balance':
        $columns = [
            ['label' => 'Invoice #',   'key' => 'invoice_number', 'fmt' => 'text',     'w' => 30],
            ['label' => 'Due Date',    'key' => 'due_date',       'fmt' => 'date',     'w' => 25],
            ['label' => 'Tenant',      'key' => 'tenant_name',    'fmt' => 'text',     'w' => 45],
            ['label' => 'Property',    'key' => 'property_name',  'fmt' => 'text',     'w' => 40],
            ['label' => 'Unit',        'key' => 'unit_number',    'fmt' => 'text',     'w' => 18],
            ['label' => 'Balance',     'key' => 'amount',         'fmt' => 'currency', 'w' => 25, 'total' => true],
            ['label' => 'Status',      'key' => 'status',         'fmt' => 'ucfirst',  'w' => 20],
        ]; break;
    case 'income_expense':
        $columns = [
            ['label' => 'Date',        'key' => 'trans_date',     'fmt' => 'date',     'w' => 28],
            ['label' => 'Type',        'key' => 'type',           'fmt' => 'text',     'w' => 22],
            ['label' => 'Property',    'key' => 'property_name',  'fmt' => 'text',     'w' => 45],
            ['label' => 'Details',     'key' => 'details',        'fmt' => 'text',     'w' => 60],
            ['label' => 'Amount',      'key' => 'amount',         'fmt' => 'currency', 'w' => 30, 'total' => true],
        ]; break;
    case 'maintenance_report':
        $columns = [
            ['label' => 'Ref #',       'key' => 'reference_number','fmt' => 'text',    'w' => 28],
            ['label' => 'Date',        'key' => 'created_at',     'fmt' => 'date',     'w' => 25],
            ['label' => 'Property',    'key' => 'property_name',  'fmt' => 'text',     'w' => 40],
            ['label' => 'Unit',        'key' => 'unit_number',    'fmt' => 'text',     'w' => 20],
            ['label' => 'Priority',    'key' => 'priority',       'fmt' => 'ucfirst',  'w' => 20],
            ['label' => 'Status',      'key' => 'status',         'fmt' => 'ucfirst',  'w' => 22],
            ['label' => 'Description', 'key' => 'description',    'fmt' => 'text',     'w' => 50],
        ]; break;
    case 'maintenance_expense':
        $columns = [
            ['label' => 'Date',        'key' => 'expense_date',   'fmt' => 'date',     'w' => 28],
            ['label' => 'Ref #',       'key' => 'reference_number','fmt' => 'text',    'w' => 28],
            ['label' => 'Property',    'key' => 'property_name',  'fmt' => 'text',     'w' => 40],
            ['label' => 'Category',    'key' => 'category',       'fmt' => 'text',     'w' => 30],
            ['label' => 'Amount',      'key' => 'amount',         'fmt' => 'currency', 'w' => 25, 'total' => true],
            ['label' => 'Description', 'key' => 'description',    'fmt' => 'text',     'w' => 45],
        ]; break;
}

// ── TCPDF setup ────────────────────────────────────────────────
if (ob_get_length()) ob_end_clean();

$brandR = hexdec(substr($brandHex, 0, 2));
$brandG = hexdec(substr($brandHex, 2, 2));
$brandB = hexdec(substr($brandHex, 4, 2));

$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('PMS');
$pdf->SetAuthor($orgName);
$pdf->SetTitle($reportTitle);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 12);
$pdf->AddPage();

// ── Header block ───────────────────────────────────────────────
$headerY = $pdf->GetY();

// Logo (left)
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 10, $headerY, 40, 20, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
}

// Org name & report title (right side)
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor($brandR, $brandG, $brandB);
$pdf->SetXY(55, $headerY);
$pdf->Cell(220, 8, $reportTitle, 0, 1, 'R');

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->SetX(55);
$pdf->Cell(220, 5, $orgName . ($orgCity ? ' — ' . $orgCity : '') . ($orgPhone ? ' | ' . $orgPhone : ''), 0, 1, 'R');

$displayStart = date('M d, Y', strtotime($startDate));
$displayEnd   = date('M d, Y', strtotime($endDate));
$pdf->SetX(55);
$pdf->Cell(220, 5, 'Period: ' . $displayStart . ' – ' . $displayEnd, 0, 1, 'R');
$pdf->SetY(max($pdf->GetY(), $headerY + 22));

// Divider
$pdf->SetDrawColor($brandR, $brandG, $brandB);
$pdf->SetLineWidth(0.5);
$pdf->Line(10, $pdf->GetY() + 1, 287, $pdf->GetY() + 1);
$pdf->Ln(4);

// ── Table ──────────────────────────────────────────────────────
$totalW = array_sum(array_column($columns, 'w'));
$scaleX = 277 / $totalW; // scale columns to fit A4 landscape

// Header row
$pdf->SetFillColor($brandR, $brandG, $brandB);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetLineWidth(0);

foreach ($columns as $col) {
    $pdf->Cell($col['w'] * $scaleX, 8, $col['label'], 0, 0, 'C', true);
}
$pdf->Ln();

// Data rows
$pdf->SetTextColor(30, 30, 30);
$pdf->SetFont('helvetica', '', 7.5);
$totals = [];
$rowNum = 0;

foreach ($data as $row) {
    $rowNum++;
    $fill = ($rowNum % 2 === 0);
    if ($fill) {
        $pdf->SetFillColor(245, 247, 250);
    }

    foreach ($columns as $col) {
        $val = $row[$col['key']] ?? '';
        switch ($col['fmt']) {
            case 'date':     $val = $val ? date('M d, Y', strtotime($val)) : ''; break;
            case 'currency': $val = '$' . number_format((float)$val, 2); break;
            case 'ucfirst':  $val = ucfirst(str_replace('_', ' ', $val)); break;
        }
        if (!empty($col['total'])) {
            $totals[$col['key']] = ($totals[$col['key']] ?? 0) + (float)($row[$col['key']] ?? 0);
        }
        $pdf->Cell($col['w'] * $scaleX, 6, $val, 0, 0, 'L', $fill);
    }
    $pdf->Ln();

    // Page break guard
    if ($pdf->GetY() > 185) {
        $pdf->AddPage();
        // Re-draw header
        $pdf->SetFillColor($brandR, $brandG, $brandB);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 8);
        foreach ($columns as $col) {
            $pdf->Cell($col['w'] * $scaleX, 8, $col['label'], 0, 0, 'C', true);
        }
        $pdf->Ln();
        $pdf->SetTextColor(30, 30, 30);
        $pdf->SetFont('helvetica', '', 7.5);
    }
}

// Totals row
if (!empty($totals)) {
    $pdf->SetFillColor($brandR, $brandG, $brandB);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 8);
    $firstTotalCol = true;
    foreach ($columns as $col) {
        if (!empty($col['total'])) {
            $pdf->Cell($col['w'] * $scaleX, 7, '$' . number_format($totals[$col['key']], 2), 0, 0, 'R', true);
        } elseif ($firstTotalCol) {
            $pdf->Cell($col['w'] * $scaleX, 7, 'TOTAL', 0, 0, 'R', true);
            $firstTotalCol = false;
        } else {
            $pdf->Cell($col['w'] * $scaleX, 7, '', 0, 0, 'L', true);
        }
    }
    $pdf->Ln();
}

// Footer — page numbers
$pdf->SetTextColor(120, 120, 120);
$pdf->SetFont('helvetica', 'I', 7);
$pdf->SetY(-8);
$pdf->Cell(0, 5, 'Generated on ' . date('M d, Y H:i') . ' — Page ' . $pdf->getAliasNumPage() . ' of ' . $pdf->getAliasNbPages(), 0, 0, 'C');

$filename = str_replace(' ', '_', $reportTitle) . '_' . date('Y-m-d') . '.pdf';
$pdf->Output($filename, 'D');
exit;
