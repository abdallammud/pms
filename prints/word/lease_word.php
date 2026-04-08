<?php
/**
 * Generate Lease Agreement Word Document
 * Design matching PDF layout with Custom Blue headers
 */

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Shared\Html;

// CRITICAL: Discard ALL buffered output
while (ob_get_level()) {
    ob_end_clean();
}

// Validate input
$lease_id = isset($_GET['lease_id']) ? (int) $_GET['lease_id'] : 0;
if ($lease_id <= 0) {
    die('Invalid lease ID');
}

$conn = $GLOBALS['conn'];

// Fetch lease data
$sql = "
SELECT l.*,
       t.full_name AS tenant_name, t.phone AS tenant_phone, t.email AS tenant_email, t.id_number AS tenant_id_number,
       g.full_name AS guarantee_name, g.phone AS guarantee_phone, g.email AS guarantee_email, g.id_number AS guarantee_id_number,
       p.name AS property_name, p.address AS property_address, p.city AS property_city,
       u.unit_number, u.unit_type, u.size_sqft
FROM leases l
LEFT JOIN tenants t ON l.tenant_id = t.id
LEFT JOIN guarantees g ON l.guarantee_id = g.id
LEFT JOIN properties p ON l.property_id = p.id
LEFT JOIN units u ON l.unit_id = u.id
WHERE l.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lease_id);
$stmt->execute();
$lease = $stmt->get_result()->fetch_assoc();

if (!$lease) {
    die('Lease not found');
}

$witnesses = json_decode($lease['witnesses'] ?? '[]', true) ?: [];

// Safe string helper - avoid ampersand and special chars
function safeStr($v, $d = 'N/A')
{
    if ($v === null || $v === '')
        return $d;
    return str_replace('&', 'and', (string) $v);
}

// Status and dates
$statusText = ucfirst($lease['status'] ?? 'unknown');
$printedDate = date('F d, Y');

// Status colors
$statusColor = '6C757D'; // Gray
if ($lease['status'] == 'active')
    $statusColor = '28A745';
elseif ($lease['status'] == 'pending')
    $statusColor = 'FFC107';
elseif ($lease['status'] == 'expired')
    $statusColor = 'DC3545';

// Org Info
$orgName = '';
$orgAddress = '';
$orgPhone = '';
$res = $conn->query("SELECT setting_key, setting_value FROM system_settings");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $key = strtolower($row['setting_key']);
        if ($key == 'org_name')
            $orgName = $row['setting_value'];
        if ($key == 'org_street1')
            $orgAddress = $row['setting_value'];
        if ($key == 'org_city')
            $orgAddress .= (!empty($orgAddress) ? ', ' : '') . $row['setting_value'];
        if ($key == 'org_phone')
            $orgPhone = $row['setting_value'];
    }
}

// Fallbacks
if (empty($orgName))
    $orgName = "Kaad PMS";
if (empty($orgAddress))
    $orgAddress = "Taleh Tower - Taleh Street - Mogadishu Somalia, Mogadishu";
if (empty($orgPhone))
    $orgPhone = "+252 77 100038 | +252 77100039";

// Custom System Blue color and font
$customBlue = '2E62A8'; // Matching --primary-accent: #2e62a8
$labelColor = '5A5C69';
$primaryGreen = '018725';

// Widths
$fullWidth = 10500;
$halfWidth = 5150;
$spacerWidth = 200;

// Create document
$phpWord = new PhpWord();
$phpWord->setDefaultFontName('Arial');
$phpWord->setDefaultFontSize(10);

$section = $phpWord->addSection([
    'marginLeft' => 720,
    'marginRight' => 720,
    'marginTop' => 720,
    'marginBottom' => 720,
]);

// ============================================================
// HEADER - Logo and Company Info
// ============================================================
$headerTable = $section->addTable(['borderSize' => 0, 'borderColor' => 'FFFFFF', 'cellMargin' => 0]);
$headerTable->addRow();

// Logo cell
$logoCell = $headerTable->addCell(5000);
$logoPath = "./public/images/logo.jpg";
if (file_exists($logoPath)) {
    $logoCell->addImage($logoPath, ['width' => 120, 'height' => 80]);
}

