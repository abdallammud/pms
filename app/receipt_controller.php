<?php
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

        if ($action == 'get_receipts') {
        get_receipts();
    } elseif ($action == 'save_receipt') {
        save_receipt();
    } elseif ($action == 'delete_receipt') {
        delete_receipt();
    } elseif ($action == 'get_receipt') {
        get_receipt();
    } elseif ($action == 'bulk_action') {
        bulk_action();
    } elseif ($action == 'get_payment_show') {
        get_payment_show();
    } elseif ($action == 'get_invoice_balance') {
        get_invoice_balance();
    }
}

function get_receipts()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    // Server-side processing for DataTables
    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    // Base query
    $sql = "SELECT p.*, COALESCE(i.reference_number, i.invoice_number) as invoice_ref, t.full_name as tenant_name 
            FROM payments_received p 
            LEFT JOIN invoices i ON p.invoice_id = i.id 
            LEFT JOIN leases l ON i.lease_id = l.id 
            LEFT JOIN tenants t ON l.tenant_id = t.id 
            WHERE " . tenant_where_clause('p');

    // Search
    if (!empty($search_value)) {
        $search_value = $conn->real_escape_string($search_value);
        $sql .= " AND (t.full_name LIKE '%$search_value%' OR p.payment_method LIKE '%$search_value%' OR p.notes LIKE '%$search_value%')";
    }

    // Total records (before filtering)
    $total_records_res = $conn->query("SELECT COUNT(*) as count FROM payments_received p WHERE " . tenant_where_clause('p'));
    $total_records = ($total_records_res) ? $total_records_res->fetch_assoc()['count'] : 0;

    // Total filtered records
    $filtered_sql = preg_replace('/SELECT\b.*?\bFROM/is', 'SELECT COUNT(*) as count FROM', $sql, 1);
    $filtered_records_res = $conn->query($filtered_sql);
    $filtered_records = 0;
    if ($filtered_records_res) {
        $row = $filtered_records_res->fetch_assoc();
        $filtered_records = $row['count'] ?? 0;
    }

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $actionBtn = '<button class="btn btn-sm btn-outline-primary me-1" onclick="viewPayment(' . $row['id'] . ')" title="View"><i class="bi bi-eye"></i></button>'
                   . '<button class="btn btn-sm btn-danger" onclick="deleteReceipt(' . $row['id'] . ')"><i class="bi bi-trash"></i></button>';

        $paymentMethodBadge = '';
        if ($row['payment_method'] == 'cash') {
            $paymentMethodBadge = '<span class="badge bg-success">Cash</span>';
        } elseif ($row['payment_method'] == 'mobile') {
            $paymentMethodBadge = '<span class="badge bg-info">Mobile</span>';
        } elseif ($row['payment_method'] == 'bank') {
            $paymentMethodBadge = '<span class="badge bg-primary">Bank</span>';
        }

        $data[] = [
            'id_check' => '<input type="checkbox" class="receipt-checkbox" value="' . $row['id'] . '">',
            'receipt_number' => $row['receipt_number'] ?? ('RCT-' . $row['id']),
            'invoice_number' => $row['invoice_ref'],
            'tenant_name' => $row['tenant_name'],
            'amount_paid' => number_format($row['amount_paid'], 2),
            'payment_method' => $paymentMethodBadge,
            'received_date' => $row['received_date'],
            'actions' => $actionBtn
        ];
    }

    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        "draw" => intval($draw),
        "recordsTotal" => intval($total_records),
        "recordsFiltered" => intval($filtered_records),
        "data" => $data
    ]);
}

