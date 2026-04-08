<?php
/**
 * Invoice Controller
 * Handles CRUD operations for invoices (Rent and Other Charges)
 */
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'get_invoices':
            get_invoices();
            break;
        case 'save_invoice':
            save_invoice();
            break;
        case 'delete_invoice':
            delete_invoice();
            break;
        case 'get_invoice':
            get_invoice();
            break;
        case 'bulk_action':
            bulk_action();
            break;
        case 'generate_rent_invoices_bulk':
            generate_rent_invoices_bulk();
            break;
        case 'get_lease_rent':
            get_lease_rent();
            break;
        case 'check_duplicate_rent':
            check_duplicate_rent();
            break;
        case 'get_invoice_show':
            get_invoice_show();
            break;
        case 'get_invoice_items':
            get_invoice_items();
            break;
        case 'get_invoice_stats':
            get_invoice_stats();
            break;
    }
}

/**
 * Get invoices for DataTable (server-side processing)
 */
function get_invoices()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';
    $type_filter = $_POST['type_filter'] ?? ''; // Optional type filter

    // Base query
    $sql = "SELECT i.*, 
                   t.full_name as tenant_name, 
                   u.unit_number,
                   ct.name as charge_type_name
            FROM invoices i 
            LEFT JOIN leases l ON i.lease_id = l.id 
            LEFT JOIN tenants t ON l.tenant_id = t.id 
            LEFT JOIN units u ON l.unit_id = u.id 
            LEFT JOIN charge_types ct ON i.charge_type_id = ct.id
            WHERE " . tenant_where_clause('i');

    // Type filter
    if (!empty($type_filter)) {
        $type_filter = $conn->real_escape_string($type_filter);
        $sql .= " AND i.invoice_type = '$type_filter'";
    }

    // Search
    if (!empty($search_value)) {
        $search_value = $conn->real_escape_string($search_value);
        $sql .= " AND (t.full_name LIKE '%$search_value%' 
                  OR u.unit_number LIKE '%$search_value%' 
                  OR i.reference_number LIKE '%$search_value%'
                  OR i.status LIKE '%$search_value%'
                  OR ct.name LIKE '%$search_value%')";
    }

    // Total records
    $total_records_res = $conn->query("SELECT COUNT(*) as count FROM invoices i WHERE " . tenant_where_clause('i'));
    $total_records = ($total_records_res) ? $total_records_res->fetch_assoc()['count'] : 0;

    // Total filtered records
    $filtered_sql = preg_replace('/SELECT i\.\*.*?FROM/s', 'SELECT COUNT(*) as count FROM', $sql);
    $filtered_records_res = $conn->query($filtered_sql);
    $filtered_records = 0;
    if ($filtered_records_res) {
        $row = $filtered_records_res->fetch_assoc();
        $filtered_records = $row['count'] ?? 0;
    }

    // Order
    $sql .= " ORDER BY i.id DESC";

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        // Invoice type badge
        $typeBadge = '';
        if ($row['invoice_type'] == 'rent') {
            $typeBadge = '<span class="badge bg-primary">Rent</span>';
        } else {
            $typeBadge = '<span class="badge bg-info">Other</span>';
        }

        // Status badge
        $statusBadge = '';
        if ($row['status'] == 'paid') {
            $statusBadge = '<span class="badge bg-success">Paid</span>';
        } elseif ($row['status'] == 'unpaid') {
            $statusBadge = '<span class="badge bg-danger">Unpaid</span>';
        } elseif ($row['status'] == 'partial') {
            $statusBadge = '<span class="badge bg-warning">Partial</span>';
        }

        // Charge type display
        $chargeType = $row['invoice_type'] == 'rent'
            ? 'Rent'
            : ($row['charge_type_name'] ?? '<span class="text-muted">N/A</span>');

        // Action buttons
        $actionBtn = '<button class="btn btn-sm btn-outline-primary me-1" onclick="viewInvoice(' . $row['id'] . ')" title="View"><i class="bi bi-eye"></i></button>';
        if ($row['status'] != 'paid') {
            $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteInvoice(' . $row['id'] . ')" title="Delete"><i class="bi bi-trash"></i></button>';
        }

        // Billing period display
        $billingPeriod = '';
        if ($row['billing_month'] && $row['billing_year']) {
            $billingPeriod = date('M Y', mktime(0, 0, 0, $row['billing_month'], 1, $row['billing_year']));
        }

        $data[] = [
            'id' => $row['id'],
            'reference_number' => $row['reference_number'] ?? $row['id'],
            'invoice_type' => $typeBadge,
            'charge_type' => $chargeType,
            'tenant_name' => $row['tenant_name'] ?? 'N/A',
            'unit_number' => $row['unit_number'] ?? 'N/A',
            'amount' => number_format($row['amount'], 2),
            'billing_period' => $billingPeriod,
            'due_date' => $row['due_date'],
            'status' => $statusBadge,
            'actions' => $actionBtn
        ];
    }

    echo json_encode([
        "draw" => intval($draw),
        "recordsTotal" => intval($total_records),
        "recordsFiltered" => intval($filtered_records),
        "data" => $data
    ]);
}