// Company info cell
$infoCell = $headerTable->addCell(5500);
$infoCell->addText($orgName, [
    'bold' => true,
    'size' => 12,
    'color' => $primaryGreen
], ['alignment' => Jc::END]);
$infoCell->addText($orgAddress, ['size' => 8, 'color' => '6C757D'], ['alignment' => Jc::END]);
$infoCell->addText($orgPhone, ['size' => 8, 'color' => '6C757D'], ['alignment' => Jc::END]);
$infoCell->addText('Date: ' . $printedDate, ['size' => 8, 'color' => '6C757D'], ['alignment' => Jc::END]);

$section->addTextBreak();

// ============================================================
// TITLE
// ============================================================
$section->addText('Lease Agreement', ['bold' => true, 'size' => 18, 'color' => $primaryGreen], ['spaceAfter' => 0]);
$section->addText('Heshiis Kiro', ['size' => 12, 'color' => $primaryGreen], ['spaceAfter' => 100]);

// Status badge and reference
$textRun = $section->addTextRun();
$textRun->addText(' ' . $statusText . ' ', ['bold' => true, 'size' => 9, 'color' => 'FFFFFF', 'bgColor' => $statusColor]);
$textRun->addText('  Reference: ' . safeStr($lease['reference_number']), ['size' => 10, 'color' => '6C757D']);

$section->addTextBreak();

// ============================================================
// TENANT & GUARANTEE INFO
// ============================================================
$mainTable = $section->addTable(['borderSize' => 0, 'borderColor' => 'FFFFFF', 'cellMargin' => 0]);
$mainTable->addRow();

// Tenant
$leftCell = $mainTable->addCell($halfWidth, ['valign' => 'top']);
$h1 = $leftCell->addTable(['borderSize' => 0, 'cellMargin' => 40]);
$h1->addRow();
$h1->addCell($halfWidth, ['bgColor' => $customBlue])->addText('Tenant Information / Macluumaadka Kiraystaha', ['bold' => true, 'size' => 10, 'color' => 'FFFFFF']);

$tTable = $leftCell->addTable(['borderSize' => 4, 'borderColor' => 'DDDDDD', 'cellMargin' => 60]);
$tRows = [
    ['Full Name', safeStr($lease['tenant_name'])],
    ['Phone', safeStr($lease['tenant_phone'])],
    ['Email', safeStr($lease['tenant_email'])],
    ['ID Number', safeStr($lease['tenant_id_number'])],
];
foreach ($tRows as $row) {
    $tTable->addRow();
    $tTable->addCell(2000, ['bgColor' => 'F8F9FA'])->addText($row[0], ['bold' => true, 'size' => 8, 'color' => $labelColor]);
    $tTable->addCell(3150)->addText($row[1], ['size' => 9, 'bold' => true]);
}

$mainTable->addCell($spacerWidth);

// Guarantee
$rightCell = $mainTable->addCell($halfWidth, ['valign' => 'top']);
$h2 = $rightCell->addTable(['borderSize' => 0, 'cellMargin' => 40]);
$h2->addRow();
$h2->addCell($halfWidth, ['bgColor' => $customBlue])->addText('Guarantee Information / Macluumaadka Damiinaha', ['bold' => true, 'size' => 10, 'color' => 'FFFFFF']);

$gTable = $rightCell->addTable(['borderSize' => 4, 'borderColor' => 'DDDDDD', 'cellMargin' => 60]);
$gRows = [
    ['Full Name', safeStr($lease['guarantee_name'])],
    ['Phone', safeStr($lease['guarantee_phone'])],
    ['Email', safeStr($lease['guarantee_email'])],
    ['ID Number', safeStr($lease['guarantee_id_number'])],
];
foreach ($gRows as $row) {
    $gTable->addRow();
    $gTable->addCell(2000, ['bgColor' => 'F8F9FA'])->addText($row[0], ['bold' => true, 'size' => 8, 'color' => $labelColor]);
    $gTable->addCell(3150)->addText($row[1], ['size' => 9, 'bold' => true]);
}

$section->addTextBreak();

// ============================================================
// PROPERTY & FINANCIAL
// ============================================================
$mainTable2 = $section->addTable(['borderSize' => 0, 'borderColor' => 'FFFFFF', 'cellMargin' => 0]);
$mainTable2->addRow();

// Property
$leftCell2 = $mainTable2->addCell($halfWidth, ['valign' => 'top']);
$h3 = $leftCell2->addTable(['borderSize' => 0, 'cellMargin' => 40]);
$h3->addRow();
$h3->addCell($halfWidth, ['bgColor' => $customBlue])->addText('Property and Unit / Dhismaha and Guriga', ['bold' => true, 'size' => 10, 'color' => 'FFFFFF']);

