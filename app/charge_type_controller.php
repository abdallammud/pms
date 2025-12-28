<?php
/**
 * Charge Type Controller
 * Handles CRUD operations for invoice charge types
 */
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'get_charge_types':
            get_charge_types();
            break;
        case 'get_active_charge_types':
            get_active_charge_types();
            break;
        case 'save_charge_type':
            save_charge_type();
            break;
        case 'delete_charge_type':
            delete_charge_type();
            break;
        case 'get_charge_type':
            get_charge_type();
            break;
    }
}

/**
 * Get charge types for DataTable (server-side processing)
 */
function get_charge_types()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    // Base query
    $sql = "SELECT * FROM charge_types WHERE 1=1";

    // Search
    if (!empty($search_value)) {
        $search_value = $conn->real_escape_string($search_value);
        $sql .= " AND (name LIKE '%$search_value%' OR description LIKE '%$search_value%')";
    }

    // Total records
    $total_records_res = $conn->query("SELECT COUNT(*) as count FROM charge_types");
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
    $sql .= " ORDER BY name ASC";

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        // Status badge
        $statusBadge = $row['status'] == 'active'
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';

        // Default amount display
        $defaultAmount = $row['default_amount']
            ? '$' . number_format($row['default_amount'], 2)
            : '<span class="text-muted">Not set</span>';

        // Action buttons
        $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editChargeType(' . $row['id'] . ')" title="Edit"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteChargeType(' . $row['id'] . ')" title="Delete"><i class="bi bi-trash"></i></button>';

        $data[] = [
            'id' => $row['id'],
            'name' => htmlspecialchars($row['name']),
            'description' => htmlspecialchars($row['description'] ?? ''),
            'default_amount' => $defaultAmount,
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
 * Get active charge types for select dropdowns
 */
function get_active_charge_types()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $result = $conn->query("SELECT id, name, default_amount FROM charge_types WHERE status = 'active' ORDER BY name ASC");
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'default_amount' => $row['default_amount']
        ];
    }

    echo json_encode(['error' => false, 'data' => $data]);
}

/**
 * Save charge type (create or update)
 */
function save_charge_type()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $id = $_POST['charge_type_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $default_amount = $_POST['default_amount'] ?? null;
    $status = $_POST['status'] ?? 'active';

    // Validation
    if (empty($name)) {
        echo json_encode(['error' => true, 'msg' => 'Charge type name is required.']);
        exit;
    }

    // Check for duplicate name
    $check_sql = "SELECT id FROM charge_types WHERE name = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_id = $id ?: 0;
    $check_stmt->bind_param("si", $name, $check_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        echo json_encode(['error' => true, 'msg' => 'A charge type with this name already exists.']);
        exit;
    }

    // Handle null or empty default amount
    if ($default_amount === '' || $default_amount === null) {
        $default_amount = null;
    } else {
        $default_amount = floatval($default_amount);
    }

    if (empty($id)) {
        // Insert
        $stmt = $conn->prepare("INSERT INTO charge_types (name, description, default_amount, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $name, $description, $default_amount, $status);

        if ($stmt->execute()) {
            echo json_encode(['error' => false, 'msg' => 'Charge type created successfully.', 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error creating charge type: ' . $conn->error]);
        }
    } else {
        // Update
        $stmt = $conn->prepare("UPDATE charge_types SET name=?, description=?, default_amount=?, status=? WHERE id=?");
        $stmt->bind_param("ssdsi", $name, $description, $default_amount, $status, $id);

        if ($stmt->execute()) {
            echo json_encode(['error' => false, 'msg' => 'Charge type updated successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error updating charge type: ' . $conn->error]);
        }
    }
}

/**
 * Delete charge type
 */
function delete_charge_type()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid charge type ID.']);
        exit;
    }

    // Check if charge type is in use
    $check = $conn->query("SELECT COUNT(*) as count FROM invoices WHERE charge_type_id = $id");
    $usage = $check->fetch_assoc()['count'];

    if ($usage > 0) {
        // Soft delete - set to inactive
        $stmt = $conn->prepare("UPDATE charge_types SET status = 'inactive' WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['error' => false, 'msg' => 'Charge type is in use by ' . $usage . ' invoice(s). It has been deactivated instead of deleted.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error deactivating charge type: ' . $conn->error]);
        }
    } else {
        // Hard delete
        $stmt = $conn->prepare("DELETE FROM charge_types WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['error' => false, 'msg' => 'Charge type deleted successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error deleting charge type: ' . $conn->error]);
        }
    }
}

/**
 * Get single charge type by ID
 */
function get_charge_type()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid charge type ID.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM charge_types WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        echo json_encode(['error' => false, 'data' => $result]);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Charge type not found.']);
    }
}
?>