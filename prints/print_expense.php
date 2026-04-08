<?php
/**
 * Expense Voucher PDF Export — TCPDF
 * Called from pdf.php?print=expense&id={expense_id}
 */

$expense_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($expense_id <= 0)
    die('Invalid expense ID');

if (!isset($conn)) {
    global $conn;
}

// ── Fetch expense + property ────────────────────────────────────
$stmt = $conn->prepare(
    "SELECT e.*, p.name AS property_name, p.address AS property_address,
            creator.name AS creator_name
     FROM expenses e
     LEFT JOIN properties p ON e.property_id = p.id
     LEFT JOIN users creator ON creator.id = e.created_by
     WHERE e.id = ?"
);
$stmt->bind_param('i', $expense_id);
$stmt->execute();
$exp = $stmt->get_result()->fetch_assoc();
if (!$exp)
    die('Expense not found');
if (!require_same_tenant_or_super($exp['org_id']))
    die('Access denied');

// ── Org settings ────────────────────────────────────────────────
$orgId = (int) ($exp['org_id'] ?? 0);
$orgClause = $orgId > 0 ? "AND org_id = $orgId" : '';

$orgName = 'Kaad PMS';
$orgPhone = '';
$orgEmail = '';
$orgAddress = '';
$logoPath = './public/images/logo.png';
$brandHex = '1D3354';

$sRes = $conn->query("SELECT setting_key, setting_value FROM system_settings WHERE 1=1 $orgClause");
while ($sRow = $sRes->fetch_assoc()) {
    switch ($sRow['setting_key']) {
        case 'org_name':
            $orgName = $sRow['setting_value'];
            break;
        case 'org_phone':
            $orgPhone = $sRow['setting_value'];
            break;
        case 'org_email':
            $orgEmail = $sRow['setting_value'];
            break;
        case 'org_street1':
            $orgAddress = $sRow['setting_value'];
            break;
        case 'org_city':
            $orgAddress .= ($orgAddress ? ', ' : '') . $sRow['setting_value'];
            break;
        case 'doc_logo_path':
        case 'logo_path':
            if (!empty($sRow['setting_value']))
                $logoPath = './' . ltrim($sRow['setting_value'], './');
            break;
        case 'brand_primary_color':
            $brandHex = strtoupper(ltrim($sRow['setting_value'], '#'));
            break;
    }
}

$brandR = hexdec(substr($brandHex, 0, 2));
$brandG = hexdec(substr($brandHex, 2, 2));
$brandB = hexdec(substr($brandHex, 4, 2));

// ── TCPDF ─────────────────────────────────────────────────────────
if (ob_get_length())
    ob_end_clean();

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Kaad PMS');
$pdf->SetAuthor($orgName);
$pdf->SetTitle('Expense Voucher ' . ($exp['reference_number'] ?? ''));
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

$pageW = 180;

// ── HEADER ────────────────────────────────────────────────────────
// Logo
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 15, 15, 30, 0, '', '', 'T', false, 300);
}

// Voucher Number (Top Right)
$pdf->SetXY(110, 15);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(30, 30, 30);
$pdf->Cell(85, 7, 'NO. ' . ($exp['reference_number'] ?? 'N/A'), 0, 1, 'R');

// "EXPENSE VOUCHER" Title (Large, Bold, Left)
$pdf->SetY(50);
$pdf->SetFont('helvetica', 'B', 24);
$pdf->SetTextColor(30, 30, 30);
$pdf->Cell(0, 15, 'EXPENSE VOUCHER', 0, 1, 'L');

// Status Label (Top Right)
// $pdf->SetXY(110, 55);
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor(108, 117, 125); // Gray for Voucher
// $pdf->Cell(0, 10, 'ISSUED', 0, 1, 'R');
$pdf->SetTextColor(30, 30, 30); // Reset

// Date (Below title)
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(15, 6, 'Date:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 6, date('d F, Y', strtotime($exp['expense_date'] ?? 'now')), 0, 1, 'L');

$pdf->Ln(5);

// ── Billed to / From (2-column layout) ───────────────────────────
$yGrid = $pdf->GetY();
$colW = $pageW / 2;

// Left: Property/Recipient
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell($colW, 6, 'Recipient / Property:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell($colW, 5, $exp['expense_type'] == 'Property' ? $exp['property_name'] : 'Internal PMS', 0, 1, 'L');
if ($exp['expense_type'] == 'Property') {
    $pdf->MultiCell($colW, 4.5, $exp['property_address'], 0, 'L');
}

// Right: From
$pdf->SetXY(15 + $colW, $yGrid);
$pdf->SetTextColor(30, 30, 30);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell($colW, 6, 'Issued From:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetX(15 + $colW);
$pdf->Cell($colW, 5, $orgName, 0, 1, 'L');
$pdf->SetX(15 + $colW);
$pdf->MultiCell($colW, 4.5, $orgAddress, 0, 'L');
$pdf->SetX(15 + $colW);
$pdf->Cell($colW, 4.5, $orgEmail ?? '', 0, 1, 'L');

$pdf->Ln(10);

// ── Amount Box ───────────────────────────────────────────────────
$pdf->SetFillColor(240, 240, 240);
$pdf->SetTextColor(30, 30, 30);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell($pageW, 8, 'VOUCHER DETAILS', 0, 1, 'L', true);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell($pageW * 0.7, 8, 'Category: ' . ($exp['category'] ?? 'General'), 'B', 0, 'L');
$pdf->Cell($pageW * 0.3, 8, 'Type: ' . ($exp['expense_type'] ?? 'General'), 'B', 1, 'R');

$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell($pageW * 0.7, 10, 'AMOUNT PAID', 0, 0, 'R');
$pdf->SetTextColor($brandR, $brandG, $brandB);
$pdf->Cell($pageW * 0.3, 10, '$' . number_format($exp['amount'], 2), 0, 1, 'R');
$pdf->SetTextColor(30, 30, 30);

$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell($pageW, 6, 'Description:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(80, 80, 80);
$pdf->MultiCell($pageW, 20, $exp['description'], 0, 'L');

// Signature Section
$pdf->SetY(240);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(30, 30, 30);
$pdf->Cell(60, 7, $exp['creator_name'] ?? 'Authorized Signatory', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(60, 0, '', 'B', 1, 'L');
$pdf->Cell(60, 5, 'Prepared By', 0, 1, 'L');

$pdf->SetXY(135, 240);
$pdf->Cell(60, 7, '', 0, 1, 'L'); // Placeholder for approval
$pdf->SetXY(135, 247);
$pdf->Cell(60, 0, '', 'B', 1, 'L');
$pdf->SetX(135);
$pdf->Cell(60, 5, 'Approved By', 0, 1, 'L');

// ── CUSTOM FOOTER SHAPES ─────────────────────────────────────────
// Light Grey Wave
$pdf->SetFillColor(220, 220, 220);
$pdf->Polygon([
    0,
    297,
    140,
    297,
    140,
    280,
    100,
    260,
    60,
    275,
    0,
    260
], 'F');

// Dark Grey/Black Wave
$pdf->SetFillColor(50, 50, 50);
$pdf->Polygon([
    210,
    297,
    60,
    297,
    60,
    285,
    100,
    275,
    150,
    285,
    210,
    265
], 'F');

$filename = 'Expense_' . $exp['reference_number'] . '.pdf';
$pdf->Output($filename, 'i');
exit;
