<?php
/**
 * Invoice PDF Export — TCPDF
 * Called from pdf.php?print=invoice&id={invoice_id}
 */

$invoice_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($invoice_id <= 0)
    die('Invalid invoice ID');

if (!isset($conn)) {
    global $conn;
}

// ── Fetch invoice + items + payments ────────────────────────────
$stmt = $conn->prepare(
    "SELECT i.*,
            t.full_name AS tenant_name, t.phone AS tenant_phone, t.email AS tenant_email,
            u.unit_number,
            p.name AS property_name, p.address AS property_address, p.city AS property_city,
            ct.name AS charge_type_name,
            l.monthly_rent AS rent_amount, l.start_date AS lease_start, l.end_date AS lease_end,
            creator.name AS creator_name
     FROM invoices i
     LEFT JOIN leases     l  ON l.id = i.lease_id
     LEFT JOIN tenants    t  ON t.id = l.tenant_id
     LEFT JOIN units      u  ON u.id = l.unit_id
     LEFT JOIN properties p  ON p.id = u.property_id
     LEFT JOIN charge_types ct ON ct.id = i.charge_type_id
     LEFT JOIN users creator ON creator.id = i.created_by
     WHERE i.id = ?"
);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$inv = $stmt->get_result()->fetch_assoc();
if (!$inv)
    die('Invoice not found');
if (!require_same_tenant_or_super($inv['org_id']))
    die('Access denied');

$items_res = $conn->query("SELECT * FROM invoice_items WHERE invoice_id = $invoice_id ORDER BY sort_order, id");
$items = [];
while ($ir = $items_res->fetch_assoc()) {
    $items[] = $ir;
}

$pmts_res = $conn->query("SELECT * FROM payments_received WHERE invoice_id = $invoice_id ORDER BY received_date ASC");
$payments = [];
while ($pr = $pmts_res->fetch_assoc()) {
    $payments[] = $pr;
}

// ── Org settings ────────────────────────────────────────────────
$orgId = function_exists('current_org_id') ? (int) current_org_id() : (int) ($inv['org_id'] ?? 0);
$orgClause = $orgId > 0 ? "AND org_id = $orgId" : '';

$orgName = 'Property Management';
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
            if (!empty($sRow['setting_value']))
                $logoPath = './' . ltrim($sRow['setting_value'], './');
            break;
        case 'logo_path':
            if (!empty($sRow['setting_value']) && $logoPath === './public/images/logo.png')
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

// ── Amounts ──────────────────────────────────────────────────────
$amountPaid = (float) $inv['amount_paid'];
$balance = round(($inv['total_amount'] ?? 0) - $amountPaid, 2);
$statusColors = [
    'paid' => [40, 167, 69],
    'partial' => [255, 193, 7],
    'pending' => [108, 117, 125],
    'overdue' => [220, 53, 69],
];
$statusColor = $statusColors[$inv['status']] ?? [108, 117, 125];

// ── TCPDF ─────────────────────────────────────────────────────────
if (ob_get_length())
    ob_end_clean();

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('PMS');
$pdf->SetAuthor($orgName);
$pdf->SetTitle('Invoice ' . ($inv['invoice_number'] ?? ''));
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

$pageW = 180; // usable width (210 - 30mm margins)

// ── HEADER ────────────────────────────────────────────────────────
// Logo
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 15, 15, 30, 0, '', '', 'T', false, 300);
}

// Invoice Number (Top Right)
$pdf->SetXY(110, 15);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(30, 30, 30);
$inv_ref = $inv['reference_number'] ?? $inv['invoice_number'] ?? '';
$pdf->Cell(85, 7, 'NO. ' . $inv_ref, 0, 1, 'R');

// "INVOICE" Title (Large, Bold, Left)
$pdf->SetY(50);
$pdf->SetFont('helvetica', 'B', 32);
$pdf->SetTextColor(30, 30, 30);
$pdf->Cell(0, 15, 'INVOICE', 0, 1, 'L');

// Status Label (Top Right, Conditional)
$statusRaw = strtolower($inv['status'] ?? 'unpaid');
$statusText = ($statusRaw === 'paid') ? 'PAID' : 'UNPAID';
$statusColor = ($statusRaw === 'paid') ? [40, 167, 69] : [220, 53, 69]; // Green vs Red

