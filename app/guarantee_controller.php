<?php
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get_guarantees') {
        get_guarantees();
    } elseif ($action == 'save_guarantee') {
        save_guarantee();
    } elseif ($action == 'delete_guarantee') {
        delete_guarantee();
    } elseif ($action == 'get_guarantee') {
        get_guarantee();
    }
}

function get_guarantees() {
    header('Content-Type: application/json');
    global $conn;

    // Server-side processing for DataTables
    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    // Base query
    $sql = "SELECT * FROM guarantees WHERE 1=1";

    // Search
    if (!empty($search_value)) {
        $sql .= " AND (full_name LIKE '%$search_value%' OR email LIKE '%$search_value%' OR phone LIKE '%$search_value%' OR id_number LIKE '%$search_value%')";
    }

    // Total records (before filtering)
    $total_records_query = $conn->query("SELECT COUNT(*) as count FROM guarantees");
    $total_records = $total_records_query->fetch_assoc()['count'];

    // Total filtered records
    $filtered_records_query = $conn->query(str_replace("SELECT *", "SELECT COUNT(*) as count", $sql));
    $filtered_records = $filtered_records_query->fetch_assoc()['count'];

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editGuarantee('.$row['id'].')"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteGuarantee('.$row['id'].')"><i class="bi bi-trash"></i></button>';

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

function save_guarantee() {
    // Placeholder for save functionality
}

function delete_guarantee() {
    // Placeholder for delete functionality
}

function get_guarantee() {
    // Placeholder for get single guarantee functionality
}
?>
