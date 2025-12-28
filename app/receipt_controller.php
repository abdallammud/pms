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
            WHERE 1=1";

    // Search
    if (!empty($search_value)) {
        $search_value = $conn->real_escape_string($search_value);
        $sql .= " AND (t.full_name LIKE '%$search_value%' OR p.payment_method LIKE '%$search_value%' OR p.notes LIKE '%$search_value%')";
    }

    // Total records (before filtering)
    $total_records_res = $conn->query("SELECT COUNT(*) as count FROM payments_received");
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
        $actionBtn = '<button class="btn btn-sm btn-danger" onclick="deleteReceipt(' . $row['id'] . ')"><i class="bi bi-trash"></i></button>';

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

    $id = $_POST['receipt_id'] ?? '';
    $invoice_id = $_POST['invoice_id'] ?? 0;
    $amount_paid = $_POST['amount_paid'] ?? 0;
    $received_date = $_POST['received_date'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $notes = $_POST['notes'] ?? '';

    if (empty($invoice_id) || empty($amount_paid) || empty($received_date)) {
        echo json_encode(['error' => true, 'msg' => 'Please fill in all required fields.']);
        exit;
    }

    if (empty($id)) {
        // Insert
        $receipt_number = generate_reference_number('payment');
        $stmt = $conn->prepare("INSERT INTO payments_received (receipt_number, invoice_id, amount_paid, received_date, payment_method, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sidsss", $receipt_number, $invoice_id, $amount_paid, $received_date, $payment_method, $notes);

        if ($stmt->execute()) {
            // Update invoice status
            update_invoice_status($invoice_id);
            echo json_encode(['error' => false, 'msg' => 'Payment recorded successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error recording payment: ' . $conn->error]);
        }
    } else {
        // Update
        $stmt = $conn->prepare("UPDATE payments_received SET amount_paid=?, received_date=?, payment_method=?, notes=? WHERE id=?");
        $stmt->bind_param("dsssi", $amount_paid, $received_date, $payment_method, $notes, $id);

        if ($stmt->execute()) {
            // Update invoice status
            update_invoice_status($invoice_id);
            echo json_encode(['error' => false, 'msg' => 'Payment updated successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error updating payment: ' . $conn->error]);
        }
    }
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
    $stmt = $conn->prepare("SELECT invoice_id FROM payments_received WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $receipt = $stmt->get_result()->fetch_assoc();
    $invoice_id = $receipt['invoice_id'] ?? 0;

    $stmt = $conn->prepare("DELETE FROM payments_received WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
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

    $stmt = $conn->prepare("SELECT * FROM payments_received WHERE id = ?");
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
    $stmt = $conn->prepare("SELECT amount FROM invoices WHERE id = ?");
    $stmt->bind_param("i", $invoice_id);
    $stmt->execute();
    $inv = $stmt->get_result()->fetch_assoc();
    $total_amount = $inv['amount'] ?? 0;

    // Get sum of payments
    $stmt = $conn->prepare("SELECT SUM(amount_paid) as total_paid FROM payments_received WHERE invoice_id = ?");
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

    $stmt = $conn->prepare("UPDATE invoices SET status = ? WHERE id = ?");
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
        $invoices = $conn->query("SELECT DISTINCT invoice_id FROM payments_received WHERE id IN ($id_list)");
        $invoice_ids = [];
        while ($inv = $invoices->fetch_assoc())
            $invoice_ids[] = $inv['invoice_id'];

        $sql = "DELETE FROM payments_received WHERE id IN ($id_list)";

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
