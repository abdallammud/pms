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
    } elseif ($action == 'bulk_action') {
        bulk_action();
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
            'id' => $row['id'],
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
    header('Content-Type: application/json');
    global $conn;

    $id = $_POST['guarantee_id'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $id_number = trim($_POST['id_number'] ?? '');
    $work_info = trim($_POST['work_info'] ?? '');
    $status = $_POST['status'] ?? 'active';

    if (empty($full_name) || empty($phone)) {
        echo json_encode(['error' => true, 'msg' => 'Full name and phone are required.']);
        exit;
    }

    if (empty($id)) {
        // Insert
        $stmt = $conn->prepare("INSERT INTO guarantees (full_name, phone, email, id_number, work_info, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $full_name, $phone, $email, $id_number, $work_info, $status);

        if ($stmt->execute()) {
            echo json_encode(['error' => false, 'msg' => 'Guarantor added successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error adding guarantor: ' . $conn->error]);
        }
    } else {
        // Update
        $stmt = $conn->prepare("UPDATE guarantees SET full_name=?, phone=?, email=?, id_number=?, work_info=?, status=? WHERE id=?");
        $stmt->bind_param("ssssssi", $full_name, $phone, $email, $id_number, $work_info, $status, $id);

        if ($stmt->execute()) {
            echo json_encode(['error' => false, 'msg' => 'Guarantor updated successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error updating guarantor: ' . $conn->error]);
        }
    }
}

function delete_guarantee() {
    header('Content-Type: application/json');
    global $conn;
    
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid ID.']);
        exit;
    }

    // Check if guarantor is linked to an active lease
    $check = $conn->prepare("SELECT id FROM leases WHERE guarantor_id = ? AND status = 'active' LIMIT 1");
    $check->bind_param("i", $id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['error' => true, 'msg' => 'Cannot delete. This guarantor is linked to an active lease.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM guarantees WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['error' => false, 'msg' => 'Guarantor deleted successfully.']);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Error deleting guarantor: ' . $conn->error]);
    }
}

function get_guarantee() {
    header('Content-Type: application/json');
    global $conn;
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid ID']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM guarantees WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    echo json_encode($result);
}

/**
 * Bulk actions for guarantees
 */
function bulk_action() {
    header('Content-Type: application/json');
    global $conn;

    $action_type = $_POST['action_type'] ?? '';
    $ids = $_POST['ids'] ?? [];

    if (empty($action_type) || empty($ids) || !is_array($ids)) {
        echo json_encode(['error' => true, 'msg' => 'Invalid request.']);
        exit;
    }

    // Sanitize IDs
    $ids = array_map('intval', $ids);
    $ids_str = implode(',', $ids);

    if (empty($ids_str)) {
         echo json_encode(['error' => true, 'msg' => 'No IDs selected.']);
         exit;
    }

    if ($action_type == 'delete') {
        // Check if any guarantor has active lease
        $check_leases = $conn->query("SELECT id FROM leases WHERE guarantor_id IN ($ids_str) AND status = 'active' LIMIT 1");
        
        if ($check_leases && $check_leases->num_rows > 0) {
            echo json_encode(['error' => true, 'msg' => 'Cannot delete selected guarantors because one or more have active leases.']);
            exit;
        }

        if ($conn->query("DELETE FROM guarantees WHERE id IN ($ids_str)")) {
            echo json_encode(['error' => false, 'msg' => 'Selected guarantors deleted successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error deleting guarantors: ' . $conn->error]);
        }

    } else {
        echo json_encode(['error' => true, 'msg' => 'Invalid action type.']);
    }
}
?>
