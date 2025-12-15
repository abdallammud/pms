<?php
/**
 * Print Lease Agreement PDF
 * Uses Standard TCPDF Cells/MultiCells (No HTML Tables)
 */

// Get lease ID
$lease_id = isset($_GET['lease_id']) ? intval($_GET['lease_id']) : 0;
if ($lease_id <= 0) die('Invalid lease ID');

$conn = $GLOBALS['conn'];

// Fetch data
$sql = "SELECT l.*, 
               t.full_name as tenant_name, t.phone as tenant_phone, t.email as tenant_email, t.id_number as tenant_id_number,
               g.full_name as guarantee_name, g.phone as guarantee_phone, g.email as guarantee_email, g.id_number as guarantee_id_number,
               p.name as property_name, p.address as property_address, p.city as property_city,
               u.unit_number, u.unit_type, u.size_sqft
        FROM leases l 
        LEFT JOIN tenants t ON l.tenant_id = t.id
        LEFT JOIN guarantees g ON l.guarantee_id = g.id
        LEFT JOIN properties p ON l.property_id = p.id
        LEFT JOIN units u ON l.unit_id = u.id
        WHERE l.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lease_id);
$stmt->execute();
$lease = $stmt->get_result()->fetch_assoc();
if (!$lease) die('Lease not found');

$witnesses = json_decode($lease['witnesses'] ?? '[]', true) ?: [];

// Status colors
$statusText = ucfirst($lease['status']);
$statusColor = [108, 117, 125]; // Gray
if ($lease['status'] == 'active') $statusColor = [40, 167, 69]; // Green
elseif ($lease['status'] == 'pending') $statusColor = [255, 193, 7]; // Yellow
elseif ($lease['status'] == 'expired') $statusColor = [220, 53, 69]; // Red

// Dates
$startDate = date('F d, Y', strtotime($lease['start_date']));
$endDate = date('F d, Y', strtotime($lease['end_date']));
$printedDate = date('F d, Y'); 

// Org Info
$orgName = ''; $orgAddress = ''; $orgPhone = ''; $logoPath = "./public/images/logo.jpg";
$result = $conn->query("SELECT setting_key, setting_value FROM system_settings");
while ($row = $result->fetch_assoc()) {
    if ($row['setting_key'] == 'org_name') $orgName = $row['setting_value'];
    if ($row['setting_key'] == 'org_street1') $orgAddress = $row['setting_value'];
    if ($row['setting_key'] == 'org_city') $orgAddress .= ', ' . $row['setting_value'];
    if ($row['setting_key'] == 'org_phone') $orgPhone = $row['setting_value'];
    if ($row['setting_key'] == 'logo_path' && !empty($row['setting_value'])) $logoPath = $row['setting_value'];
}
$logoPath = "./public/images/logo.jpg";
// Init PDF
// Clean any existing output buffer to prevent "Some data has already been output" errors
if (ob_get_length()) ob_end_clean();

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('PMS');
$pdf->SetAuthor($orgName);
$pdf->SetTitle('Lease ' . ($lease['reference_number'] ?? ''));
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->AddPage();

// Colors
$colHeaderBg = [234, 236, 244]; // #eaecf4
$colLabel = [90, 92, 105];      // #5a5c69
$colValue = [0, 0, 0];
$colPrimary = [1, 135, 37];     // #018725

// ============================================================
// HEADER
// ============================================================
$yStart = $pdf->GetY();
// Logo
if (file_exists($logoPath)) {
    // Suppress potential libpng warnings (iCCP sRGB profile) to avoid breaking PDF output
    @$pdf->Image($logoPath, 10, $yStart, 30);
}

// Company Info (Right Aligned)
$pdf->SetXY(100, $yStart);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColorArray($colPrimary);
$pdf->Cell(100, 6, $orgName, 0, 1, 'R');

$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(108, 117, 125);
$pdf->Cell(190, 4, $orgAddress, 0, 1, 'R');
$pdf->Cell(190, 4, $orgPhone, 0, 1, 'R');
$pdf->Cell(190, 4, 'Date / Taariikh: ' . $printedDate, 0, 1, 'R');

$pdf->SetY($yStart + 22);

