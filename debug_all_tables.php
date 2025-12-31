<?php
require_once 'app/db.php';
$tables = ['properties', 'units', 'leases', 'tenants', 'invoices', 'payments_received', 'expenses', 'maintenance_requests'];
foreach ($tables as $table) {
    echo "--- $table ---<br>";
    $res = $mysqli->query("DESCRIBE `$table`");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            echo "{$row['Field']} ({$row['Type']})<br>";
        }
    } else {
        echo "Table does not exist.<br>";
    }
}
?>