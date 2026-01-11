<?php
/**
 * Maintenance Report - Excel Export
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

while (ob_get_level()) {
    ob_end_clean();
}

// Get logo path
require_once('./app/db.php');
$logoQuery = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'logo_path'");
$logoRow = $logoQuery ? $logoQuery->fetch_assoc() : null;
$logoPath = ($logoRow && !empty($logoRow['setting_value'])) ? $logoRow['setting_value'] : 'public/images/logo.png';
$logoPath = realpath($logoPath);

$startDate = $_GET['startDate'] ?? date('Y-m-01');
$endDate = $_GET['endDate'] ?? date('Y-m-d');
$propertyId = $_GET['property_id'] ?? null;

require_once('./app/report_controller.php');
$report = new ReportController();
$data = $report->getMaintenanceReportData([
    'startDate' => $startDate,
    'endDate' => $endDate,
    'property_id' => $propertyId
]);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Maintenance Report');

// Add Logo
if ($logoPath && file_exists($logoPath)) {
    $drawing = new Drawing();
    $drawing->setName('Logo');
    $drawing->setPath($logoPath);
    $drawing->setHeight(100);
    $drawing->setCoordinates('A1');
    $drawing->setOffsetX(5);
    $drawing->setOffsetY(5);
    $drawing->setWorksheet($sheet);
}

$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4E73DF']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '4E73DF']]]
];

$dataStyle = [
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
];

// Title (Right-aligned)
$sheet->mergeCells('C1:G1');
$sheet->setCellValue('C1', 'Maintenance Report');
$sheet->getStyle('C1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '4E73DF']],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_RIGHT,
        'vertical' => Alignment::VERTICAL_CENTER
    ]
]);
$sheet->getRowDimension(1)->setRowHeight(85);

$sheet->mergeCells('C2:G2');
$sheet->setCellValue('C2', "Period: " . date('M d, Y', strtotime($startDate)) . " - " . date('M d, Y', strtotime($endDate)));
$sheet->getStyle('C2')->applyFromArray([
    'font' => ['italic' => true, 'color' => ['rgb' => '6C757D']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
]);
$sheet->getRowDimension(2)->setRowHeight(20);

$sheet->getRowDimension(3)->setRowHeight(10);

// Headers
$headers = ['Ref #', 'Date', 'Property', 'Unit', 'Priority', 'Status', 'Description'];
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '4', $header);
    $col++;
}
$sheet->getStyle('A4:G4')->applyFromArray($headerStyle);
$sheet->getRowDimension(4)->setRowHeight(25);

$sheet->getColumnDimension('A')->setWidth(12);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(25);
$sheet->getColumnDimension('D')->setWidth(10);
$sheet->getColumnDimension('E')->setWidth(12);
$sheet->getColumnDimension('F')->setWidth(12);
$sheet->getColumnDimension('G')->setWidth(40);

$row = 5;
foreach ($data as $item) {
    $sheet->setCellValue('A' . $row, $item['reference_number']);
    $sheet->setCellValue('B' . $row, date('M d, Y', strtotime($item['created_at'])));
    $sheet->setCellValue('C' . $row, $item['property_name']);
    $sheet->setCellValue('D' . $row, $item['unit_number'] ?: 'N/A');
    $sheet->setCellValue('E' . $row, ucfirst($item['priority']));
    $sheet->setCellValue('F' . $row, str_replace('_', ' ', ucfirst($item['status'])));
    $sheet->setCellValue('G' . $row, $item['description']);
    $row++;
}

if ($row > 5) {
    $sheet->getStyle('A5:G' . ($row - 1))->applyFromArray($dataStyle);
}

$sheet->freezePane('A5');

$filename = 'Maintenance_Report_' . date('Y-m-d') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
