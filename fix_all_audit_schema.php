<?php
chdir(__DIR__ . '/app');
require_once('init.php');

$tables = [
    'maintenance_requests',
    'maintenance_assignments',
    'payments_received',
    'guarantors',
    'charge_types',
    'utility_readings'
];

foreach ($tables as $t) {
    echo "Processing $t... ";
    $res = $conn->query("DESCRIBE $t");
    $cols = [];
    while ($row = $res->fetch_assoc())
        $cols[] = $row['Field'];

    if (in_array('updated_by', $cols) && !in_array('updated_at', $cols)) {
        $sql = "ALTER TABLE $t ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL AFTER updated_by";
        if ($conn->query($sql)) {
            echo "SUCCESS (Added updated_at)\n";
        } else {
            echo "ERROR: " . $conn->error . "\n";
        }
    } else {
        echo "SKIPPED (Already has updated_at or missing updated_by)\n";
    }
}