$pTable = $leftCell2->addTable(['borderSize' => 4, 'borderColor' => 'DDDDDD', 'cellMargin' => 60]);
$pRows = [
    ['Property', safeStr($lease['property_name'])],
    ['Address', safeStr($lease['property_address'], '') . ', ' . safeStr($lease['property_city'], '')],
    ['Unit', safeStr($lease['unit_number'])],
    ['Type', safeStr($lease['unit_type'])],
    ['Size (sq ft)', number_format((float) ($lease['size_sqft'] ?? 0))],
];
foreach ($pRows as $row) {
    $pTable->addRow();
    $pTable->addCell(2000, ['bgColor' => 'F8F9FA'])->addText($row[0], ['bold' => true, 'size' => 8, 'color' => $labelColor]);
    $pTable->addCell(3150)->addText($row[1], ['size' => 9, 'bold' => true]);
}

$mainTable2->addCell($spacerWidth);

// Financial
$rightCell2 = $mainTable2->addCell($halfWidth, ['valign' => 'top']);
$h4 = $rightCell2->addTable(['borderSize' => 0, 'cellMargin' => 40]);
$h4->addRow();
$h4->addCell($halfWidth, ['bgColor' => $customBlue])->addText('Financial Details / Faahfaahinta Lacagta', ['bold' => true, 'size' => 10, 'color' => 'FFFFFF']);

$fTable = $rightCell2->addTable(['borderSize' => 4, 'borderColor' => 'DDDDDD', 'cellMargin' => 60]);
// Lease Start
$fTable->addRow();
$fTable->addCell(2000, ['bgColor' => 'F8F9FA'])->addText('Lease Start', ['bold' => true, 'size' => 8, 'color' => $labelColor]);
$fTable->addCell(3150)->addText(date('F d, Y', strtotime($lease['start_date'] ?? 'now')), ['size' => 9, 'bold' => true]);

$fTable->addRow();
$fTable->addCell(2000, ['bgColor' => 'F8F9FA'])->addText('Monthly Rent', ['bold' => true, 'size' => 8, 'color' => $labelColor]);
$fTable->addCell(3150)->addText('$' . number_format((float) ($lease['monthly_rent'] ?? 0), 2), ['bold' => true, 'size' => 10, 'color' => '28A745']);

$fRows = [
    ['Deposit', '$' . number_format((float) ($lease['deposit'] ?? 0), 2)],
    ['Cycle', ucfirst(safeStr($lease['payment_cycle'], 'Monthly'))],
    ['Auto Invoice', ($lease['auto_invoice'] ?? false) ? 'Yes' : 'No'],
];
foreach ($fRows as $row) {
    $fTable->addRow();
    $fTable->addCell(2000, ['bgColor' => 'F8F9FA'])->addText($row[0], ['bold' => true, 'size' => 8, 'color' => $labelColor]);
    $fTable->addCell(3150)->addText($row[1], ['size' => 9, 'bold' => true]);
}

$section->addTextBreak();

// ============================================================
// VEHICLE & WEAPONS INFO
// ============================================================
$mainTable3 = $section->addTable(['borderSize' => 0, 'borderColor' => 'FFFFFF', 'cellMargin' => 0]);
$mainTable3->addRow();

// Vehicle
$leftCell3 = $mainTable3->addCell($halfWidth, ['valign' => 'top']);
$hV = $leftCell3->addTable(['borderSize' => 0, 'cellMargin' => 40]);
$hV->addRow();
$hV->addCell($halfWidth, ['bgColor' => $customBlue])->addText('Vehicle Information / Macluumaadka Gaariga', ['bold' => true, 'size' => 10, 'color' => 'FFFFFF']);

$vTable = $leftCell3->addTable(['borderSize' => 4, 'borderColor' => 'DDDDDD', 'cellMargin' => 60]);
$vTable->addRow();
$vTable->addCell(5150)->addText(safeStr($lease['vehicle_info'] ?? 'N/A'), ['size' => 9]);

$mainTable3->addCell($spacerWidth);

// Weapons
$rightCell3 = $mainTable3->addCell($halfWidth, ['valign' => 'top']);
$hW = $rightCell3->addTable(['borderSize' => 0, 'cellMargin' => 40]);
$hW->addRow();
$hW->addCell($halfWidth, ['bgColor' => $customBlue])->addText('Weapons Information / Hubka Sharciga ah', ['bold' => true, 'size' => 10, 'color' => 'FFFFFF']);

