<?php
require('d:/xampp/htdocs/FileZillaFTP/source/diff/bu/pms/app/init.php');

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
file_put_contents('d:/xampp/htdocs/FileZillaFTP/source/diff/bu/pms/schema_out.json', json_encode($out, JSON_PRETTY_PRINT));
