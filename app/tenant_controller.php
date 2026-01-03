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
        } elseif ($action == 'bulk_action') {
                bulk_action();
        }
}

function get_tenants()
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
        $sql = "SELECT * FROM tenants WHERE 1=1";

        // Search
        if (!empty($search_value)) {
                $sql .= " AND (full_name LIKE '%$search_value%' OR email LIKE '%$search_value%' OR phone LIKE '%$search_value%' OR id_number LIKE '%$search_value%')";
        }

        // Total records (before filtering)
        $total_records_res = $conn->query("SELECT COUNT(*) as count FROM tenants");
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
                $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editTenant(' . $row['id'] . ')"><i class="bi bi-pencil"></i></button>';
                $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteTenant(' . $row['id'] . ')"><i class="bi bi-trash"></i></button>';

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

        ob_clean();
        header('Content-Type: application/json');
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
 * @param string $type - 'tenant' or 'guarantee'
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

function save_tenant()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];

        $id = $_POST['tenant_id'] ?? '';
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
                $uploaded_path = handleIdPhotoUpload($_FILES['id_photo'], 'tenant', $id_number);
                if ($uploaded_path) {
                        $id_photo = $uploaded_path;
                }
        }

        // Handle Work ID photo upload
        $work_id_photo = $existing_work_id_photo;
        if (isset($_FILES['work_id_photo']) && $_FILES['work_id_photo']['error'] === UPLOAD_ERR_OK) {
                $uploaded_path = handleIdPhotoUpload($_FILES['work_id_photo'], 'tenant_work', $id_number);
                if ($uploaded_path) {
                        $work_id_photo = $uploaded_path;
                }
        }

        // For new tenant, ID photo is required
        if (empty($id) && empty($id_photo)) {
                echo json_encode(['error' => true, 'msg' => 'ID photo is required.']);
                exit;
        }

        if (empty($id)) {
                // Insert
                $stmt = $conn->prepare("INSERT INTO tenants (full_name, phone, email, id_number, id_photo, work_id_photo, work_info, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssss", $full_name, $phone, $email, $id_number, $id_photo, $work_id_photo, $work_info, $status);

                if ($stmt->execute()) {
                        echo json_encode(['error' => false, 'msg' => 'Tenant added successfully.']);
                } else {
                        echo json_encode(['error' => true, 'msg' => 'Error adding tenant: ' . $conn->error]);
                }
        } else {
                // Update
                $stmt = $conn->prepare("UPDATE tenants SET full_name=?, phone=?, email=?, id_number=?, id_photo=?, work_id_photo=?, work_info=?, status=? WHERE id=?");
                $stmt->bind_param("ssssssssi", $full_name, $phone, $email, $id_number, $id_photo, $work_id_photo, $work_info, $status, $id);

                if ($stmt->execute()) {
                        echo json_encode(['error' => false, 'msg' => 'Tenant updated successfully.']);
                } else {
                        echo json_encode(['error' => true, 'msg' => 'Error updating tenant: ' . $conn->error]);
                }
        }
}

function delete_tenant()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($id <= 0) {
                echo json_encode(['error' => true, 'msg' => 'Invalid ID.']);
                exit;
        }

        // Check if tenant has active lease
        $check = $conn->prepare("SELECT id FROM leases WHERE tenant_id = ? AND status = 'active' LIMIT 1");
        $check->bind_param("i", $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
                echo json_encode(['error' => true, 'msg' => 'Cannot delete. This tenant has an active lease.']);
                exit;
        }

        $stmt = $conn->prepare("DELETE FROM tenants WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
                echo json_encode(['error' => false, 'msg' => 'Tenant deleted successfully.']);
        } else {
                echo json_encode(['error' => true, 'msg' => 'Error deleting tenant: ' . $conn->error]);
        }
}

function get_tenant()
{
        ob_clean();
        header('Content-Type: application/json');
        global $conn;

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id <= 0) {
                echo json_encode(['error' => true, 'msg' => 'Invalid ID']);
                exit;
        }

        $stmt = $conn->prepare("SELECT * FROM tenants WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        echo json_encode($result);
}

/**
 * Bulk actions for tenants
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
                // Check if any tenant has active lease
                $check_leases = $conn->query("SELECT id FROM leases WHERE tenant_id IN ($ids_str) AND status = 'active' LIMIT 1");

                if ($check_leases && $check_leases->num_rows > 0) {
                        echo json_encode([
                                'error' => true,
                                'msg' => 'Cannot delete selected tenants because one or more have active leases.'
                        ]);
                        exit;
                }

                if ($conn->query("DELETE FROM tenants WHERE id IN ($ids_str)")) {
                        echo json_encode(['error' => false, 'msg' => 'Selected tenants deleted successfully.']);
                } else {
                        echo json_encode(['error' => true, 'msg' => 'Error deleting tenants: ' . $conn->error]);
                }

        } else {
                echo json_encode(['error' => true, 'msg' => 'Invalid action type.']);
        }
}
?>