<?php
/**
 * Receipt PDF Export — TCPDF
 * Called from pdf.php?print=receipt&id={payment_id}
 */

$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($payment_id <= 0)
    die('Invalid payment ID');

if (!isset($conn)) {
    global $conn;
}

// ── Fetch payment + invoice + tenant ────────────────────────────
$stmt = $conn->prepare(
    "SELECT p.*, 
            i.reference_number AS invoice_ref, i.invoice_date,
            t.full_name AS tenant_name, t.phone AS tenant_phone, t.email AS tenant_email,
            u.unit_number,
            pr.name AS property_name,
            creator.name AS creator_name
     FROM payments_received p
     LEFT JOIN invoices i ON p.invoice_id = i.id
     LEFT JOIN leases l ON i.lease_id = l.id
     LEFT JOIN tenants t ON l.tenant_id = t.id
     LEFT JOIN units u ON l.unit_id = u.id
     LEFT JOIN properties pr ON u.property_id = pr.id
     LEFT JOIN users creator ON creator.id = p.created_by
     WHERE p.id = ?"
);
$stmt->bind_param('i', $payment_id);
$stmt->execute();
$pmt = $stmt->get_result()->fetch_assoc();
if (!$pmt)
    die('Payment not found');
if (!require_same_tenant_or_super($pmt['org_id']))
    die('Access denied');

$invoice_id = (int) $pmt['invoice_id'];

// ── Fetch invoice items ──────────────────────────────────────────
$items = [];
if ($invoice_id > 0) {
    $item_stmt = $conn->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
    $item_stmt->bind_param('i', $invoice_id);
    $item_stmt->execute();
    $item_res = $item_stmt->get_result();
    while ($it = $item_res->fetch_assoc()) {
        $items[] = $it;
    }
}

// ── Org settings ────────────────────────────────────────────────
$orgId = (int) ($pmt['org_id'] ?? 0);
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
$pdf->SetTitle('Receipt ' . ($pmt['receipt_number'] ?? ''));
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

// Receipt Number (Top Right)
$pdf->SetXY(110, 15);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(30, 30, 30);
$pdf->Cell(85, 7, 'NO. ' . ($pmt['receipt_number'] ?? 'N/A'), 0, 1, 'R');

// "OFFICIAL RECEIPT" Title (Large, Bold, Left)
$pdf->SetY(50);
$pdf->SetFont('helvetica', 'B', 24);
$pdf->SetTextColor(30, 30, 30);
$pdf->Cell(0, 15, 'RECEIPT', 0, 1, 'L');

// Status Label (Top Right, Conditional)
// $pdf->SetXY(110, 55);
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor(40, 167, 69); // Green for Receipt
// $pdf->Cell(0, 10, 'PAID', 0, 1, 'R');
$pdf->SetTextColor(30, 30, 30); // Reset

// Date (Below title)
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(15, 6, 'Date:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 6, date('d F, Y', strtotime($pmt['received_date'] ?? 'now')), 0, 1, 'L');

$pdf->Ln(5);

// ── Billed to / From (2-column layout) ───────────────────────────
$yGrid = $pdf->GetY();
$colW = $pageW / 2;

// Left: Received From
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell($colW, 6, 'Received From:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell($colW, 5, $pmt['tenant_name'] ?? 'N/A', 0, 1, 'L');
$pdf->MultiCell($colW, 4.5, ($pmt['property_name'] ?? '') . "\nUnit: " . ($pmt['unit_number'] ?? 'N/A'), 0, 'L');

// Right: Payment Details
$pdf->SetXY(15 + $colW, $yGrid);
$pdf->SetTextColor(30, 30, 30);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell($colW, 6, 'Payment Details:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetX(15 + $colW);
$pdf->Cell($colW, 5, 'Method: ' . strtoupper($pmt['payment_method'] ?? 'N/A'), 0, 1, 'L');
$pdf->SetX(15 + $colW);
$pdf->Cell($colW, 5, 'Invoice Ref: ' . ($pmt['invoice_ref'] ?? 'N/A'), 0, 1, 'L');
$pdf->SetX(15 + $colW);
$pdf->Cell($colW, 5, 'Organization: ' . $orgName, 0, 1, 'L');

$pdf->Ln(10);

// ── Items Table (Minimalist) ─────────────────────────────────────
$pdf->SetFillColor(245, 245, 245);
$pdf->SetTextColor(30, 30, 30);
$pdf->SetFont('helvetica', 'B', 9);

$tw = $pageW;
$pdf->Cell($tw * 0.5, 8, 'Item', 'B', 0, 'L', true);
$pdf->Cell($tw * 0.15, 8, 'Quantity', 'B', 0, 'R', true);
$pdf->Cell($tw * 0.15, 8, 'Price', 'B', 0, 'R', true);
$pdf->Cell($tw * 0.2, 8, 'Amount', 'B', 1, 'R', true);

$pdf->SetFont('helvetica', '', 9);

foreach ($items as $it) {
    $lineTotal = (float) $it['line_total'];

    $pdf->Cell($tw * 0.5, 9, $it['description'], 'B', 0, 'L');
    $pdf->Cell($tw * 0.15, 9, number_format($it['qty'], 2), 'B', 0, 'R');
    $pdf->Cell($tw * 0.15, 9, '$' . number_format($it['unit_price'], 2), 'B', 0, 'R');
    $pdf->Cell($tw * 0.2, 9, '$' . number_format($lineTotal, 2), 'B', 1, 'R');
}

$pdf->Ln(8);

// Totals area
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell($tw * 0.8, 8, 'Total', 0, 0, 'R');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell($tw * 0.2, 8, '$' . number_format($pmt['amount_paid'], 2), 0, 1, 'R');
$pdf->SetTextColor(30, 30, 30);

if (!empty($pmt['notes'])) {
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell($pageW, 6, 'Notes:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(80, 80, 80);
    $pdf->MultiCell($pageW, 10, $pmt['notes'], 0, 'L');
}

// Signature Section
$pdf->SetY(240);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(30, 30, 30);
$pdf->Cell(60, 7, $pmt['creator_name'] ?? 'Authorized Signatory', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(60, 0, '', 'B', 1, 'L');
// $pdf->Cell(60, 5, 'Issued By', 0, 1, 'L');

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

$filename = 'Receipt_' . $pmt['receipt_number'] . '.pdf';
$pdf->Output($filename, 'I');
exit;
