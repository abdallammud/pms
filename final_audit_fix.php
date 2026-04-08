<?php
chdir(__DIR__ . '/app');
require_once('init.php');

$tables = [
    'maintenance_requests',
    'maintenance_assignments',
    'payments_received',
    'guarantors',
    'charge_types',
    'utility_readings',
    'properties',
    'units',
    'tenants',
    'leases',
    'invoices',
    'expenses',
    'system_settings',
    'users',
    'roles'
];

foreach ($tables as $t) {
    // Check columns
    $res = $conn->query("DESCRIBE $t");
    if (!$res)
        continue;
    $cols = [];
    while ($row = $res->fetch_assoc())
        $cols[] = $row['Field'];

    // Add updated_by if missing
    if (!in_array('updated_by', $cols)) {
        echo "Adding updated_by to $t... ";
        $conn->query("ALTER TABLE $t ADD COLUMN updated_by INT NULL AFTER created_by");
        $cols[] = 'updated_by';
        echo "OK\n";
    }

    // Add updated_at if missing
    if (!in_array('updated_at', $cols)) {
        echo "Adding updated_at to $t... ";
        $conn->query("ALTER TABLE $t ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL AFTER updated_by");
        echo "OK\n";
    }
}
echo "All tables processed.\n";
