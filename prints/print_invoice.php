<?php
/**
 * Invoice PDF Export — TCPDF
 * Called from pdf.php?print=invoice&id={invoice_id}
 */

$invoice_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($invoice_id <= 0) die('Invalid invoice ID');

if (!isset($conn)) { global $conn; }

// ── Fetch invoice + items + payments ────────────────────────────
$stmt = $conn->prepare(
    "SELECT i.*,
            t.full_name AS tenant_name, t.phone AS tenant_phone, t.email AS tenant_email,
            u.unit_number,
            p.name AS property_name, p.address AS property_address, p.city AS property_city,
            ct.name AS charge_type_name,
            l.monthly_rent AS rent_amount, l.start_date AS lease_start, l.end_date AS lease_end
     FROM invoices i
     LEFT JOIN leases     l  ON l.id = i.lease_id
     LEFT JOIN tenants    t  ON t.id = l.tenant_id
     LEFT JOIN units      u  ON u.id = l.unit_id
     LEFT JOIN properties p  ON p.id = u.property_id
     LEFT JOIN charge_types ct ON ct.id = i.charge_type_id
     WHERE i.id = ?"
);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$inv = $stmt->get_result()->fetch_assoc();
if (!$inv) die('Invoice not found');

$items_res = $conn->query("SELECT * FROM invoice_items WHERE invoice_id = $invoice_id ORDER BY sort_order, id");
$items = [];
while ($ir = $items_res->fetch_assoc()) { $items[] = $ir; }

$pmts_res = $conn->query("SELECT * FROM payments_received WHERE invoice_id = $invoice_id ORDER BY received_date ASC");
$payments = [];
while ($pr = $pmts_res->fetch_assoc()) { $payments[] = $pr; }

// ── Org settings ────────────────────────────────────────────────
$orgId     = function_exists('current_org_id') ? (int) current_org_id() : (int) ($inv['org_id'] ?? 0);
$orgClause = $orgId > 0 ? "AND org_id = $orgId" : '';

$orgName = 'Property Management'; $orgPhone = ''; $orgEmail = ''; $orgAddress = '';
$logoPath  = './public/images/logo.png';
$brandHex  = '1D3354';