/**
 * Save invoice (create or update)
 */
function save_invoice()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $id = $_POST['invoice_id'] ?? '';
    $invoice_type = $_POST['invoice_type'] ?? 'rent';
    $charge_type_id = $_POST['charge_type_id'] ?? null;
    $lease_ids = $_POST['lease_id'] ?? [];
    $due_date = $_POST['due_date'] ?? '';
    $invoice_date = $_POST['invoice_date'] ?? '';
    $billing_month = intval($_POST['billing_month'] ?? date('m'));
    $billing_year = intval($_POST['billing_year'] ?? date('Y'));
    $notes = $_POST['notes'] ?? '';
    $status = $_POST['status'] ?? 'unpaid';
    $org_id = resolve_request_org_id();

    // Parse line items
    $items_desc = $_POST['item_description'] ?? [];
    $items_qty = $_POST['item_qty'] ?? [];
    $items_unit_price = $_POST['item_unit_price'] ?? [];
    $items_tax_rate = $_POST['item_tax_rate'] ?? [];
    $has_items = !empty($items_desc) && is_array($items_desc);

    // Compute totals from items if provided; otherwise use legacy amount field
    $amount = 0;
    if ($has_items) {
        foreach ($items_desc as $i => $desc) {
            if (trim($desc) === '')
                continue;
            $qty = floatval($items_qty[$i] ?? 1);
            $uprc = floatval($items_unit_price[$i] ?? 0);
            $taxr = floatval($items_tax_rate[$i] ?? 0);
            $ltax = round($qty * $uprc * ($taxr / 100), 2);
            $ltot = round($qty * $uprc + $ltax, 2);
            $amount += $ltot;
        }
    } else {
        $amount = floatval($_POST['amount'] ?? 0);
    }

    // Normalize lease_ids to array
    if (!is_array($lease_ids)) {
        $lease_ids = [$lease_ids];
    }

    // Remove empty values
    $lease_ids = array_filter($lease_ids);

    // Validation
    if (empty($lease_ids)) {
        echo json_encode(['error' => true, 'msg' => 'Please select at least one lease.']);
        exit;
    }

    if ($amount <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Amount must be greater than zero.']);
        exit;
    }

    if (empty($due_date) || empty($invoice_date)) {
        echo json_encode(['error' => true, 'msg' => 'Please fill in all required fields.']);
        exit;
    }

    // Validation: Rent invoice must be single lease
    if ($invoice_type == 'rent' && count($lease_ids) > 1) {
        echo json_encode(['error' => true, 'msg' => 'Rent invoices can only be created for one lease at a time.']);
        exit;
    }

    // Validation: Other charge requires charge_type_id
    if ($invoice_type == 'other_charge' && empty($charge_type_id)) {
        echo json_encode(['error' => true, 'msg' => 'Please select a charge type for Other Charges invoice.']);
        exit;
    }

    // Handle null charge_type_id for rent invoices
    if ($invoice_type == 'rent') {
        $charge_type_id = null;
    } else {
        $charge_type_id = intval($charge_type_id);
    }

    // Escape notes
    $notes = $conn->real_escape_string($notes);

    if (empty($id)) {
        // INSERT (Batch for multiple leases)
        $success_count = 0;
        $error_count = 0;
        $skipped_count = 0;
        $errors = [];

        foreach ($lease_ids as $lease_id) {
            $lease_id = intval($lease_id);

            // Check for duplicate rent invoice
            if ($invoice_type == 'rent') {
                if (check_duplicate_rent_invoice($lease_id, $billing_month, $billing_year)) {
                    $skipped_count++;
                    $errors[] = "Lease #$lease_id already has a rent invoice for this billing period.";
                    continue;
                }
            }

            // Verify lease exists and is active
            $lease_check = $conn->query("SELECT id, status FROM leases WHERE id = $lease_id AND " . tenant_where_clause());
            $lease_data = $lease_check->fetch_assoc();

            if (!$lease_data) {
                $error_count++;
                $errors[] = "Lease #$lease_id not found.";
                continue;
            }

            if ($lease_data['status'] != 'active') {
                $skipped_count++;
                $errors[] = "Lease #$lease_id is not active (Status: " . ucfirst($lease_data['status']) . ").";
                continue;
            }

            // Generate reference number based on invoice type
            $ref_module = ($invoice_type == 'rent') ? 'rent_invoice' : 'other_invoice';
            $reference_number = generate_invoice_reference($ref_module);

            // Insert invoice
            $sql = "INSERT INTO invoices (
                        org_id, invoice_type, charge_type_id, lease_id, reference_number,
                        amount, invoice_date, due_date, billing_month, billing_year, 
                        notes, status, created_by
                    ) VALUES (
                        $org_id, '$invoice_type', " . ($charge_type_id === null ? 'NULL' : $charge_type_id) . ", $lease_id, '$reference_number',
                        $amount, '$invoice_date', '$due_date', $billing_month, $billing_year,
                        '$notes', '$status', " . (int) ($_SESSION['user_id'] ?? 0) . "
                    )";

            if ($conn->query($sql)) {
                $new_invoice_id = $conn->insert_id;
                save_invoice_items($conn, $new_invoice_id, $org_id, $items_desc, $items_qty, $items_unit_price, $items_tax_rate, $has_items, $amount);
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Failed to create invoice for Lease #$lease_id: " . $conn->error;
            }
        }

        // Build response message
        $msg = "";
        if ($success_count > 0) {
            $msg = "$success_count invoice(s) created successfully.";
        }
        if ($skipped_count > 0) {
            $msg .= " $skipped_count skipped (duplicate).";
        }
        if ($error_count > 0) {
            $msg .= " $error_count failed.";
        }

        if ($success_count > 0) {
            echo json_encode([
                'error' => false,
                'msg' => $msg,
                'details' => [
                    'success' => $success_count,
                    'skipped' => $skipped_count,
                    'failed' => $error_count,
                    'errors' => $errors
                ]
            ]);
        } else {
            echo json_encode([
                'error' => true,
                'msg' => 'Failed to create invoices. ' . implode(' ', $errors)
            ]);
        }

    } else {
        // UPDATE (Single invoice)
        $lease_id = intval($lease_ids[0] ?? 0);

        if ($lease_id <= 0) {
            echo json_encode(['error' => true, 'msg' => 'Invalid lease ID for update.']);
            exit;
        }

        // Get current invoice data to check type
        $current = $conn->query("SELECT invoice_type FROM invoices WHERE id = $id AND " . tenant_where_clause())->fetch_assoc();
        if (!$current) {
            echo json_encode(['error' => true, 'msg' => 'Invoice not found.']);
            exit;
        }

        // Build update SQL
        $sql = "UPDATE invoices SET 
                    lease_id = $lease_id,
                    amount = $amount,
                    invoice_date = '$invoice_date',
                    due_date = '$due_date',
                    billing_month = $billing_month,
                    billing_year = $billing_year,
                    notes = '$notes',
                    status = '$status'";

        // Only update charge_type_id for other_charge invoices
        if ($current['invoice_type'] == 'other_charge' && $charge_type_id) {
            $sql .= ", charge_type_id = $charge_type_id";
        }

        $sql .= " WHERE id = $id AND " . tenant_where_clause();

        if ($conn->query($sql)) {
            save_invoice_items($conn, intval($id), $org_id, $items_desc, $items_qty, $items_unit_price, $items_tax_rate, $has_items, $amount);
            // Recompute payment status against new items
            update_invoice_status_from_items(intval($id));
            echo json_encode(['error' => false, 'msg' => 'Invoice updated successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error updating invoice: ' . $conn->error]);
        }
    }
}

