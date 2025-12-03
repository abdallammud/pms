<?php
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get_leases') {
        get_leases();
    } elseif ($action == 'save_lease') {
        save_lease();
    } elseif ($action == 'delete_lease') {
        delete_lease();
    } elseif ($action == 'get_lease') {
        get_lease();
    }
}

function get_leases() {
    header('Content-Type: application/json');
    global $conn;

    // Server-side processing for DataTables
    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    // Base query
    $sql = "SELECT l.*, t.full_name as tenant_name, u.unit_number 
            FROM leases l 
            LEFT JOIN tenants t ON l.tenant_id = t.id 
            LEFT JOIN units u ON l.unit_id = u.id 
            WHERE 1=1";

    // Search
    if (!empty($search_value)) {
        $sql .= " AND (t.full_name LIKE '%$search_value%' OR u.unit_number LIKE '%$search_value%' OR l.status LIKE '%$search_value%')";
    }

    // Total records (before filtering)
    $total_records_query = $conn->query("SELECT COUNT(*) as count FROM leases");
    $total_records = $total_records_query->fetch_assoc()['count'];

    // Total filtered records
    $filtered_records_query = $conn->query(str_replace("SELECT l.*, t.full_name as tenant_name, u.unit_number", "SELECT COUNT(*) as count", $sql));
    $filtered_records = $filtered_records_query->fetch_assoc()['count'];

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editLease('.$row['id'].')"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteLease('.$row['id'].')"><i class="bi bi-trash"></i></button>';

        $statusBadge = '';
        if ($row['status'] == 'active') {
            $statusBadge = '<span class="badge bg-success">Active</span>';
        } elseif ($row['status'] == 'pending') {
            $statusBadge = '<span class="badge bg-warning">Pending</span>';
        } elseif ($row['status'] == 'expired') {
            $statusBadge = '<span class="badge bg-danger">Expired</span>';
        } elseif ($row['status'] == 'terminated') {
            $statusBadge = '<span class="badge bg-secondary">Terminated</span>';
        }

        $data[] = [
            'tenant_name' => $row['tenant_name'],
            'unit_number' => $row['unit_number'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
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

function save_lease() {
    // Placeholder for save functionality
}

function delete_lease() {
    // Placeholder for delete functionality
}

function get_lease() {
    // Placeholder for get single lease functionality
}
?>
