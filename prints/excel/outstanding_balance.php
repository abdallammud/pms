<?php
/**
 * Outstanding Balance Report - Excel Export
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

require_once('./prints/excel/_branding.php'); // sets $logoPath and $brandColorHex

$startDate = $_GET['startDate'] ?? date('Y-m-01');
$endDate = $_GET['endDate'] ?? date('Y-m-d');
$propertyId = $_GET['property_id'] ?? null;

require_once('./app/report_controller.php');
$report = new ReportController();
$data = $report->getOutstandingBalanceData([
    'startDate' => $startDate,
    'endDate' => $endDate,
    'property_id' => $propertyId
]);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Outstanding Balance');

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
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $brandColorHex]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $brandColorHex]]]
];

$dataStyle = [
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
];

$footerStyle = [
    'font' => ['bold' => true, 'size' => 11],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FA']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $brandColorHex]]]
];

// Title (Right-aligned)
$sheet->mergeCells('C1:G1');
$sheet->setCellValue('C1', 'Outstanding Balance Report');
$sheet->getStyle('C1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => $brandColorHex]],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_RIGHT,
        'vertical' => Alignment::VERTICAL_CENTER
    ]
]);
$sheet->getRowDimension(1)->setRowHeight(85);

// Date range (Right-aligned)
$sheet->mergeCells('C2:G2');
$sheet->setCellValue('C2', "Period: " . date('M d, Y', strtotime($startDate)) . " - " . date('M d, Y', strtotime($endDate)));
$sheet->getStyle('C2')->applyFromArray([
    'font' => ['italic' => true, 'color' => ['rgb' => '6C757D']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
]);
$sheet->getRowDimension(2)->setRowHeight(20);

$sheet->getRowDimension(3)->setRowHeight(10);

// Headers
$headers = ['Invoice #', 'Due Date', 'Tenant', 'Property', 'Unit', 'Amount', 'Status'];
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '4', $header);
    $col++;
}
$sheet->getStyle('A4:G4')->applyFromArray($headerStyle);
$sheet->getRowDimension(4)->setRowHeight(25);

$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(25);
$sheet->getColumnDimension('D')->setWidth(25);
$sheet->getColumnDimension('E')->setWidth(12);
$sheet->getColumnDimension('F')->setWidth(15);
$sheet->getColumnDimension('G')->setWidth(12);

$row = 5;
$totalAmount = 0;
foreach ($data as $item) {
    $sheet->setCellValue('A' . $row, $item['invoice_number']);
    $sheet->setCellValue('B' . $row, date('M d, Y', strtotime($item['due_date'])));
    $sheet->setCellValue('C' . $row, $item['tenant_name']);
    $sheet->setCellValue('D' . $row, $item['property_name']);
    $sheet->setCellValue('E' . $row, $item['unit_number']);
    $sheet->setCellValue('F' . $row, $item['amount']);
    $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');
    $sheet->setCellValue('G' . $row, ucfirst($item['status']));
    $totalAmount += $item['amount'];
    $row++;
}

if ($row > 5) {
    $sheet->getStyle('A5:G' . ($row - 1))->applyFromArray($dataStyle);
}

// Footer
$sheet->mergeCells('A' . $row . ':E' . $row);
$sheet->setCellValue('A' . $row, 'Total Outstanding:');
$sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->setCellValue('F' . $row, $totalAmount);
$sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');
$sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($footerStyle);
$sheet->getRowDimension($row)->setRowHeight(25);

$sheet->freezePane('A5');

$filename = 'Outstanding_Balance_' . date('Y-m-d') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
