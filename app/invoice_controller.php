<?php
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get_invoices') {
        get_invoices();
    } elseif ($action == 'save_invoice') {
        save_invoice();
    } elseif ($action == 'delete_invoice') {
        delete_invoice();
    } elseif ($action == 'get_invoice') {
        get_invoice();
    }
}

function get_invoices() {
    header('Content-Type: application/json');
    global $conn;

    // Server-side processing for DataTables
    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    // Base query
    $sql = "SELECT i.*, t.full_name as tenant_name, u.unit_number 
            FROM rent_invoices i 
            LEFT JOIN leases l ON i.lease_id = l.id 
            LEFT JOIN tenants t ON l.tenant_id = t.id 
            LEFT JOIN units u ON l.unit_id = u.id 
            WHERE 1=1";

    // Search
    if (!empty($search_value)) {
        $sql .= " AND (t.full_name LIKE '%$search_value%' OR u.unit_number LIKE '%$search_value%' OR i.status LIKE '%$search_value%')";
    }

    // Total records (before filtering)
    $total_records_query = $conn->query("SELECT COUNT(*) as count FROM rent_invoices");
    $total_records = $total_records_query->fetch_assoc()['count'];

    // Total filtered records
    $filtered_records_query = $conn->query(str_replace("SELECT i.*, t.full_name as tenant_name, u.unit_number", "SELECT COUNT(*) as count", $sql));
    $filtered_records = $filtered_records_query->fetch_assoc()['count'];

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editInvoice('.$row['id'].')"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteInvoice('.$row['id'].')"><i class="bi bi-trash"></i></button>';

        $statusBadge = '';
        if ($row['status'] == 'paid') {
            $statusBadge = '<span class="badge bg-success">Paid</span>';
        } elseif ($row['status'] == 'unpaid') {
            $statusBadge = '<span class="badge bg-danger">Unpaid</span>';
        } elseif ($row['status'] == 'partial') {
            $statusBadge = '<span class="badge bg-warning">Partial</span>';
        }

        $data[] = [
            'id' => $row['id'],
            'tenant_name' => $row['tenant_name'],
            'unit_number' => $row['unit_number'],
            'amount' => number_format($row['amount'], 2),
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

function save_invoice() {
    // Placeholder for save functionality
}

function delete_invoice() {
    // Placeholder for delete functionality
}

function get_invoice() {
    // Placeholder for get single invoice functionality
}
?>
