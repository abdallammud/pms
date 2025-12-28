<?php
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'get_property_types':
            get_property_types();
            break;
        case 'get_property_type':
            get_property_type();
            break;
        case 'save':
            save_property_type();
            break;
        case 'delete':
            delete_property_type();
            break;
        case 'get_active_types':
            get_active_types();
            break;
    }
}

/**
 * Get property types for DataTable (server-side)
 */
<<<<<<< HEAD
function get_property_types()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];
=======
function get_property_types() {
    header('Content-Type: application/json');
    global $conn;
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6

    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    // Base query
    $sql = "SELECT * FROM property_types WHERE 1=1";

    // Search
    if (!empty($search_value)) {
        $search_value = $conn->real_escape_string($search_value);
        $sql .= " AND (type_name LIKE '%$search_value%' OR description LIKE '%$search_value%')";
    }

    // Total records
<<<<<<< HEAD
    $total_records_res = $conn->query("SELECT COUNT(*) as count FROM property_types");
    $total_records = ($total_records_res) ? $total_records_res->fetch_assoc()['count'] : 0;

    // Filtered records
    $filtered_sql = preg_replace('/SELECT\b.*?\bFROM/is', 'SELECT COUNT(*) as count FROM', $sql, 1);
    $filtered_records_res = $conn->query($filtered_sql);
    $filtered_records = 0;
    if ($filtered_records_res) {
        $row = $filtered_records_res->fetch_assoc();
        $filtered_records = $row['count'] ?? 0;
    }
=======
    $total_records_query = $conn->query("SELECT COUNT(*) as count FROM property_types");
    $total_records = $total_records_query->fetch_assoc()['count'];

    // Filtered records
    $filtered_sql = str_replace("SELECT *", "SELECT COUNT(*) as count", $sql);
    $filtered_records_query = $conn->query($filtered_sql);
    $filtered_records = $filtered_records_query->fetch_assoc()['count'];
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6

    // Ordering
    $order_column = $_POST['order'][0]['column'] ?? 0;
    $order_dir = $_POST['order'][0]['dir'] ?? 'asc';
    $columns = ['type_name', 'description', 'status', 'created_at'];
    $order_by = isset($columns[$order_column]) ? $columns[$order_column] : 'type_name';
    $sql .= " ORDER BY $order_by $order_dir";

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
<<<<<<< HEAD
        $statusBadge = $row['status'] == 'active'
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';

        $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editPropertyType(' . $row['id'] . ')" title="Edit"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deletePropertyType(' . $row['id'] . ')" title="Delete"><i class="bi bi-trash"></i></button>';
=======
        $statusBadge = $row['status'] == 'active' 
            ? '<span class="badge bg-success">Active</span>' 
            : '<span class="badge bg-secondary">Inactive</span>';
        
        $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editPropertyType('.$row['id'].')" title="Edit"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deletePropertyType('.$row['id'].')" title="Delete"><i class="bi bi-trash"></i></button>';
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6

        $data[] = [
            'type_name' => htmlspecialchars($row['type_name']),
            'description' => htmlspecialchars($row['description'] ?? ''),
            'status' => $statusBadge,
            'created_at' => date('M d, Y', strtotime($row['created_at'])),
            'actions' => $actionBtn
        ];
    }

<<<<<<< HEAD
    ob_clean();
    header('Content-Type: application/json');
=======
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
    echo json_encode([
        "draw" => intval($draw),
        "recordsTotal" => intval($total_records),
        "recordsFiltered" => intval($filtered_records),
        "data" => $data
    ]);
}

/**
 * Get single property type for editing
 */
<<<<<<< HEAD
function get_property_type()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

=======
function get_property_type() {
    header('Content-Type: application/json');
    global $conn;
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
    if ($id <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid ID']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM property_types WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    echo json_encode($result);
}

/**
 * Save (create/update) property type
 */
<<<<<<< HEAD
function save_property_type()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];
=======
function save_property_type() {
    header('Content-Type: application/json');
    global $conn;
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6

    $id = isset($_POST['property_type_id']) && is_numeric($_POST['property_type_id']) ? intval($_POST['property_type_id']) : 0;
    $type_name = trim($_POST['type_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'active';

    // Validation
    if (empty($type_name)) {
        echo json_encode(['error' => true, 'msg' => 'Type name is required.']);
        exit;
    }

    if ($id === 0) {
        // Insert new
        // Check for duplicate name
        $check = $conn->prepare("SELECT id FROM property_types WHERE type_name = ?");
        $check->bind_param("s", $type_name);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(['error' => true, 'msg' => 'Property type with this name already exists.']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO property_types (type_name, description, status) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $type_name, $description, $status);

        if ($stmt->execute()) {
            echo json_encode(['error' => false, 'msg' => 'Property type added successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error adding property type: ' . $conn->error]);
        }
    } else {
        // Update existing
        // Check for duplicate name (excluding current)
        $check = $conn->prepare("SELECT id FROM property_types WHERE type_name = ? AND id != ?");
        $check->bind_param("si", $type_name, $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(['error' => true, 'msg' => 'Property type with this name already exists.']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE property_types SET type_name = ?, description = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssi", $type_name, $description, $status, $id);

        if ($stmt->execute()) {
            echo json_encode(['error' => false, 'msg' => 'Property type updated successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error updating property type: ' . $conn->error]);
        }
    }
}

/**
 * Delete property type
 */
<<<<<<< HEAD
function delete_property_type()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];
=======
function delete_property_type() {
    header('Content-Type: application/json');
    global $conn;
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid ID.']);
        exit;
    }

    // Check if type is in use
    $check = $conn->prepare("SELECT id FROM properties WHERE type_id = ? LIMIT 1");
    $check->bind_param("i", $id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['error' => true, 'msg' => 'Cannot delete. This property type is in use.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM property_types WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['error' => false, 'msg' => 'Property type deleted successfully.']);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Error deleting property type: ' . $conn->error]);
    }
}

/**
 * Get active property types for dropdowns
 */
<<<<<<< HEAD
function get_active_types()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $result = $conn->query("SELECT id, type_name FROM property_types WHERE status = 'active' ORDER BY type_name");
    $types = [];

=======
function get_active_types() {
    header('Content-Type: application/json');
    global $conn;

    $result = $conn->query("SELECT id, type_name FROM property_types WHERE status = 'active' ORDER BY type_name");
    $types = [];
    
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
    while ($row = $result->fetch_assoc()) {
        $types[] = $row;
    }

    echo json_encode($types);
}
<<<<<<< HEAD
=======
?>
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
