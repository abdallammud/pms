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

function get_guarantees()
{
    ob_clean();
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
        $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editGuarantee(' . $row['id'] . ')"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteGuarantee(' . $row['id'] . ')"><i class="bi bi-trash"></i></button>';

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

/**
 * Handle file upload for ID photos
 * @param array $file - $_FILES array element
 * @param string $type - 'guarantee' or 'guarantee_work'
 * @param string $id_number - ID number for naming
 * @return string|false - Relative file path or false on failure
 */
function handleIdPhotoUpload($file, $type, $id_number)
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK || $file['size'] == 0) {
        return false;
    }

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        return false;
    }

    // Max 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }

    // Create upload directory if not exists
    $upload_dir = dirname(__DIR__) . '/public/uploads/id/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Generate filename: type_idnumber_timestamp.ext
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $clean_id = preg_replace('/[^a-zA-Z0-9_-]/', '_', $id_number);
    $filename = $type . '_' . $clean_id . '_' . time() . '.' . strtolower($ext);
    $target_path = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return 'uploads/id/' . $filename;
    }

    return false;
}

function save_guarantee()
{
    ob_clean();
    header('Content-Type: application/json');
    global $conn;

    $id = $_POST['guarantee_id'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $id_number = trim($_POST['id_number'] ?? '');
    $work_info = trim($_POST['work_info'] ?? '');
    $status = $_POST['status'] ?? 'active';

    // Existing photo paths (for edit mode)
    $existing_id_photo = trim($_POST['existing_id_photo'] ?? '');
    $existing_work_id_photo = trim($_POST['existing_work_id_photo'] ?? '');

    // Validation
    if (empty($full_name) || empty($phone)) {
        echo json_encode(['error' => true, 'msg' => 'Full name and phone are required.']);
        exit;
    }

    if (empty($id_number)) {
        echo json_encode(['error' => true, 'msg' => 'ID number is required.']);
        exit;
    }

    // Handle ID photo upload
    $id_photo = $existing_id_photo;
    if (isset($_FILES['id_photo']) && $_FILES['id_photo']['error'] === UPLOAD_ERR_OK) {
        $uploaded_path = handleIdPhotoUpload($_FILES['id_photo'], 'guarantee', $id_number);
        if ($uploaded_path) {
            $id_photo = $uploaded_path;
        }
    }

    // Handle Work ID photo upload
    $work_id_photo = $existing_work_id_photo;
    if (isset($_FILES['work_id_photo']) && $_FILES['work_id_photo']['error'] === UPLOAD_ERR_OK) {
        $uploaded_path = handleIdPhotoUpload($_FILES['work_id_photo'], 'guarantee_work', $id_number);
        if ($uploaded_path) {
            $work_id_photo = $uploaded_path;
        }
    }

    // For new guarantee, ID photo is required
    if (empty($id) && empty($id_photo)) {
        echo json_encode(['error' => true, 'msg' => 'ID photo is required.']);
        exit;
    }

    if (empty($id)) {
        // Insert
        $stmt = $conn->prepare("INSERT INTO guarantees (full_name, phone, email, id_number, id_photo, work_id_photo, work_info, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $full_name, $phone, $email, $id_number, $id_photo, $work_id_photo, $work_info, $status);

        if ($stmt->execute()) {
            echo json_encode(['error' => false, 'msg' => 'Guarantor added successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error adding guarantor: ' . $conn->error]);
        }
    } else {
        // Update
        $stmt = $conn->prepare("UPDATE guarantees SET full_name=?, phone=?, email=?, id_number=?, id_photo=?, work_id_photo=?, work_info=?, status=? WHERE id=?");
        $stmt->bind_param("ssssssssi", $full_name, $phone, $email, $id_number, $id_photo, $work_id_photo, $work_info, $status, $id);

        if ($stmt->execute()) {
            echo json_encode(['error' => false, 'msg' => 'Guarantor updated successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error updating guarantor: ' . $conn->error]);
        }
    }
}

function delete_guarantee()
{
    ob_clean();
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

function get_guarantee()
{
    ob_clean();
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
function bulk_action()
{
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