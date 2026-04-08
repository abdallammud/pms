<?php
chdir(__DIR__ . '/app');
require_once('init.php');

$conn->query("SET FOREIGN_KEY_CHECKS = 0");

$tables = [
    'invoice_items',
    'payments_received',
    'invoices',
    'expenses',
    'maintenance_assignments',
    'maintenance_requests',
    'leases',
    'tenants',
    'units',
    'properties',
    'organizations',
    'roles',
    'user_roles',
    'users',
    'role_permissions',
    'system_settings',
    'charge_types'
];

foreach ($tables as $t) {
    if (!$conn->query("DELETE FROM `$t`")) {
        echo "Error deleting $t: " . $conn->error . "\n";
    }
    $conn->query("ALTER TABLE `$t` AUTO_INCREMENT = 1");
}

$conn->query("SET FOREIGN_KEY_CHECKS = 1");
echo "Cleaned all tables.\n";
