<?php
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get_vendors') {
        get_vendors();
    } elseif ($action == 'save_vendor') {
        save_vendor();
    } elseif ($action == 'delete_vendor') {
        delete_vendor();
    } elseif ($action == 'get_vendor') {
        get_vendor();
    }
}

function get_vendors() {
    header('Content-Type: application/json');
    global $conn;

    // Server-side processing for DataTables
    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    // Base query
    $sql = "SELECT * FROM vendors WHERE 1=1";

    // Search
    if (!empty($search_value)) {
        $sql .= " AND (vendor_name LIKE '%$search_value%' OR service_type LIKE '%$search_value%' OR phone LIKE '%$search_value%' OR email LIKE '%$search_value%')";
    }

    // Total records (before filtering)
    $total_records_query = $conn->query("SELECT COUNT(*) as count FROM vendors");
    $total_records = $total_records_query->fetch_assoc()['count'];

    // Total filtered records
    $filtered_records_query = $conn->query(str_replace("SELECT *", "SELECT COUNT(*) as count", $sql));
    $filtered_records = $filtered_records_query->fetch_assoc()['count'];

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editVendor('.$row['id'].')"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteVendor('.$row['id'].')"><i class="bi bi-trash"></i></button>';

        $data[] = [
            'id' => $row['id'],
            'vendor_name' => $row['vendor_name'],
            'service_type' => $row['service_type'],
            'phone' => $row['phone'],
            'email' => $row['email'],
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

function save_vendor() {
    // Placeholder for save functionality
}

function delete_vendor() {
    // Placeholder for delete functionality
}

function get_vendor() {
    // Placeholder for get single vendor functionality
}
?>