/**
 * Save invoice line items – replace all existing items then redistribute payments
 */
function save_invoice_items($conn, $invoice_id, $org_id, $items_desc, $items_qty, $items_unit_price, $items_tax_rate, $has_items, $fallback_amount)
{
    if (!$has_items) {
        // Legacy: ensure at least one item exists
        $exists = $conn->query("SELECT id FROM invoice_items WHERE invoice_id = $invoice_id LIMIT 1");
        if ($exists && $exists->num_rows === 0) {
            $desc = $conn->real_escape_string('Invoice Amount');
            $ltot = floatval($fallback_amount);
            $creator_id = (int) ($_SESSION['user_id'] ?? 0);
            $conn->query("INSERT INTO invoice_items (org_id, invoice_id, description, qty, unit_price, tax_rate, tax_amount, line_total, amount_paid, balance, sort_order, created_by)
                          VALUES ($org_id, $invoice_id, '$desc', 1, $ltot, 0, 0, $ltot, 0, $ltot, 1, $creator_id)");
        }
        return;
    }

    // Delete old items (payment_allocations cascade-delete via FK)
    $conn->query("DELETE FROM invoice_items WHERE invoice_id = $invoice_id");

    $sort = 1;
    foreach ($items_desc as $i => $desc) {
        $desc = trim($desc);
        if ($desc === '')
            continue;

        $qty = round(floatval($items_qty[$i] ?? 1), 2);
        $uprc = round(floatval($items_unit_price[$i] ?? 0), 2);
        $taxr = round(floatval($items_tax_rate[$i] ?? 0), 2);
        $ltax = round($qty * $uprc * ($taxr / 100), 2);
        $ltot = round($qty * $uprc + $ltax, 2);

        $desc_esc = $conn->real_escape_string($desc);
        $creator_id = (int) ($_SESSION['user_id'] ?? 0);
        $conn->query("INSERT INTO invoice_items (org_id, invoice_id, description, qty, unit_price, tax_rate, tax_amount, line_total, amount_paid, balance, sort_order, created_by)
                      VALUES ($org_id, $invoice_id, '$desc_esc', $qty, $uprc, $taxr, $ltax, $ltot, 0, $ltot, $sort, $creator_id)");
        $sort++;
    }
}

/**
 * Return invoice items as JSON (for receipt form / invoice show)
 */
function get_invoice_items()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $invoice_id = intval($_GET['invoice_id'] ?? 0);
    if ($invoice_id <= 0) {
        echo json_encode([]);
        exit;
    }

    $rows = $conn->query("
        SELECT ii.*, 
               (ii.line_total - COALESCE(
                   (SELECT SUM(pa.amount) FROM payment_allocations pa WHERE pa.invoice_item_id = ii.id), 0
               )) AS current_balance
        FROM invoice_items ii
        WHERE ii.invoice_id = $invoice_id AND ii.org_id = " . current_org_id() . "
        ORDER BY ii.sort_order, ii.id
    ");

    $items = [];
    while ($r = $rows->fetch_assoc()) {
        $items[] = $r;
    }
    echo json_encode($items);
}

/**
 * Full invoice show data (header + items + payments)
 */
function get_invoice_show()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(404);
        echo json_encode(['error' => true, 'msg' => 'Invoice not found.']);
        exit;
    }

    $row = $conn->query(
        "
        SELECT i.*,
               t.full_name     AS tenant_name,
               t.phone         AS tenant_phone,
               u.unit_number,
               p.name          AS property_name,
               ct.name         AS charge_type_name,
               l.monthly_rent  AS rent_amount
        FROM invoices i
        LEFT JOIN leases l   ON l.id  = i.lease_id
        LEFT JOIN tenants t  ON t.id  = l.tenant_id
        LEFT JOIN units u    ON u.id  = l.unit_id
        LEFT JOIN properties p ON p.id = u.property_id
        LEFT JOIN charge_types ct ON ct.id = i.charge_type_id
        WHERE i.id = $id AND " . tenant_where_clause('i')
    )->fetch_assoc();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => true, 'msg' => 'Invoice not found.']);
        exit;
    }

    // Items
    $items_res = $conn->query("
        SELECT ii.*,
               COALESCE((SELECT SUM(pa.amount) FROM payment_allocations pa WHERE pa.invoice_item_id = ii.id), 0) AS allocated
        FROM invoice_items ii
        WHERE ii.invoice_id = $id
        ORDER BY ii.sort_order, ii.id
    ");
    $items = [];
    while ($ir = $items_res->fetch_assoc()) {
        $ir['item_balance'] = round($ir['line_total'] - $ir['allocated'], 2);
        $items[] = $ir;
    }

    // Payments
    $pmts_res = $conn->query("
        SELECT pr.*, pr.receipt_number
        FROM payments_received pr
        WHERE pr.invoice_id = $id AND " . tenant_where_clause('pr') . "
        ORDER BY pr.received_date ASC, pr.id ASC
    ");
    $payments = [];
    while ($pr = $pmts_res->fetch_assoc()) {
        $payments[] = $pr;
    }

    $row['items'] = $items;
    $row['payments'] = $payments;

    echo json_encode($row);
}

/**
 * Recompute invoice status based on item-level allocations
 */
function update_invoice_status_from_items($invoice_id)
{
    $conn = $GLOBALS['conn'];

    $res = $conn->query("
        SELECT SUM(line_total) AS total,
               SUM(COALESCE((SELECT SUM(pa.amount) FROM payment_allocations pa WHERE pa.invoice_item_id = ii.id), 0)) AS paid
        FROM invoice_items ii
        WHERE ii.invoice_id = $invoice_id
    ")->fetch_assoc();

    $total = floatval($res['total'] ?? 0);
    $paid = floatval($res['paid'] ?? 0);

    if ($total <= 0)
        return;

    if ($paid <= 0) {
        $status = 'unpaid';
    } elseif ($paid >= $total) {
        $status = 'paid';
    } else {
        $status = 'partial';
    }

    $conn->query("UPDATE invoices SET status = '$status', amount = $total WHERE id = $invoice_id");
}

/**
 * Check if duplicate rent invoice exists
 */
function check_duplicate_rent_invoice($lease_id, $billing_month, $billing_year)
{
    $conn = $GLOBALS['conn'];

    $stmt = $conn->prepare("
        SELECT id FROM invoices 
        WHERE lease_id = ? 
        AND org_id = ? 
        AND invoice_type = 'rent' 
        AND billing_month = ? 
        AND billing_year = ?
        LIMIT 1
    ");
    $org_id = resolve_request_org_id();
    $stmt->bind_param("iiii", $lease_id, $org_id, $billing_month, $billing_year);
    $stmt->execute();

    return $stmt->get_result()->num_rows > 0;
}

/**
 * Generate invoice reference number with concurrency protection
 */
function generate_invoice_reference($module)
{
    $conn = $GLOBALS['conn'];
    $org_id = (int) resolve_request_org_id();

    $conn->begin_transaction();

    try {
        // Lock the settings row for update
        $result = $conn->query("
            SELECT setting_value FROM system_settings 
            WHERE setting_key = 'transaction_series' 
            AND org_id = $org_id
            FOR UPDATE
        ");
        $row = $result->fetch_assoc();

        $current_year = date('Y');

        if ($row && !empty($row['setting_value'])) {
            $series = json_decode($row['setting_value'], true);

            // Check if the module exists in series
            if (!isset($series[$module])) {
                // Fallback to legacy 'invoice' key or create new
                if ($module == 'rent_invoice' && isset($series['invoice'])) {
                    $series[$module] = $series['invoice'];
                    $series[$module]['prefix'] = 'RNT-';
                } elseif ($module == 'other_invoice' && isset($series['invoice'])) {
                    $series[$module] = $series['invoice'];
                    $series[$module]['prefix'] = 'CHR-';
                    $series[$module]['current_number'] = 0;
                } else {
                    $series[$module] = [
                        'prefix' => ($module == 'rent_invoice') ? 'RNT-' : 'CHR-',
                        'suffix' => '',
                        'starting_number' => '00001',
                        'current_number' => 0,
                        'include_year' => true,
                        'auto_reset' => true,
                        'last_reset_year' => $current_year
                    ];
                }
            }

            $config = $series[$module];
            $prefix = $config['prefix'] ?? '';
            $suffix = $config['suffix'] ?? '';
            $starting = intval($config['starting_number'] ?? 1);
            $current = intval($config['current_number'] ?? 0);
            $include_year = $config['include_year'] ?? true;
            $auto_reset = $config['auto_reset'] ?? false;
            $last_reset_year = intval($config['last_reset_year'] ?? $current_year);

            // Check for yearly auto-reset
            if ($auto_reset && $last_reset_year < $current_year) {
                $current = 0;
                $series[$module]['last_reset_year'] = $current_year;
            }

            // Calculate next number
            $next_number = ($current > 0) ? $current + 1 : $starting;

            // Update current number
            $series[$module]['current_number'] = $next_number;

            // Save updated series
            $updated_series = $conn->real_escape_string(json_encode($series));
            $conn->query("UPDATE system_settings SET setting_value = '$updated_series' WHERE setting_key = 'transaction_series' AND org_id = $org_id");

            // Format number
            $formatted_number = str_pad($next_number, 5, '0', STR_PAD_LEFT);

            // Build reference with optional year
            if ($include_year) {
                $reference = $prefix . $current_year . '-' . $formatted_number . $suffix;
            } else {
                $reference = $prefix . $formatted_number . $suffix;
            }

            $conn->commit();
            return $reference;
        }

        $conn->commit();

    } catch (Exception $e) {
        $conn->rollback();
    }

    // Fallback
    return strtoupper(substr($module, 0, 3)) . '-' . date('Ymd') . rand(100, 999);
}

/**
 * Delete invoice
 */
function delete_invoice()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid ID.']);
        exit;
    }

    // Check status
    $check = $conn->query("SELECT status FROM invoices WHERE id = $id AND " . tenant_where_clause());
    $inv = $check->fetch_assoc();
    if ($inv && $inv['status'] == 'paid') {
        echo json_encode(['error' => true, 'msg' => 'Paid invoices cannot be deleted.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM invoices WHERE id = ? AND " . tenant_where_clause());
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['error' => false, 'msg' => 'Invoice deleted successfully.']);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Error deleting invoice: ' . $conn->error]);
    }
}

/**
 * Bulk action handler
 */
function bulk_action()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $action_type = $_POST['action_type'] ?? '';
    $ids = $_POST['ids'] ?? [];

    if (empty($action_type) || empty($ids) || !is_array($ids)) {
        echo json_encode(['error' => true, 'msg' => 'Missing required data.']);
        exit;
    }

    if ($action_type == 'delete') {
        $id_list = implode(',', array_map('intval', $ids));

        // Check for paid invoices
        $check = $conn->query("SELECT COUNT(*) as count FROM invoices WHERE id IN ($id_list) AND status = 'paid' AND " . tenant_where_clause());
        if ($check->fetch_assoc()['count'] > 0) {
            echo json_encode(['error' => true, 'msg' => 'One or more selected invoices are Paid and cannot be deleted.']);
            exit;
        }

        $sql = "DELETE FROM invoices WHERE id IN ($id_list) AND " . tenant_where_clause();

        if ($conn->query($sql)) {
            echo json_encode(['error' => false, 'msg' => count($ids) . ' invoice(s) deleted successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error performing bulk deletion: ' . $conn->error]);
        }
    } else {
        echo json_encode(['error' => true, 'msg' => 'Invalid action type.']);
    }
}

/**
 * Get single invoice
 */
function get_invoice()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid ID']);
        exit;
    }

    $sql = "SELECT i.*, ct.name as charge_type_name 
            FROM invoices i 
            LEFT JOIN charge_types ct ON i.charge_type_id = ct.id
            WHERE i.id = ? AND " . tenant_where_clause('i');

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        echo json_encode(['error' => false, 'data' => $result]);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Invoice not found.']);
    }
}

