<?php
require_once 'app/init.php';
$conn = $GLOBALS['conn'];

function dumpTable($conn, $table)
{
    echo "--- $table ---\n";
    $res = $conn->query("SELECT * FROM `$table` LIMIT 20");
    if (!$res) {
        echo "Error querying $table: " . $conn->error . "\n";
        return;
    }
    while ($row = $res->fetch_assoc()) {
        echo json_encode($row, JSON_PRETTY_PRINT) . "\n";
    }
}

dumpTable($conn, 'organizations');
dumpTable($conn, 'users');

$res = $conn->query("SELECT setting_key, setting_value, org_id FROM system_settings LIMIT 10");
echo "--- system_settings ---\n";
while ($row = $res->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
?>