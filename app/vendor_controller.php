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

function get_vendors()
{
    $conn = $GLOBALS['conn'];

    // Server-side processing for DataTables
    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    // Base query
    $sql = "SELECT * FROM vendors WHERE 1=1";

    // Search
    if (!empty($search_value)) {
        $search_value = $conn->real_escape_string($search_value);
        $sql .= " AND (vendor_name LIKE '%$search_value%' 
                    OR service_type LIKE '%$search_value%' 
                    OR phone LIKE '%$search_value%' 
                    OR email LIKE '%$search_value%')";
    }

    // Total records
    $total_records_res = $conn->query("SELECT COUNT(*) as count FROM vendors");
    $total_records = ($total_records_res) ? $total_records_res->fetch_assoc()['count'] : 0;

    // Total filtered records
    $filtered_sql = preg_replace('/SELECT\b.*?\bFROM/is', 'SELECT COUNT(*) as count FROM', $sql, 1);
    $filtered_records_res = $conn->query($filtered_sql);
    $filtered_records = 0;
    if ($filtered_records_res) {
        $row = $filtered_records_res->fetch_assoc();
        $filtered_records = $row['count'] ?? 0;
    }

    // Order
    $sql .= " ORDER BY vendor_name ASC";

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editVendor(' . $row['id'] . ')" title="Edit"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteVendor(' . $row['id'] . ')" title="Delete"><i class="bi bi-trash"></i></button>';

        $data[] = [
            'id' => $row['id'],
            'vendor_name' => htmlspecialchars($row['vendor_name']),
            'service_type' => htmlspecialchars($row['service_type']),
            'phone' => htmlspecialchars($row['phone']),
            'email' => htmlspecialchars($row['email']),
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

function save_vendor()
{
    $conn = $GLOBALS['conn'];

    $id = $_POST['vendor_id'] ?? '';
    $vendor_name = trim($_POST['vendor_name'] ?? '');
    $service_type = trim($_POST['service_type'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($vendor_name) || empty($phone)) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => true, 'msg' => 'Vendor name and phone are required.']);
        exit;
    }

    if (empty($id)) {
        // Insert
        $stmt = $conn->prepare("INSERT INTO vendors (vendor_name, service_type, phone, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $vendor_name, $service_type, $phone, $email);

        if ($stmt->execute()) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => false, 'msg' => 'Vendor added successfully.', 'id' => $conn->insert_id]);
        } else {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => true, 'msg' => 'Error adding vendor: ' . $conn->error]);
        }
    } else {
        // Update
        $stmt = $conn->prepare("UPDATE vendors SET vendor_name=?, service_type=?, phone=?, email=? WHERE id=?");
        $stmt->bind_param("ssssi", $vendor_name, $service_type, $phone, $email, $id);

        if ($stmt->execute()) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => false, 'msg' => 'Vendor updated successfully.']);
        } else {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => true, 'msg' => 'Error updating vendor: ' . $conn->error]);
        }
    }
}

function delete_vendor()
{
    $conn = $GLOBALS['conn'];
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => true, 'msg' => 'Invalid ID.']);
        exit;
    }

    // Check if vendor has assignments
    $check = $conn->query("SELECT id FROM maintenance_assignments WHERE vendor_id = $id LIMIT 1");
    if ($check && $check->num_rows > 0) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => true, 'msg' => 'Cannot delete. This vendor has active maintenance assignments.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM vendors WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => false, 'msg' => 'Vendor deleted successfully.']);
    } else {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => true, 'msg' => 'Error deleting vendor: ' . $conn->error]);
    }
}

function get_vendor()
{
    $conn = $GLOBALS['conn'];
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => true, 'msg' => 'Invalid ID.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM vendors WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    ob_clean();
    header('Content-Type: application/json');
    if ($result) {
        echo json_encode(['error' => false, 'data' => $result]);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Vendor not found.']);
    }
}