function save_receipt()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $id            = $_POST['receipt_id']    ?? '';
    $invoice_id    = intval($_POST['invoice_id']    ?? 0);
    $amount_paid   = floatval($_POST['amount_paid'] ?? 0);
    $received_date = $_POST['received_date'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $notes         = $conn->real_escape_string($_POST['notes'] ?? '');
    $org_id        = resolve_request_org_id();

    if ($invoice_id <= 0 || $amount_paid <= 0 || empty($received_date)) {
        echo json_encode(['error' => true, 'msg' => 'Please fill in all required fields.']);
        exit;
    }

    // Validate amount does not exceed invoice balance
    $balance_res = $conn->query("
        SELECT GREATEST(0, SUM(ii.line_total) - COALESCE(SUM(pa.amount),0)) AS balance
        FROM invoice_items ii
        LEFT JOIN payment_allocations pa ON pa.invoice_item_id = ii.id
        WHERE ii.invoice_id = $invoice_id
    ")->fetch_assoc();
    $invoice_balance = floatval($balance_res['balance'] ?? 0);

    if ($amount_paid > $invoice_balance + 0.01) {
        echo json_encode(['error' => true, 'msg' => "Amount paid ($$amount_paid) exceeds invoice balance ($$invoice_balance)."]);
        exit;
    }

    if (empty($id)) {
        // Insert payment
        $receipt_number = generate_reference_number('payment');
        $stmt = $conn->prepare("INSERT INTO payments_received (org_id, receipt_number, invoice_id, amount_paid, received_date, payment_method, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isidsss", $org_id, $receipt_number, $invoice_id, $amount_paid, $received_date, $payment_method, $notes);

        if ($stmt->execute()) {
            $payment_id = $conn->insert_id;
            allocate_payment_fifo($conn, $payment_id, $invoice_id, $amount_paid, $org_id);
            update_invoice_status($invoice_id);
            echo json_encode(['error' => false, 'msg' => 'Payment recorded successfully.', 'receipt_number' => $receipt_number]);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error recording payment: ' . $conn->error]);
        }
    } else {
        $id = intval($id);
        // Revert previous allocations for this payment before re-allocating
        $conn->query("DELETE FROM payment_allocations WHERE payment_id = $id");

        $stmt = $conn->prepare("UPDATE payments_received SET amount_paid=?, received_date=?, payment_method=?, notes=? WHERE id=? AND " . tenant_where_clause());
        $stmt->bind_param("dsssi", $amount_paid, $received_date, $payment_method, $notes, $id);

        if ($stmt->execute()) {
            allocate_payment_fifo($conn, $id, $invoice_id, $amount_paid, $org_id);
            update_invoice_status($invoice_id);
            echo json_encode(['error' => false, 'msg' => 'Payment updated successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error updating payment: ' . $conn->error]);
        }
    }
}

/**
 * FIFO allocation: distribute $amount across invoice_items ordered by sort_order.
 * Creates payment_allocations records and updates item amount_paid / balance.
 */
function allocate_payment_fifo($conn, $payment_id, $invoice_id, $amount, $org_id)
{
    $items = $conn->query("
        SELECT ii.id, ii.line_total,
               COALESCE((SELECT SUM(pa.amount) FROM payment_allocations pa WHERE pa.invoice_item_id = ii.id AND pa.payment_id != $payment_id), 0) AS already_paid
        FROM invoice_items ii
        WHERE ii.invoice_id = $invoice_id
        ORDER BY ii.sort_order ASC, ii.id ASC
    ");

    $remaining = $amount;

    while ($item = $items->fetch_assoc()) {
        if ($remaining <= 0) break;

        $item_balance = round(floatval($item['line_total']) - floatval($item['already_paid']), 2);
        if ($item_balance <= 0) continue;

        $alloc = min($remaining, $item_balance);
        $alloc = round($alloc, 2);

        $conn->query("INSERT INTO payment_allocations (org_id, payment_id, invoice_item_id, amount) VALUES ($org_id, $payment_id, {$item['id']}, $alloc)");

        // Update item amount_paid and balance
        $new_paid    = round(floatval($item['already_paid']) + $alloc, 2);
        $new_balance = round(floatval($item['line_total']) - $new_paid, 2);
        $conn->query("UPDATE invoice_items SET amount_paid = $new_paid, balance = $new_balance WHERE id = {$item['id']}");

        $remaining = round($remaining - $alloc, 2);
    }
}

/**
 * Return invoice balance summary + itemised list (for payment form)
 */
function get_invoice_balance()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $invoice_id = intval($_GET['invoice_id'] ?? 0);
    if ($invoice_id <= 0) { echo json_encode(['error' => true]); exit; }

    $inv = $conn->query("
        SELECT i.reference_number, i.status,
               t.full_name AS tenant_name, u.unit_number
        FROM invoices i
        LEFT JOIN leases l   ON l.id  = i.lease_id
        LEFT JOIN tenants t  ON t.id  = l.tenant_id
        LEFT JOIN units u    ON u.id  = l.unit_id
        WHERE i.id = $invoice_id AND " . tenant_where_clause('i')
    )->fetch_assoc();

    if (!$inv) { echo json_encode(['error' => true, 'msg' => 'Invoice not found']); exit; }

    $items_res = $conn->query("
        SELECT ii.*,
               COALESCE((SELECT SUM(pa.amount) FROM payment_allocations pa WHERE pa.invoice_item_id = ii.id), 0) AS allocated
        FROM invoice_items ii
        WHERE ii.invoice_id = $invoice_id
        ORDER BY ii.sort_order, ii.id
    ");

    $items     = [];
    $total     = 0;
    $total_paid = 0;

    while ($r = $items_res->fetch_assoc()) {
        $r['item_balance'] = round(floatval($r['line_total']) - floatval($r['allocated']), 2);
        $items[]    = $r;
        $total      += floatval($r['line_total']);
        $total_paid += floatval($r['allocated']);
    }

    echo json_encode([
        'error'        => false,
        'invoice'      => $inv,
        'items'        => $items,
        'total'        => round($total, 2),
        'total_paid'   => round($total_paid, 2),
        'balance'      => round($total - $total_paid, 2),
    ]);
}

/**
 * Full payment show data
 */
function get_payment_show()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) { http_response_code(404); echo json_encode(['error' => true]); exit; }

    $pmt = $conn->query("
        SELECT pr.*,
               i.reference_number AS invoice_ref, i.status AS invoice_status,
               t.full_name AS tenant_name, u.unit_number,
               p.name AS property_name
        FROM payments_received pr
        LEFT JOIN invoices i   ON i.id  = pr.invoice_id
        LEFT JOIN leases l     ON l.id  = i.lease_id
        LEFT JOIN tenants t    ON t.id  = l.tenant_id
        LEFT JOIN units u      ON u.id  = l.unit_id
        LEFT JOIN properties p ON p.id  = u.property_id
        WHERE pr.id = $id AND " . tenant_where_clause('pr')
    )->fetch_assoc();

    if (!$pmt) { http_response_code(404); echo json_encode(['error' => true, 'msg' => 'Payment not found']); exit; }

    $alloc_res = $conn->query("
        SELECT pa.amount, ii.description, ii.line_total, ii.sort_order
        FROM payment_allocations pa
        JOIN invoice_items ii ON ii.id = pa.invoice_item_id
        WHERE pa.payment_id = $id
        ORDER BY ii.sort_order, ii.id
    ");
    $allocations = [];
    while ($a = $alloc_res->fetch_assoc()) $allocations[] = $a;

    $pmt['allocations'] = $allocations;
    echo json_encode($pmt);
}

function delete_receipt()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $id = $_POST['id'] ?? 0;

    if ($id <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid ID.']);
        exit;
    }

    // Get invoice_id before deleting
    $stmt = $conn->prepare("SELECT invoice_id FROM payments_received WHERE id = ? AND " . tenant_where_clause());
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $receipt = $stmt->get_result()->fetch_assoc();
    $invoice_id = $receipt['invoice_id'] ?? 0;

    // Remove allocations first (cascade will also do this, but we want to recompute item balances)
    $alloc_items = $conn->query("SELECT DISTINCT invoice_item_id FROM payment_allocations WHERE payment_id = $id");
    $affected_item_ids = [];
    if ($alloc_items) {
        while ($ai = $alloc_items->fetch_assoc()) $affected_item_ids[] = intval($ai['invoice_item_id']);
    }
    $conn->query("DELETE FROM payment_allocations WHERE payment_id = $id");

    $stmt = $conn->prepare("DELETE FROM payments_received WHERE id = ? AND " . tenant_where_clause());
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Recompute item balances
        foreach ($affected_item_ids as $item_id) {
            $r = $conn->query("SELECT line_total, COALESCE((SELECT SUM(pa.amount) FROM payment_allocations pa WHERE pa.invoice_item_id = $item_id),0) AS paid FROM invoice_items WHERE id = $item_id")->fetch_assoc();
            if ($r) {
                $new_paid = round(floatval($r['paid']), 2);
                $new_bal  = round(floatval($r['line_total']) - $new_paid, 2);
                $conn->query("UPDATE invoice_items SET amount_paid=$new_paid, balance=$new_bal WHERE id=$item_id");
            }
        }
        if ($invoice_id > 0) {
            update_invoice_status($invoice_id);
        }
        echo json_encode(['error' => false, 'msg' => 'Payment deleted successfully.']);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Error deleting payment: ' . $conn->error]);
    }
}

