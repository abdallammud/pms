<?php
/**
 * Excel Document Generation Entry Point
 * Uses PHPSpreadsheet library from public folder
 */

// ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('./public/phpspreadsheet/Autoloader.php');
\PhpOffice\PhpSpreadsheet\Autoloader::register();

require('./app/init.php');

// Environment Checks
if (!class_exists('ZipArchive')) {
    die("Error: PHP 'zip' extension is not installed on this server. Please enable it.");
}

if (!function_exists('mb_strlen')) {
    die("Error: PHP 'mbstring' extension is not installed on this server. Please enable it.");
}

$tempDir = sys_get_temp_dir();
if (!is_writable($tempDir)) {
    // Try to fallback to a local temp folder if system temp is not writable
    $tempDir = __DIR__ . '/public/uploads/temp';
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
    }
}

if (!authenticate()) {
    ob_end_clean();
    header("Location: " . baseUri() . "/login");
    exit;
}

if (isset($_GET['print'])) {
    $print = $_GET['print'];

    if ($print == 'rent_collection') {
        require('prints/excel/rent_collection.php');
    } elseif ($print == 'unit_occupancy') {
        require('prints/excel/unit_occupancy.php');
    } elseif ($print == 'tenant_report') {
        require('prints/excel/tenant_report.php');
    } elseif ($print == 'outstanding_balance') {
        require('prints/excel/outstanding_balance.php');
    } elseif ($print == 'income_expense') {
        require('prints/excel/income_expense.php');
    } elseif ($print == 'maintenance_report') {
        require('prints/excel/maintenance_report.php');
    } elseif ($print == 'maintenance_expense') {
        require('prints/excel/maintenance_expense.php');
    } else {
        ob_end_clean();
        header("Location: /");
        exit;
    }
} else {
    ob_end_clean();
    header("Location: /");
    exit;
}
