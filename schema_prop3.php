<?php
chdir('d:/xampp/htdocs/FileZillaFTP/source/diff/bu/pms/app');
$_SERVER['SERVER_PORT'] = '80'; // Mock for CLI
$_SERVER['HTTP_HOST'] = 'localhost'; // Mock for CLI
$_SERVER['SCRIPT_NAME'] = '/index.php'; // Mock for CLI
mysqli_report(MYSQLI_REPORT_OFF);
require_once('init.php');

$tables = ['properties', 'attachments', 'property_images'];
$out = [];
foreach ($tables as $t) {
    if ($res = $conn->query("DESCRIBE $t")) {
        $cols = [];
        while ($r = $res->fetch_assoc())
            $cols[] = $r['Field'];
        $out[$t] = $cols;
    }
}
file_put_contents('../schema_out.json', json_encode($out, JSON_PRETTY_PRINT));