/**
 * Generate rent invoices in bulk (for auto-generation)
 */
function generate_rent_invoices_bulk()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];
    $org_id = resolve_request_org_id();

    $lease_ids = $_POST['lease_ids'] ?? [];
    $billing_month = intval($_POST['billing_month'] ?? date('m'));
    $billing_year = intval($_POST['billing_year'] ?? date('Y'));

    if (empty($lease_ids) || !is_array($lease_ids)) {
        echo json_encode(['error' => true, 'msg' => 'No leases provided.']);
        exit;
    }

    $results = [];
    $success_count = 0;
    $skipped_count = 0;
    $failed_count = 0;

    foreach ($lease_ids as $lease_id) {
        $lease_id = intval($lease_id);
        $result = ['lease_id' => $lease_id, 'status' => '', 'message' => ''];

        // Check if lease exists and get rent amount
        $lease = $conn->query("
            SELECT l.id, l.monthly_rent, l.status, l.auto_invoice, t.full_name
            FROM leases l
            LEFT JOIN tenants t ON l.tenant_id = t.id
            WHERE l.id = $lease_id AND " . tenant_where_clause('l') . "
        ")->fetch_assoc();

        if (!$lease) {
            $result['status'] = 'failed';
            $result['message'] = 'Lease not found';
            $failed_count++;
            $results[] = $result;
            continue;
        }

        // Check lease is active
        if ($lease['status'] != 'active') {
            $result['status'] = 'skipped';
            $result['message'] = 'Lease is not active';
            $skipped_count++;
            $results[] = $result;
            continue;
        }

        // Check for duplicate
        if (check_duplicate_rent_invoice($lease_id, $billing_month, $billing_year)) {
            $result['status'] = 'skipped';
            $result['message'] = 'Already invoiced for this period';
            $skipped_count++;
            $results[] = $result;
            continue;
        }

        // Generate invoice
        $amount = floatval($lease['monthly_rent']);
        $invoice_date = date('Y-m-d');

        // Due date is first of the billing month
        $due_date = sprintf('%04d-%02d-01', $billing_year, $billing_month);

        $reference_number = generate_invoice_reference('rent_invoice');

        $sql = "INSERT INTO invoices (
                    org_id, invoice_type, lease_id, reference_number, amount, 
                    invoice_date, due_date, billing_month, billing_year, status
                ) VALUES (
                    $org_id, 'rent', $lease_id, '$reference_number', $amount,
                    '$invoice_date', '$due_date', $billing_month, $billing_year, 'unpaid'
                )";

        if ($conn->query($sql)) {
            $new_inv_id = $conn->insert_id;
            $conn->query("INSERT INTO invoice_items (org_id, invoice_id, description, qty, unit_price, tax_rate, tax_amount, line_total, amount_paid, balance, sort_order)
                          VALUES ($org_id, $new_inv_id, 'Rent Charge', 1, $amount, 0, 0, $amount, 0, $amount, 1)");
            $result['status'] = 'success';
            $result['message'] = 'Invoice created: ' . $reference_number;
            $result['invoice_id'] = $new_inv_id;
            $result['reference_number'] = $reference_number;
            $result['tenant_name'] = $lease['full_name'];
            $success_count++;
        } else {
            $result['status'] = 'failed';
            $result['message'] = 'Database error: ' . $conn->error;
            $failed_count++;
        }

        $results[] = $result;
    }

    echo json_encode([
        'error' => false,
        'msg' => "$success_count generated, $skipped_count skipped, $failed_count failed",
        'summary' => [
            'success' => $success_count,
            'skipped' => $skipped_count,
            'failed' => $failed_count,
            'total' => count($lease_ids)
        ],
        'results' => $results
    ]);
}