function get_receipt()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $id = $_GET['id'] ?? 0;

    $stmt = $conn->prepare("SELECT * FROM payments_received WHERE id = ? AND " . tenant_where_clause());
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    echo json_encode($result);
}

/**
 * Helper to update invoice status based on payments
 */
function update_invoice_status($invoice_id)
{
    $conn = $GLOBALS['conn'];

    // Get invoice total
    $stmt = $conn->prepare("SELECT amount FROM invoices WHERE id = ? AND " . tenant_where_clause());
    $stmt->bind_param("i", $invoice_id);
    $stmt->execute();
    $inv = $stmt->get_result()->fetch_assoc();
    $total_amount = $inv['amount'] ?? 0;

    // Get sum of payments
    $stmt = $conn->prepare("SELECT SUM(amount_paid) as total_paid FROM payments_received WHERE invoice_id = ? AND " . tenant_where_clause());
    $stmt->bind_param("i", $invoice_id);
    $stmt->execute();
    $pay = $stmt->get_result()->fetch_assoc();
    $total_paid = $pay['total_paid'] ?? 0;

    $status = 'unpaid';
    if ($total_paid >= $total_amount) {
        $status = 'paid';
    } elseif ($total_paid > 0) {
        $status = 'partial';
    }

    $stmt = $conn->prepare("UPDATE invoices SET status = ? WHERE id = ? AND " . tenant_where_clause());
    $stmt->bind_param("si", $status, $invoice_id);
    $stmt->execute();
}

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

        // Get affected invoices before deleting
        $invoices = $conn->query("SELECT DISTINCT invoice_id FROM payments_received WHERE id IN ($id_list) AND " . tenant_where_clause());
        $invoice_ids = [];
        while ($inv = $invoices->fetch_assoc())
            $invoice_ids[] = $inv['invoice_id'];

        $sql = "DELETE FROM payments_received WHERE id IN ($id_list) AND " . tenant_where_clause();

        if ($conn->query($sql)) {
            // Update all affected invoices
            foreach ($invoice_ids as $inv_id) {
                update_invoice_status($inv_id);
            }
            echo json_encode(['error' => false, 'msg' => count($ids) . ' receipt(s) deleted successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error performing bulk deletion: ' . $conn->error]);
        }
    } else {
        echo json_encode(['error' => true, 'msg' => 'Invalid action type.']);
    }
}
