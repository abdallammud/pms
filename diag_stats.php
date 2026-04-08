<?php
// Test stats endpoints
$endpoints = [
    'units' => 'app/property_controller.php?action=get_unit_stats',
    'tenants' => 'app/tenant_controller.php?action=get_tenant_stats',
    'leases' => 'app/lease_controller.php?action=get_lease_stats',
    'invoices' => 'app/invoice_controller.php?action=get_invoice_stats',
    'payments' => 'app/receipt_controller.php?action=get_payment_stats',
    'expenses' => 'app/expense_controller.php?action=get_expense_stats',
    'maintenance' => 'app/maintenance_controller.php?action=get_maintenance_stats'
];

// Mock session/auth for current_org_id
$_SESSION['org_id'] = 1;
$_SESSION['is_logged_in'] = true;

foreach ($endpoints as $page => $url) {
    echo "Testing $page: $url\n";
    // Since we are running via CLI or on the same server, we can't easily mock the session for an HTTP GET.
    // Instead, I'll just check if the file exists and if I can manually call the functions.
}

// Let's actually try to INCLUDE and call them if possible, but they are procedural with global $conn.
echo "Manually checking some controllers...\n";
require_once 'app/init.php';

function test_func($file, $func)
{
    try {
        require_once $file;
        if (function_exists($func)) {
            echo "SUCCESS: $func exists in $file\n";
        } else {
            echo "ERROR: $func NOT FOUND in $file\n";
        }
    } catch (Throwable $e) {
        echo "CATCH: " . $e->getMessage() . "\n";
    }
}

test_func('app/property_controller.php', 'get_unit_stats');
test_func('app/tenant_controller.php', 'get_tenant_stats');
test_func('app/lease_controller.php', 'get_lease_stats');
test_func('app/invoice_controller.php', 'get_invoice_stats');
test_func('app/maintenance_controller.php', 'get_maintenance_stats');
test_func('app/receipt_controller.php', 'get_payment_stats');
test_func('app/expense_controller.php', 'get_expense_stats');
?>