$sRes = $conn->query("SELECT setting_key, setting_value FROM system_settings WHERE 1=1 $orgClause");
while ($sRow = $sRes->fetch_assoc()) {
    switch ($sRow['setting_key']) {
        case 'org_name':    $orgName    = $sRow['setting_value']; break;
        case 'org_phone':   $orgPhone   = $sRow['setting_value']; break;
        case 'org_email':   $orgEmail   = $sRow['setting_value']; break;
        case 'org_street1': $orgAddress = $sRow['setting_value']; break;
        case 'org_city':    $orgAddress .= ($orgAddress ? ', ' : '') . $sRow['setting_value']; break;
        case 'doc_logo_path':
            if (!empty($sRow['setting_value'])) $logoPath = './' . ltrim($sRow['setting_value'], './');
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
$totalAmount  = (float) $inv['total_amount'];
$amountPaid   = (float) $inv['amount_paid'];
$balance      = round($totalAmount - $amountPaid, 2);
$statusColors = [
    'paid'    => [40, 167, 69],
    'partial' => [255, 193, 7],
    'pending' => [108, 117, 125],
    'overdue' => [220, 53, 69],
];
$statusColor = $statusColors[$inv['status']] ?? [108, 117, 125];

// ── TCPDF ─────────────────────────────────────────────────────────
if (ob_get_length()) ob_end_clean();

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
    $pdf->Image($logoPath, 15, 15, 35, 18, '', '', 'T', false, 300);
}

// Org block (right)
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor($brandR, $brandG, $brandB);
$pdf->SetXY(110, 15);
$pdf->Cell(85, 7, $orgName, 0, 1, 'R');
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(80, 80, 80);
$pdf->SetX(110);
$pdf->Cell(85, 4.5, $orgAddress, 0, 1, 'R');
$pdf->SetX(110);
$pdf->Cell(85, 4.5, $orgPhone . ($orgEmail ? '  |  ' . $orgEmail : ''), 0, 1, 'R');

// Invoice title band
$pdf->SetY(40);
$pdf->SetFillColor($brandR, $brandG, $brandB);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell($pageW, 10, 'INVOICE', 0, 1, 'C', true);
$pdf->Ln(2);

// ── Invoice meta (2-column grid) ──────────────────────────────────
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(30, 30, 30);
$colW = $pageW / 2;

// Left: Billed To
$yMeta = $pdf->GetY();
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor($brandR, $brandG, $brandB);
$pdf->Cell($colW, 5, 'BILLED TO', 0, 1, 'L');
$pdf->SetTextColor(30, 30, 30);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell($colW, 5, $inv['tenant_name'] ?? 'N/A', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(80, 80, 80);
$pdf->SetX(15);
$pdf->Cell($colW, 4.5, ($inv['property_name'] ?? '') . ' — Unit ' . ($inv['unit_number'] ?? ''), 0, 1, 'L');
$pdf->SetX(15);
$pdf->Cell($colW, 4.5, $inv['tenant_phone'] ?? '', 0, 1, 'L');

// Right: Invoice details
$pdf->SetXY(15 + $colW, $yMeta);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor($brandR, $brandG, $brandB);
$pdf->Cell($colW, 5, 'INVOICE DETAILS', 0, 1, 'R');

$details = [
    ['Invoice #',  $inv['invoice_number'] ?? ''],
    ['Invoice Date', date('M d, Y', strtotime($inv['invoice_date'] ?? 'now'))],
    ['Due Date',   $inv['due_date'] ? date('M d, Y', strtotime($inv['due_date'])) : 'N/A'],
    ['Period',     ($inv['period_start'] ? date('M d, Y', strtotime($inv['period_start'])) . ' – ' . date('M d, Y', strtotime($inv['period_end'] ?? $inv['period_start'])) : 'N/A')],
];
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(30, 30, 30);
foreach ($details as $d) {
    $pdf->SetX(15 + $colW);
    $pdf->Cell($colW * 0.5, 4.5, $d[0] . ':', 0, 0, 'R');
    $pdf->Cell($colW * 0.5, 4.5, $d[1], 0, 1, 'R');
}

// Status badge
$pdf->SetXY(15 + $colW, $pdf->GetY() + 1);
$pdf->SetFillColor($statusColor[0], $statusColor[1], $statusColor[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell($colW, 6, strtoupper($inv['status'] ?? 'PENDING'), 0, 1, 'R', true);
$pdf->SetTextColor(30, 30, 30);
$pdf->Ln(4);

// ── Items Table ────────────────────────────────────────────────────
$pdf->SetFillColor($brandR, $brandG, $brandB);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 9);
$colWs = [80, 25, 25, 25, 25]; // Description, Qty, Unit Price, Line Total, Balance
$headers = ['Description', 'Qty', 'Unit Price', 'Line Total', 'Balance'];
foreach ($headers as $i => $h) {
    $pdf->Cell($colWs[$i], 7, $h, 0, 0, $i === 0 ? 'L' : 'R', true);
}
$pdf->Ln();
$pdf->SetTextColor(30, 30, 30);
$pdf->SetFont('helvetica', '', 8.5);

if (!empty($items)) {
    $rowNum = 0;
    foreach ($items as $item) {
        $rowNum++;
        $fill = ($rowNum % 2 === 0);
        if ($fill) $pdf->SetFillColor(245, 247, 250);
        $pdf->Cell($colWs[0], 6, $item['description'] ?? '', 0, 0, 'L', $fill);
        $pdf->Cell($colWs[1], 6, $item['quantity'],            0, 0, 'R', $fill);
        $pdf->Cell($colWs[2], 6, '$' . number_format($item['unit_price'], 2),   0, 0, 'R', $fill);
        $pdf->Cell($colWs[3], 6, '$' . number_format($item['line_total'], 2),   0, 0, 'R', $fill);
        $itemAlloc = (float)($item['allocated'] ?? 0);
        $itemBal   = round((float)$item['line_total'] - $itemAlloc, 2);
        $pdf->Cell($colWs[4], 6, '$' . number_format($itemBal, 2), 0, 1, 'R', $fill);
    }
} else {
    // Single-line invoice (no items table)
    $pdf->Cell($colWs[0], 6, $inv['description'] ?? ($inv['charge_type_name'] ?? 'Rent'), 0, 0, 'L');
    $pdf->Cell($colWs[1], 6, '1',                                                  0, 0, 'R');
    $pdf->Cell($colWs[2], 6, '$' . number_format($totalAmount, 2),                 0, 0, 'R');
    $pdf->Cell($colWs[3], 6, '$' . number_format($totalAmount, 2),                 0, 0, 'R');
    $pdf->Cell($colWs[4], 6, '$' . number_format($balance, 2),                     0, 1, 'R');
}

// Totals block (right-aligned)
$pdf->Ln(3);
$tw = 90;  $tx = 15 + $pageW - $tw;
$summaryRows = [
    ['Sub-total',    '$' . number_format($totalAmount, 2)],
    ['Amount Paid',  '-$' . number_format($amountPaid, 2)],
    ['Balance Due',  '$' . number_format(max(0, $balance), 2), true],
];
foreach ($summaryRows as $sr) {
    $bold = !empty($sr[2]);
    $pdf->SetFont('helvetica', $bold ? 'B' : '', 9);
    if ($bold) {
        $pdf->SetFillColor($brandR, $brandG, $brandB);
        $pdf->SetTextColor(255, 255, 255);
    }
    $pdf->SetX($tx);
    $pdf->Cell($tw * 0.55, 6, $sr[0], 0, 0, 'R', $bold);
    $pdf->Cell($tw * 0.45, 6, $sr[1], 0, 1, 'R', $bold);
    if ($bold) $pdf->SetTextColor(30, 30, 30);
}

// ── Payments History ───────────────────────────────────────────────
if (!empty($payments)) {
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor($brandR, $brandG, $brandB);
    $pdf->Cell($pageW, 5, 'PAYMENT HISTORY', 0, 1, 'L');
    $pdf->SetFillColor($brandR, $brandG, $brandB);
    $pdf->SetTextColor(255, 255, 255);
    $ph = [50, 40, 45, 45];
    foreach (['Date', 'Receipt #', 'Method', 'Amount'] as $i => $h) {
        $pdf->Cell($ph[$i], 6, $h, 0, 0, $i === 0 ? 'L' : 'R', true);
    }
    $pdf->Ln();
    $pdf->SetTextColor(30, 30, 30);
    $pdf->SetFont('helvetica', '', 8.5);
    foreach ($payments as $pmt) {
        $pdf->Cell($ph[0], 5.5, date('M d, Y', strtotime($pmt['received_date'])), 0, 0, 'L');
        $pdf->Cell($ph[1], 5.5, $pmt['receipt_number'] ?? 'N/A',                  0, 0, 'R');
        $pdf->Cell($ph[2], 5.5, $pmt['payment_method'] ?? '',                     0, 0, 'R');
        $pdf->Cell($ph[3], 5.5, '$' . number_format($pmt['amount_paid'], 2),      0, 1, 'R');
    }
}

// ── Footer ────────────────────────────────────────────────────────
$pdf->SetY(-18);
$pdf->SetDrawColor($brandR, $brandG, $brandB);
$pdf->SetLineWidth(0.3);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('helvetica', 'I', 7.5);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell($pageW, 4, 'Thank you for your business! — ' . $orgName, 0, 1, 'C');
$pdf->Cell($pageW, 4, 'Generated on ' . date('M d, Y H:i'), 0, 0, 'C');

$filename = 'Invoice_' . ($inv['invoice_number'] ?? $invoice_id) . '.pdf';
$pdf->Output($filename, 'D');
exit;
