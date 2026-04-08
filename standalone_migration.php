<?php
$host = 'localhost';
$db_name = 'test_edurdur_1';
$username = 'root';
$password = '';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($host, $username, $password, $db_name);
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

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
    echo "Processing $t... ";
    $res = $conn->query("DESCRIBE `$t` ");
    if (!$res) {
        echo "Table $t not found.\n";
        continue;
    }
    $cols = [];
    while ($row = $res->fetch_assoc())
        $cols[] = $row['Field'];

    if (!in_array('updated_by', $cols)) {
        if ($conn->query("ALTER TABLE `$t` ADD COLUMN updated_by INT NULL")) {
            echo "Added updated_by. ";
        } else {
            echo "Error adding updated_by: " . $conn->error . " ";
        }
    }
    if (!in_array('updated_at', $cols)) {
        if ($conn->query("ALTER TABLE `$t` ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL")) {
            echo "Added updated_at. ";
        } else {
            echo "Error adding updated_at: " . $conn->error . " ";
        }
    }
    echo "Done.\n";
}
echo "Migration complete.\n";