// Title
$pdf->SetFont('helvetica', 'B', 18);
$pdf->SetTextColorArray($colPrimary);
$pdf->Cell(100, 8, 'Lease Agreement', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(100, 6, 'Heshiis Kiro', 0, 1, 'L');

// Badge & Ref
$pdf->Ln(1);
$currentY = $pdf->GetY();
// Badge
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetFillColorArray($statusColor);
$pdf->SetTextColor(255, 255, 255);
$badgeWidth = $pdf->GetStringWidth($statusText) + 6;
$pdf->Cell($badgeWidth, 5, $statusText, 0, 0, 'C', 1);

// Reference
$pdf->SetTextColor(108, 117, 125);
$pdf->SetFont('helvetica', '', 9);
$pdf->SetX($pdf->GetX() + 2);
$pdf->Cell(50, 5, 'Reference: ' . ($lease['reference_number'] ?? 'N/A'), 0, 1, 'L');

$pdf->Ln(5);

// ============================================================
// HELPER FUNCTIONS FOR CARDS
// ============================================================
function drawCardHeader($pdf, $x, $y, $w, $titleEng, $titleSom) {
    $pdf->SetXY($x, $y);
    $pdf->SetFillColor(234, 236, 244); 
    $pdf->SetTextColor(51, 51, 51);
    $pdf->SetFont('helvetica', 'B', 9);
    // Draw background rect
    $pdf->Rect($x, $y, $w, 10, 'F');
    // Text
    $pdf->Cell($w, 5, $titleEng, 0, 1, 'L');
    $pdf->SetXY($x, $y + 4);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell($w, 5, $titleSom, 0, 1, 'L');
    return $y + 10;
}

function drawRow($pdf, $x, $y, $w, $labelEng, $labelSom, $value, $isLast=false) {
    $h = 10; // Fixed row height
    // Border bottom line
    if (!$isLast) {
        $pdf->SetLineStyle(['width' => 0.1, 'color' => [240, 240, 240]]);
        $pdf->Line($x, $y + $h, $x + $w, $y + $h);
    }
    
    // Label
    $pdf->SetXY($x + 2, $y + 1);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor(90, 92, 105);
    $pdf->Cell($w * 0.4, 4, $labelEng, 0, 1);
    
    $pdf->SetXY($x + 2, $y + 5);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor(133, 135, 150);
    $pdf->Cell($w * 0.4, 4, $labelSom, 0, 1);
    
    // Value
    $pdf->SetXY($x + ($w * 0.4), $y);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($w * 0.6, $h, $value, 0, 1, 'L', 0, '', 1); // Vertically aligned
    
    return $y + $h;
}

// Layout Variables
$pageWidth = 190; // 210 - 10 - 10
$colGap = 5;
$colWidth = ($pageWidth - $colGap) / 2;
$startX = 10;
$startY = $pdf->GetY();

// ============================================================
// ROW 1: TENANT & GUARANTEE
// ============================================================
// Left Card: Tenant
$curX = $startX;
$curY = $startY;

// Draw Card Border
$cardH = 10 + (3 * 10); // Header + 3 rows
$pdf->SetLineStyle(['width' => 0.1, 'color' => [200, 200, 200]]);
$pdf->Rect($curX, $curY, $colWidth, $cardH);

$curY = drawCardHeader($pdf, $curX, $curY, $colWidth, 'Tenant Information', 'Macluumaadka Kiraystaha');
$curY = drawRow($pdf, $curX, $curY, $colWidth, 'Full Name', 'Magaca', $lease['tenant_name']);
$curY = drawRow($pdf, $curX, $curY, $colWidth, 'Phone', 'Telefoon', $lease['tenant_phone']);
$curY = drawRow($pdf, $curX, $curY, $colWidth, 'Email', 'Email', $lease['tenant_email'], true);

// Right Card: Guarantee
$curX = $startX + $colWidth + $colGap;
$curY = $startY;
$pdf->Rect($curX, $curY, $colWidth, $cardH);

$curY = drawCardHeader($pdf, $curX, $curY, $colWidth, 'Guarantee Information', 'Macluumaadka Damiinaha');
$curY = drawRow($pdf, $curX, $curY, $colWidth, 'Full Name', 'Magaca', $lease['guarantee_name']);
$curY = drawRow($pdf, $curX, $curY, $colWidth, 'Phone', 'Telefoon', $lease['guarantee_phone']);
$curY = drawRow($pdf, $curX, $curY, $colWidth, 'Email', 'Email', $lease['guarantee_email'], true);

$pdf->SetY($startY + $cardH + 5);

// ============================================================
// ROW 2: PROPERTY & FINANCIAL
// ============================================================
$startY = $pdf->GetY();
$cardH = 10 + (5 * 10); // Header + 5 rows (Property)
// Financial has fewer rows but we'll match height or let them be diff?
// Let's make financial match height or just draw independently. 
// Financial has 4 items. Property has 5 items.
// We'll set Property Height.

// Left: Property
$curX = $startX;
$curY = $startY;
$pdf->Rect($curX, $curY, $colWidth, $cardH);
$curY = drawCardHeader($pdf, $curX, $curY, $colWidth, 'Property & Unit', 'Dhismaha & Guriga');
$curY = drawRow($pdf, $curX, $curY, $colWidth, 'Property', 'Dhismaha', $lease['property_name']);
$curY = drawRow($pdf, $curX, $curY, $colWidth, 'Address', 'Cinwaanka', $lease['property_address'] . ', ' . $lease['property_city']);
$curY = drawRow($pdf, $curX, $curY, $colWidth, 'Unit', 'Guriga', $lease['unit_number']);
$curY = drawRow($pdf, $curX, $curY, $colWidth, 'Type', 'Nooca', $lease['unit_type']);
$curY = drawRow($pdf, $curX, $curY, $colWidth, 'Size (sq ft)', 'Cabirka', number_format($lease['size_sqft']), true);

// Right: Financial
$curX = $startX + $colWidth + $colGap;
$curY = $startY;
// Use same height for visual balance
$pdf->Rect($curX, $curY, $colWidth, $cardH);

$curY = drawCardHeader($pdf, $curX, $curY, $colWidth, 'Financial Details', 'Faahfaahinta Lacagta');
$curY = drawRow($pdf, $curX, $curY, $colWidth, 'Monthly Rent', 'Kirada', '$' . number_format($lease['monthly_rent'], 2));
// Bold green for rent
$pdf->SetTextColor(40, 167, 69);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetXY($curX + ($colWidth * 0.4), $curY - 10);
// $pdf->Cell($colWidth * 0.6, 10, '$' . number_format($lease['monthly_rent'], 2), 0, 0, 'L');
// Reset
$pdf->SetTextColor(0,0,0);

$curY = drawRow($pdf, $curX, $curY, $colWidth, 'Deposit', 'Debaaji', '$' . number_format($lease['deposit'], 2));
$curY = drawRow($pdf, $curX, $curY, $colWidth, 'Cycle', 'Wareega', ucfirst($lease['payment_cycle']));
$curY = drawRow($pdf, $curX, $curY, $colWidth, 'Auto Invoice', 'Qaansheeg Toos', $lease['auto_invoice'] ? 'Yes / Haa' : 'No / Maya', true);
// Empty row to fill space
//$pdf->SetY($startY + $cardH + 5);

$pdf->SetY($startY + $cardH + 5);

// ============================================================
// LEASE CONDITIONS
// ============================================================
if (!empty($lease['lease_conditions'])) {
    $curY = $pdf->GetY();
    
    // Header
    $pdf->SetFillColor(234, 236, 244);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->Rect(10, $curY, 190, 10, 'F');
    $pdf->SetXY(10, $curY);
    $pdf->Cell(190, 5, 'Lease Conditions', 0, 1, 'L');
    $pdf->SetXY(10, $curY + 4);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(190, 5, 'Shuruudaha Heshiiska', 0, 1, 'L');
    
    $curY += 10;
    
    // Content Box
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(0, 0, 0);
    
    // Clean text
    $condText = strip_tags(str_replace(['<br>', '<br/>', '<p>', '</p>'], ["\n", "\n", "\n", "\n"], $lease['lease_conditions']));
    $condText = trim(preg_replace('/\n+/', "\n", $condText));
    
    // Calculate height
    $numLines = $pdf->getNumLines($condText, 190);
    $boxH = ($numLines * 4) + 4; // 4mm per line + padding
    
    $pdf->Rect(10, $curY, 190, $boxH);
    $pdf->SetXY(10, $curY + 2);
    $pdf->MultiCell(190, 4, $condText, 0, 'L');
    
    $pdf->SetY($curY + $boxH + 5);
}

// ============================================================
// WITNESSES
// ============================================================
if (!empty($witnesses)) {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 8, 'Witnesses / Marqaatiyaal', 0, 1);
    
    $pdf->SetFont('helvetica', '', 8);
    foreach ($witnesses as $i => $w) {
        $y = $pdf->GetY();
        if ($y > 260) { $pdf->AddPage(); $y = 10; } // Auto page break check manually if needed
        
        $name = $w['name'] ?? '';
        $phone = $w['phone'] ?? '';
        $id = $w['id_card'] ?? '';
        
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(25, 5, 'Name / Magaca:', 0, 0);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(40, 5, $name, 0, 0);
        
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(7, 5, 'Tel:', 0, 0);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(20, 5, $phone, 0, 0);
        
        // Signature Line
        $pdf->SetX(120);
        $pdf->Cell(60, 5, 'Signature: ___________________________________', 0, 1, 'R');
        
        $pdf->Ln(2);
        // Divider
        $pdf->SetLineStyle(['width' => 0.1, 'color' => [200, 200, 200], 'dash' => '1,1']);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->SetLineStyle(['width' => 0.1, 'color' => [0,0,0], 'dash' => 0]); // Reset
        $pdf->Ln(2);
    }
    $pdf->Ln(3);
}

