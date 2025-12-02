<?php
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get_tenants') {
        get_tenants();
    } elseif ($action == 'save_tenant') {
        save_tenant();
    } elseif ($action == 'delete_tenant') {
        delete_tenant();
    } elseif ($action == 'get_tenant') {
        get_tenant();
    }
}

function get_tenants() {
    header('Content-Type: application/json');
    global $conn;

    // Server-side processing for DataTables
    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    // Base query
    $sql = "SELECT * FROM tenants WHERE 1=1";

    // Search
    if (!empty($search_value)) {
        $sql .= " AND (full_name LIKE '%$search_value%' OR email LIKE '%$search_value%' OR phone LIKE '%$search_value%' OR id_number LIKE '%$search_value%')";
    }

    // Total records (before filtering)
    $total_records_query = $conn->query("SELECT COUNT(*) as count FROM tenants");
    $total_records = $total_records_query->fetch_assoc()['count'];

    // Total filtered records
    $filtered_records_query = $conn->query(str_replace("SELECT *", "SELECT COUNT(*) as count", $sql));
    $filtered_records = $filtered_records_query->fetch_assoc()['count'];

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editTenant('.$row['id'].')"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteTenant('.$row['id'].')"><i class="bi bi-trash"></i></button>';

        $statusBadge = '';
        if ($row['status'] == 'active') {
            $statusBadge = '<span class="badge bg-success">Active</span>';
        } else {
            $statusBadge = '<span class="badge bg-secondary">Inactive</span>';
        }

        $data[] = [
            'full_name' => $row['full_name'],
            'phone' => $row['phone'],
            'email' => $row['email'],
            'id_number' => $row['id_number'],
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

function save_tenant() {
    // Placeholder for save functionality
}

function delete_tenant() {
    // Placeholder for delete functionality
}

function get_tenant() {
    // Placeholder for get single tenant functionality
}
?>