$pdf->SetXY(110, 55);
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor($statusColor[0], $statusColor[1], $statusColor[2]);
$pdf->Cell(0, 10, $statusText, 0, 1, 'R');
$pdf->SetTextColor(30, 30, 30); // Reset

// Date (Below title)
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(15, 6, 'Date:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 6, date('d F, Y', strtotime($inv['invoice_date'] ?? 'now')), 0, 1, 'L');

$pdf->Ln(5);

// ── Billed to / From (2-column layout) ───────────────────────────
$yGrid = $pdf->GetY();
$colW = $pageW / 2;

// Left: Billed To
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell($colW, 6, 'Billed to:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell($colW, 5, $inv['tenant_name'] ?? 'N/A', 0, 1, 'L');
$pdf->MultiCell($colW, 4.5, ($inv['property_name'] ?? '') . "\n" . ($inv['property_address'] ?? '') . ', ' . ($inv['property_city'] ?? ''), 0, 'L');
$pdf->Cell($colW, 4.5, $inv['tenant_email'] ?? '', 0, 1, 'L');

// Right: From
$pdf->SetXY(15 + $colW, $yGrid);
$pdf->SetTextColor(30, 30, 30);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell($colW, 6, 'From:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetX(15 + $colW);
$pdf->Cell($colW, 5, $orgName, 0, 1, 'L');
$pdf->SetX(15 + $colW);
$pdf->MultiCell($colW, 4.5, $orgAddress, 0, 'L');
$pdf->SetX(15 + $colW);
$pdf->Cell($colW, 4.5, $orgEmail, 0, 1, 'L');

$pdf->Ln(10);
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(30, 30, 30);
// ── Items Table ────────────────────────────────────────────────────
$pdf->SetFillColor(240, 240, 240); // Light Grey
$pdf->SetTextColor(30, 30, 30);
$pdf->SetFont('helvetica', 'B', 9);
$colWs = [100, 25, 25, 30]; // Item, Quantity, Price, Amount
$headers = ['Item', 'Quantity', 'Price', 'Amount'];
foreach ($headers as $i => $h) {
    $pdf->Cell($colWs[$i], 7, $h, 'B', 0, $i === 0 ? 'L' : 'R', true);
}
$pdf->Ln();
$pdf->SetFont('helvetica', '', 9);
$totalAmount = 0;
if (!empty($items)) {
    foreach ($items as $item) {
        $pdf->SetDrawColor(230, 230, 230);
        $pdf->Cell($colWs[0], 8, $item['description'] ?? '', 'B', 0, 'L');
        $pdf->Cell($colWs[1], 8, $item['qty'] ?? 1, 'B', 0, 'R');
        $pdf->Cell($colWs[2], 8, '$' . number_format($item['unit_price'] ?? 0, 2), 'B', 0, 'R');
        $pdf->Cell($colWs[3], 8, '$' . number_format($item['line_total'] ?? 0, 2), 'B', 1, 'R');

        $totalAmount += $item['line_total'] ?? 0;
    }
} else {
    // Single-line invoice
    $pdf->SetDrawColor(230, 230, 230);
    $pdf->Cell($colWs[0], 8, $inv['description'] ?? ($inv['charge_type_name'] ?? 'Rent'), 'B', 0, 'L');
    $pdf->Cell($colWs[1], 8, '1', 'B', 0, 'R');
    $pdf->Cell($colWs[2], 8, '$' . number_format($totalAmount, 2), 'B', 0, 'R');
    $pdf->Cell($colWs[3], 8, '$' . number_format($totalAmount, 2), 'B', 1, 'R');
}

// Totals block (right-aligned)
$pdf->Ln(5);
$tw = 50;
$tx = 15 + $pageW - $tw;
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetX($tx);
$pdf->Cell($tw * 0.5, 8, 'Total', 0, 0, 'R');
$pdf->Cell($tw * 0.5, 8, '$' . number_format($totalAmount, 2), 0, 1, 'R');

// Signature Section
$pdf->SetY(240);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(30, 30, 30);
$pdf->Cell(60, 7, $inv['creator_name'] ?? 'Authorized Signatory', 0, 1, 'L');
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

$filename = 'Invoice_' . ($inv['invoice_number'] ?? $invoice_id) . '.pdf';
$pdf->Output($filename, 'I');
exit;