/**
 * Get lease rent amount (for auto-fill)
 */
function get_lease_rent()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $lease_id = isset($_GET['lease_id']) ? intval($_GET['lease_id']) : 0;

    if ($lease_id <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid lease ID.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT monthly_rent FROM leases WHERE id = ? AND " . tenant_where_clause());
    $stmt->bind_param("i", $lease_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        echo json_encode(['error' => false, 'rent' => $result['monthly_rent']]);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Lease not found.']);
    }
}

/**
 * Check for duplicate rent invoice (AJAX endpoint)
 */
function check_duplicate_rent()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $lease_id = intval($_GET['lease_id'] ?? 0);
    $billing_month = intval($_GET['billing_month'] ?? date('m'));
    $billing_year = intval($_GET['billing_year'] ?? date('Y'));

    if ($lease_id <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid lease ID.']);
        exit;
    }

    $is_duplicate = check_duplicate_rent_invoice($lease_id, $billing_month, $billing_year);

    echo json_encode([
        'error' => false,
        'is_duplicate' => $is_duplicate,
        'msg' => $is_duplicate ? 'A rent invoice already exists for this lease and billing period.' : ''
    ]);
}

function get_invoice_stats()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];
    $org_where = tenant_where_clause();

    $total = $conn->query("SELECT COUNT(*) as count FROM invoices WHERE $org_where")->fetch_assoc()['count'] ?? 0;
    $unpaid = $conn->query("SELECT COUNT(*) as count FROM invoices WHERE status = 'unpaid' AND $org_where")->fetch_assoc()['count'] ?? 0;
    $overdue = $conn->query("SELECT COUNT(*) as count FROM invoices WHERE status = 'unpaid' AND due_date < CURDATE() AND $org_where")->fetch_assoc()['count'] ?? 0;

    echo json_encode([
        'error' => false,
        'stats' => [
            number_format($total),
            number_format($unpaid),
            number_format($overdue)
        ]
    ]);
}
