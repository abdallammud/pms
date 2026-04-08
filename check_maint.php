<?php
chdir(__DIR__ . '/app');
require_once('init.php');
$tables = ['maintenance_requests', 'maintenance_assignments', 'payments_received'];
foreach ($tables as $t) {
    echo "--- $t ---\n";
    $res = $conn->query("DESCRIBE $t");
    while ($row = $res->fetch_assoc())
        echo $row['Field'] . "\n";
}