// ============================================================
// VEHICLE & WEAPONS
// ============================================================
if (!empty($lease['vehicle_info']) || !empty($lease['legal_weapons'])) {
    $y = $pdf->GetY();
    // Check page break
    if ($y > 250) { $pdf->AddPage(); $y = 10; }

    // Header
    $pdf->SetFillColor(234, 236, 244);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Rect(10, $y, 190, 10, 'F');
    $pdf->SetXY(10, $y);
    $pdf->Cell(190, 5, 'Vehicle & Legal Weapons', 0, 1, 'L');
    $pdf->SetXY(10, $y + 4);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->Cell(190, 5, 'Gaariga & Hubka', 0, 1, 'L');
    
    $y += 10;
    
    // Two columns
    $pdf->Rect(10, $y, 190, 20); // Border
    $pdf->Line(105, $y, 105, $y + 20); // Middle Line
    
    // Col 1
    $pdf->SetXY(12, $y + 2);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(90, 4, 'Vehicle Information', 0, 1);
    $pdf->SetX(12);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(90, 3, 'Macluumaadka Gaariga', 0, 1);
    $pdf->SetX(12);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->MultiCell(90, 8, strip_tags($lease['vehicle_info']), 0, 'L');

    // Col 2
    $pdf->SetXY(107, $y + 2);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(90, 4, 'Legal Weapons', 0, 1);
    $pdf->SetX(107);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(90, 3, 'Hubka Sharciga ah', 0, 1);
    $pdf->SetX(107);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->MultiCell(90, 8, strip_tags($lease['legal_weapons']), 0, 'L');

    $pdf->SetY($y + 25);
}

