<?php
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get_requests') {
        get_requests();
    } elseif ($action == 'save_request') {
        save_request();
    } elseif ($action == 'delete_request') {
        delete_request();
    } elseif ($action == 'get_request') {
        get_request();
    }
}

function get_requests() {
    header('Content-Type: application/json');
    global $conn;

    // Server-side processing for DataTables
    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    // Base query
    $sql = "SELECT m.*, p.name as property_name, u.unit_number, v.vendor_name 
            FROM maintenance_requests m 
            LEFT JOIN properties p ON m.property_id = p.id 
            LEFT JOIN units u ON m.unit_id = u.id 
            LEFT JOIN maintenance_assignments ma ON m.id = ma.request_id 
            LEFT JOIN vendors v ON ma.vendor_id = v.id 
            WHERE 1=1";

    // Search
    if (!empty($search_value)) {
        $sql .= " AND (p.name LIKE '%$search_value%' OR u.unit_number LIKE '%$search_value%' OR m.description LIKE '%$search_value%' OR v.vendor_name LIKE '%$search_value%')";
    }

    // Total records (before filtering)
    $total_records_query = $conn->query("SELECT COUNT(*) as count FROM maintenance_requests");
    $total_records = $total_records_query->fetch_assoc()['count'];

    // Total filtered records
    $filtered_records_query = $conn->query(str_replace("SELECT m.*, p.name as property_name, u.unit_number, v.vendor_name", "SELECT COUNT(*) as count", $sql));
    $filtered_records = $filtered_records_query->fetch_assoc()['count'];

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editRequest('.$row['id'].')"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteRequest('.$row['id'].')"><i class="bi bi-trash"></i></button>';

        $statusBadge = '';
        if ($row['status'] == 'new') {
            $statusBadge = '<span class="badge bg-info">New</span>';
        } elseif ($row['status'] == 'in_progress') {
            $statusBadge = '<span class="badge bg-warning">In Progress</span>';
        } elseif ($row['status'] == 'completed') {
            $statusBadge = '<span class="badge bg-success">Completed</span>';
        }

        $priorityBadge = '';
        if ($row['priority'] == 'low') {
            $priorityBadge = '<span class="badge bg-success">Low</span>';
        } elseif ($row['priority'] == 'medium') {
            $priorityBadge = '<span class="badge bg-warning">Medium</span>';
        } elseif ($row['priority'] == 'high') {
            $priorityBadge = '<span class="badge bg-danger">High</span>';
        }

        $data[] = [
            'id' => $row['id'],
            'property_name' => $row['property_name'],
            'unit_number' => $row['unit_number'],
            'priority' => $priorityBadge,
            'description' => $row['description'],
            'assigned_to' => $row['vendor_name'] ?? 'Unassigned',
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

function save_request() {
    // Placeholder for save functionality
}

function delete_request() {
    // Placeholder for delete functionality
}

function get_request() {
    // Placeholder for get single request functionality
}
?>
