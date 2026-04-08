<?php
chdir(__DIR__ . '/app');
require_once('init.php');
$tables = ['properties', 'units', 'tenants', 'guarantors', 'leases', 'invoices', 'payments_received', 'expenses', 'maintenance_requests', 'maintenance_assignments', 'charge_types', 'utility_readings', 'system_settings', 'users', 'roles'];

$results = [];
foreach ($tables as $t) {
    $res = $conn->query("DESCRIBE $t");
    $cols = [];
    while ($row = $res->fetch_assoc())
        $cols[] = $row['Field'];

    $missing = [];
    if (!in_array('updated_by', $cols))
        $missing[] = 'updated_by';
    if (!in_array('updated_at', $cols))
        $missing[] = 'updated_at';

    if (!empty($missing))
        $results[$t] = $missing;
}
echo json_encode($results);
