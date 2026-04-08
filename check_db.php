<?php
chdir('d:/xampp/htdocs/FileZillaFTP/source/diff/bu/pms/app');
$_SERVER['SERVER_PORT'] = '80'; // Mock for CLI
$_SERVER['HTTP_HOST'] = 'localhost'; // Mock for CLI
$_SERVER['SCRIPT_NAME'] = '/index.php'; // Mock for CLI
mysqli_report(MYSQLI_REPORT_OFF);
require_once('init.php');

$r = $conn->query("SELECT COUNT(*) from properties")->fetch_row();
echo "Property count: " . $r[0] . "\n";
