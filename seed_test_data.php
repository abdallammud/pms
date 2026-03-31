<?php
require_once('app/db.php');

$org_id = 1; // Assuming default org_id is 1

// 1. Property
$conn->query("INSERT IGNORE INTO property_types (id, type_name) VALUES (1, 'Apartment')");
$conn->query("INSERT IGNORE INTO properties (id, org_id, name, type_id, address, city) VALUES (1, $org_id, 'Test Mansion', 1, '123 Test St', 'Mogadishu')");

// 2. Unit
$conn->query("INSERT IGNORE INTO units (id, property_id, unit_number, unit_type, rent_amount, status) VALUES (1, 1, '101', 'Bedroom', 500.00, 'occupied')");

// 3. Tenant
$conn->query("INSERT IGNORE INTO tenants (id, full_name, phone, email, status) VALUES (1, 'Test Tenant', '+252615555555', 'tenant@test.com', 'active')");

// 4. Lease
$conn->query("INSERT IGNORE INTO leases (id, tenant_id, unit_id, property_id, start_date, end_date, monthly_rent, status) VALUES (1, 1, 1, 1, '2026-01-01', '2026-12-31', 500.00, 'active')");

// 5. Invoice
$conn->query("INSERT IGNORE INTO invoices (id, invoice_number, lease_id, invoice_date, due_date, amount, status) VALUES (1, 'INV-001', 1, '2026-03-01', '2026-03-10', 500.00, 'unpaid')");

// 6. Payment
$conn->query("INSERT IGNORE INTO payments_received (id, receipt_number, invoice_id, amount_paid, payment_method, received_date) VALUES (1, 'RCT-001', 1, 100.00, 'cash', '2026-03-05')");

echo "Test data seeded successfully.\n";
?>