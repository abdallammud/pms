<?php
/**
 * Automated Monthly Rent Invoicing Cron Script
 * Should be run once a day (e.g., at midnight).
 */

require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/lease_controller.php'; // For generate_reference_number

// Check if automated invoicing is enabled
$result = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'auto_invoice_enabled'");
$row = $result->fetch_assoc();
if (!$row || $row['setting_value'] !== 'yes') {
    die("Auto-invoicing is disabled.\n");
}

// Check which day to run
$result = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'auto_invoice_day'");
$row = $result->fetch_assoc();
$target_day = intval($row['setting_value'] ?? 1);

$current_day = intval(date('j'));
if ($current_day !== $target_day) {
    die("Today is not the scheduled day (Configured: $target_day, Today: $current_day).\n");
}

$billing_month = intval(date('n'));
$billing_year = intval(date('Y'));

// Log the start
$stmt = $conn->prepare("INSERT INTO auto_invoice_log (billing_month, billing_year, status, message) VALUES (?, ?, 'in_progress', 'Started automated invoicing cron')");
$stmt->bind_param("ii", $billing_month, $billing_year);
$stmt->execute();
$log_id = $conn->insert_id;

echo "Starting automated invoicing for $billing_month/$billing_year...\n";

// Get active leases with auto-invoice enabled
$leases_query = "SELECT l.id, l.monthly_rent, t.full_name as tenant_name 
                 FROM leases l
                 LEFT JOIN tenants t ON l.tenant_id = t.id
                 WHERE l.status = 'active' AND l.auto_invoice = 1";
$leases_result = $conn->query($leases_query);

$success_count = 0;
$skipped_count = 0;
$error_count = 0;

while ($lease = $leases_result->fetch_assoc()) {
    $lease_id = $lease['id'];
    $tenant_name = $lease['tenant_name'];
    
    // Check if invoice already exists for this period
    $check_stmt = $conn->prepare("SELECT id FROM invoices WHERE lease_id = ? AND invoice_type = 'rent' AND billing_month = ? AND billing_year = ?");
    $check_stmt->bind_param("iii", $lease_id, $billing_month, $billing_year);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        echo "[SKIPPED] Lease #$lease_id ($tenant_name): Invoice already exists.\n";
        $skipped_count++;
        continue;
    }

    // Generate reference number using global function from lease_controller.php
    $reference_number = generate_reference_number('rent_invoice');
    
    $invoice_date = date('Y-m-d');
    $due_date = date('Y-m-d', strtotime('+7 days'));
    $amount = $lease['monthly_rent'];
    $status = 'unpaid';
    $invoice_type = 'rent';
    $notes = "Automated monthly rent invoice for " . date('F Y');

    $insert_stmt = $conn->prepare("INSERT INTO invoices (lease_id, reference_number, amount, invoice_date, due_date, status, invoice_type, billing_month, billing_year, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("issssssiis", $lease_id, $reference_number, $amount, $invoice_date, $due_date, $status, $invoice_type, $billing_month, $billing_year, $notes);
    
    if ($insert_stmt->execute()) {
        echo "[SUCCESS] Lease #$lease_id ($tenant_name): Invoice $reference_number created.\n";
        $success_count++;
    } else {
        echo "[ERROR] Lease #$lease_id ($tenant_name): " . $conn->error . "\n";
        $error_count++;
    }
}

// Update log
$final_status = $error_count > 0 ? 'failed' : 'success';
$message = "Completed: $success_count succeeded, $skipped_count skipped, $error_count failed.";
$update_stmt = $conn->prepare("UPDATE auto_invoice_log SET status = ?, message = ? WHERE id = ?");
$update_stmt->bind_param("ssi", $final_status, $message, $log_id);
$update_stmt->execute();

echo "\nSummary: $message\n";
