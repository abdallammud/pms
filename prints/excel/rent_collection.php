<?php
/**
 * Rent Collection Report - Excel Export
 * Generates Excel file for rent collection data
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// Discard any buffered output
while (ob_get_level()) {
    ob_end_clean();
}

require_once('./prints/excel/_branding.php'); // sets $logoPath and $brandColorHex

// Get filters from request
$startDate = $_GET['startDate'] ?? date('Y-m-01');
$endDate = $_GET['endDate'] ?? date('Y-m-d');
$propertyId = $_GET['property_id'] ?? null;

require_once('./app/report_controller.php');
$report = new ReportController();
$data = $report->getRentCollectionData([
    'startDate' => $startDate,
    'endDate' => $endDate,
    'property_id' => $propertyId
]);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Rent Collection');

// Add Logo
if ($logoPath && file_exists($logoPath)) {
    $drawing = new Drawing();
    $drawing->setName('Logo');
    $drawing->setDescription('Company Logo');
    $drawing->setPath($logoPath);
    $drawing->setHeight(100);
    $drawing->setCoordinates('A1');
    $drawing->setOffsetX(5);
    $drawing->setOffsetY(5);
    $drawing->setWorksheet($sheet);
}

// Header style
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size' => 11,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => $brandColorHex]
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => $brandColorHex]
        ]
    ]
];

// Data cell style
$dataStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'DDDDDD']
        ]
    ],
    'alignment' => [
        'vertical' => Alignment::VERTICAL_CENTER,
    ]
];

// Footer style
$footerStyle = [
    'font' => [
        'bold' => true,
        'size' => 11,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'F8F9FA']
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => $brandColorHex]
        ]
    ]
];

// Title row (Right-aligned)
$sheet->mergeCells('C1:F1');
$sheet->setCellValue('C1', 'Rent Collection Report');
$sheet->getStyle('C1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => $brandColorHex]],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_RIGHT,
        'vertical' => Alignment::VERTICAL_CENTER
    ]
]);
$sheet->getRowDimension(1)->setRowHeight(85);

// Date range row (Right-aligned)
$sheet->mergeCells('C2:F2');
$displayStart = date('M d, Y', strtotime($startDate));
$displayEnd = date('M d, Y', strtotime($endDate));
$sheet->setCellValue('C2', "Period: {$displayStart} - {$displayEnd}");
$sheet->getStyle('C2')->applyFromArray([
    'font' => ['italic' => true, 'color' => ['rgb' => '6C757D']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
]);
$sheet->getRowDimension(2)->setRowHeight(20);

// Blank row
$sheet->getRowDimension(3)->setRowHeight(10);

// Header row (Row 4)
$headers = ['Date', 'Receipt #', 'Tenant', 'Property', 'Unit', 'Amount'];
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '4', $header);
    $col++;
}
$sheet->getStyle('A4:F4')->applyFromArray($headerStyle);
$sheet->getRowDimension(4)->setRowHeight(25);

// Column widths
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(25);
$sheet->getColumnDimension('D')->setWidth(25);
$sheet->getColumnDimension('E')->setWidth(12);
$sheet->getColumnDimension('F')->setWidth(15);

// Data rows
$row = 5;
$totalAmount = 0;
foreach ($data as $item) {
    $sheet->setCellValue('A' . $row, date('M d, Y', strtotime($item['received_date'])));
    $sheet->setCellValue('B' . $row, $item['receipt_number']);
    $sheet->setCellValue('C' . $row, $item['tenant_name']);
    $sheet->setCellValue('D' . $row, $item['property_name']);
    $sheet->setCellValue('E' . $row, $item['unit_number']);
    $sheet->setCellValue('F' . $row, $item['amount_paid']);
    $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');
    $totalAmount += $item['amount_paid'];
    $row++;
}

if ($row > 5) {
    $sheet->getStyle('A5:F' . ($row - 1))->applyFromArray($dataStyle);
}

// Footer total row
$sheet->mergeCells('A' . $row . ':E' . $row);
$sheet->setCellValue('A' . $row, 'Total Collected:');
$sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->setCellValue('F' . $row, $totalAmount);
$sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');
$sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray($footerStyle);
$sheet->getRowDimension($row)->setRowHeight(25);

// Freeze header row
$sheet->freezePane('A5');

$filename = 'Rent_Collection_' . date('Y-m-d') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: cache, must-revalidate');
header('Pragma: public');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>