$wTable = $rightCell3->addTable(['borderSize' => 4, 'borderColor' => 'DDDDDD', 'cellMargin' => 60]);
$wTable->addRow();
$wTable->addCell(5150)->addText(safeStr($lease['legal_weapons'] ?? 'N/A'), ['size' => 9]);

$section->addTextBreak();

// ============================================================
// LEASE CONDITIONS (HTML Handling)
// ============================================================
if (!empty($lease['lease_conditions'])) {
    $h5 = $section->addTable(['borderSize' => 0, 'cellMargin' => 40]);
    $h5->addRow();
    $h5->addCell($fullWidth, ['bgColor' => $customBlue])->addText('Lease Conditions / Shuruudaha Heshiiska', ['bold' => true, 'size' => 10, 'color' => 'FFFFFF']);

    $condTable = $section->addTable(['borderSize' => 4, 'borderColor' => 'DDDDDD', 'cellMargin' => 80]);
    $condTable->addRow();
    $condCell = $condTable->addCell($fullWidth);

    // Fix: Using HTML parser to correctly render formatted content
    $htmlContent = '<div>' . str_replace('&', '&amp;', $lease['lease_conditions']) . '</div>';
    Html::addHtml($condCell, $htmlContent, false, false);

    $section->addTextBreak();
}

// ============================================================
// WITNESSES
// ============================================================
if (!empty($witnesses)) {
    $hw = $section->addTable(['borderSize' => 0, 'cellMargin' => 40]);
    $hw->addRow();
    $hw->addCell($fullWidth, ['bgColor' => $customBlue])->addText('Witnesses / Marqaatiyaal', ['bold' => true, 'size' => 10, 'color' => 'FFFFFF']);
    $section->addTextBreak();

    $witTable = $section->addTable(['borderSize' => 4, 'borderColor' => 'DDDDDD', 'cellMargin' => 60]);
    $witTable->addRow();
    $witTable->addCell(600, ['bgColor' => $customBlue])->addText('#', ['bold' => true, 'size' => 9, 'color' => 'FFFFFF']);
    $witTable->addCell(2800, ['bgColor' => $customBlue])->addText('Name', ['bold' => true, 'size' => 9, 'color' => 'FFFFFF']);
    $witTable->addCell(2200, ['bgColor' => $customBlue])->addText('Phone', ['bold' => true, 'size' => 9, 'color' => 'FFFFFF']);
    $witTable->addCell(2200, ['bgColor' => $customBlue])->addText('ID Card', ['bold' => true, 'size' => 9, 'color' => 'FFFFFF']);
    $witTable->addCell(2700, ['bgColor' => $customBlue])->addText('Signature', ['bold' => true, 'size' => 9, 'color' => 'FFFFFF']);

    foreach ($witnesses as $i => $w) {
        $witTable->addRow();
        $witTable->addCell(600)->addText(strval($i + 1), ['size' => 9]);
        $witTable->addCell(2800)->addText(safeStr($w['name'] ?? '', ''), ['size' => 9]);
        $witTable->addCell(2200)->addText(safeStr($w['phone'] ?? '', ''), ['size' => 9]);
        $witTable->addCell(2200)->addText(safeStr($w['id_card'] ?? '', ''), ['size' => 9]);
        $witTable->addCell(2700)->addText('_________________', ['size' => 9]);
    }
    $section->addTextBreak();
}

// ============================================================
// SIGNATURES
// ============================================================
$section->addTextBreak(2);
$sigTable = $section->addTable(['borderSize' => 0]);
$sigTable->addRow();
$c1 = $sigTable->addCell(5000);
$c1->addText('______________________________');
$c1->addText('Tenant Signature', ['bold' => true, 'size' => 9], ['alignment' => Jc::CENTER]);
$c1->addText(safeStr($lease['tenant_name'], ''), ['size' => 9, 'bold' => true], ['alignment' => Jc::CENTER]);
$sigTable->addCell(500);
$c2 = $sigTable->addCell(5000);
$c2->addText('______________________________');
$c2->addText('Manager Signature', ['bold' => true, 'size' => 9], ['alignment' => Jc::CENTER]);
$c2->addText($orgName, ['size' => 9, 'bold' => true], ['alignment' => Jc::CENTER]);

// OUTPUT
$filename = 'Lease_' . $lease_id . '.docx';
$tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('lease_') . '.docx';
$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save($tempFile);
while (ob_get_level())
    ob_end_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($tempFile));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
readfile($tempFile);
@unlink($tempFile);
exit;
