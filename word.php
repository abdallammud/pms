<?php
/**
 * Word Document Generation Entry Point
 * Uses PHPWord library from public folder
 */

// START OUTPUT BUFFERING IMMEDIATELY - capture ANY stray output
ob_start();

// Suppress display errors completely
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// PHPWord Autoloader
require('./public/phpword/PHPWord-master/src/PhpWord/Autoloader.php');
\PhpOffice\PhpWord\Autoloader::register();

// App initialization
require('./app/init.php');

// Authentication check
if (!authenticate()) {
    ob_end_clean();
    header("Location: " . baseUri() . "/login");
    exit;
}

// Route to appropriate Word template
if (isset($_GET['print'])) {
    if ($_GET['print'] == 'lease') {
        require('prints/word/lease_word.php');
    }
} else {
    ob_end_clean();
    header("Location: /");
    exit;
}