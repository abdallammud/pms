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
                $sql .= " AND (full_name LIKE '%$search_value%' OR email LIKE '%$search_value%' OR phone LIKE '%$search_value%' OR
id_number LIKE '%$search_value%')";
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
                $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editTenant(' . $row['id'] . ')"><i
                class="bi bi-pencil"></i></button>';
                $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteTenant(' . $row['id'] . ')"><i
                class="bi bi-trash"></i></button>';

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

        if (empty($full_name) || empty($phone)) {
                echo json_encode(['error' => true, 'msg' => 'Full name and phone are required.']);
                exit;
        }

        if (empty($id)) {
                // Insert
                $stmt = $conn->prepare("INSERT INTO tenants (full_name, phone, email, id_number, work_info, status) VALUES (?, ?, ?, ?,
?, ?)");
                $stmt->bind_param("ssssss", $full_name, $phone, $email, $id_number, $work_info, $status);

                if ($stmt->execute()) {
                        echo json_encode(['error' => false, 'msg' => 'Tenant added successfully.']);
                } else {
                        echo json_encode(['error' => true, 'msg' => 'Error adding tenant: ' . $conn->error]);
                }
        } else {
                // Update
                $stmt = $conn->prepare("UPDATE tenants SET full_name=?, phone=?, email=?, id_number=?, work_info=?, status=? WHERE
id=?");
                $stmt->bind_param("ssssssi", $full_name, $phone, $email, $id_number, $work_info, $status, $id);

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
                $check_leases = $conn->query("SELECT id FROM leases WHERE tenant_id IN ($ids_str) AND status = 'active'
                LIMIT
                1");

                if ($check_leases && $check_leases->num_rows > 0) {
                        echo json_encode([
                                'error' => true,
                                'msg' => 'Cannot delete selected tenants because one or more have
                active
                leases.'
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