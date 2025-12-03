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
    }
}

function get_receipts() {
    header('Content-Type: application/json');
    global $conn;

    // Server-side processing for DataTables
    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    // Base query
    $sql = "SELECT p.*, i.id as invoice_number, t.full_name as tenant_name 
            FROM payments_received p 
            LEFT JOIN rent_invoices i ON p.invoice_id = i.id 
            LEFT JOIN leases l ON i.lease_id = l.id 
            LEFT JOIN tenants t ON l.tenant_id = t.id 
            WHERE 1=1";

    // Search
    if (!empty($search_value)) {
        $sql .= " AND (t.full_name LIKE '%$search_value%' OR p.payment_method LIKE '%$search_value%' OR p.notes LIKE '%$search_value%')";
    }

    // Total records (before filtering)
    $total_records_query = $conn->query("SELECT COUNT(*) as count FROM payments_received");
    $total_records = $total_records_query->fetch_assoc()['count'];

    // Total filtered records
    $filtered_records_query = $conn->query(str_replace("SELECT p.*, i.id as invoice_number, t.full_name as tenant_name", "SELECT COUNT(*) as count", $sql));
    $filtered_records = $filtered_records_query->fetch_assoc()['count'];

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editReceipt('.$row['id'].')"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteReceipt('.$row['id'].')"><i class="bi bi-trash"></i></button>';

        $paymentMethodBadge = '';
        if ($row['payment_method'] == 'cash') {
            $paymentMethodBadge = '<span class="badge bg-success">Cash</span>';
        } elseif ($row['payment_method'] == 'mobile') {
            $paymentMethodBadge = '<span class="badge bg-info">Mobile</span>';
        } elseif ($row['payment_method'] == 'bank') {
            $paymentMethodBadge = '<span class="badge bg-primary">Bank</span>';
        }

        $data[] = [
            'id' => $row['id'],
            'invoice_number' => $row['invoice_number'],
            'tenant_name' => $row['tenant_name'],
            'amount_paid' => number_format($row['amount_paid'], 2),
            'payment_method' => $paymentMethodBadge,
            'received_date' => $row['received_date'],
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

function save_receipt() {
    // Placeholder for save functionality
}

function delete_receipt() {
    // Placeholder for delete functionality
}

function get_receipt() {
    // Placeholder for get single receipt functionality
}
?>