// ============================================================
// SIGNATURES
// ============================================================
$y = $pdf->GetY();
if ($y > 265) { $pdf->AddPage(); $y = 10; }

$pdf->SetY($y + 5);
$pdf->SetFont('helvetica', 'B', 9);

$pdf->Line(10, $y + 15, 80, $y + 15);
$pdf->SetXY(10, $y + 16);
$pdf->Cell(70, 4, 'Tenant Signature / Saxiixa Kiraystaha', 0, 1, 'C');
$pdf->SetX(10);
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(70, 4, $lease['tenant_name'], 0, 1, 'C');

$pdf->Line(130, $y + 15, 200, $y + 15);
$pdf->SetXY(130, $y + 16);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(70, 4, 'Manager Signature / Saxiixa Maamulaha', 0, 1, 'C');
$pdf->SetXY(130, $y + 20);
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(70, 4, $orgName, 0, 1, 'C');

// Footer
// $pdf->SetY(-15);
// $pdf->SetFont('helvetica', 'I', 7);
// $pdf->SetTextColor(150, 150, 150);
// $pdf->Cell(0, 5, 'Generated on ' . date('F d, Y H:i'), 0, 1, 'C');
// $pdf->Cell(0, 5, $orgName . ' - Property Management System', 0, 1, 'C');

// Output
$pdf->Output('Lease_' . $lease_id . '.pdf', 'I');