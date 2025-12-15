<?php
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get_properties') {
        get_properties();
    } elseif ($action == 'save_property') {
        save_property();
    } elseif ($action == 'delete_property') {
        delete_property();
    } elseif ($action == 'get_property') {
        get_property();
    } elseif ($action == 'get_units') {
        get_units();
    } elseif ($action == 'save_unit') {
        save_unit();
    } elseif ($action == 'get_unit') {
        get_unit();
    } elseif ($action == 'delete_unit') {
        delete_unit();
    } elseif ($action == 'get_all_properties') {
        get_all_properties();
    } elseif ($action == 'get_units_by_property') {
        get_units_by_property();
    }
}

// ... existing functions ...

function save_unit() {
    header('Content-Type: application/json');
    global $conn;

    $id = $_POST['unit_id'] ?? '';
    $property_id = $_POST['property_id'] ?? '';
    $unit_number = $_POST['unit_number'] ?? '';
    $unit_type = $_POST['unit_type'] ?? '';
    $size_sqft = $_POST['size_sqft'] ?? 0;
    $rent_amount = $_POST['rent_amount'] ?? 0.00;
    $status = $_POST['status'] ?? 'vacant';
    $tenant_id = $_POST['tenant_id'] ?? null;
    if (empty($tenant_id)) $tenant_id = null;

    if (empty($property_id) || empty($unit_number) || empty($unit_type)) {
        echo json_encode(['error' => true, 'msg' => 'Please fill in all required fields.']);
        exit;
    }

    if (empty($id)) {
        // Insert
        $stmt = $conn->prepare("INSERT INTO units (property_id, unit_number, unit_type, size_sqft, rent_amount, status, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issidsi", $property_id, $unit_number, $unit_type, $size_sqft, $rent_amount, $status, $tenant_id);

        if ($stmt->execute()) {
            echo json_encode(['error' => false, 'msg' => 'Unit added successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error adding unit: ' . $conn->error]);
        }
    } else {
        // Update
        $stmt = $conn->prepare("UPDATE units SET property_id=?, unit_number=?, unit_type=?, size_sqft=?, rent_amount=?, status=?, tenant_id=? WHERE id=?");
        $stmt->bind_param("issidsii", $property_id, $unit_number, $unit_type, $size_sqft, $rent_amount, $status, $tenant_id, $id);

        if ($stmt->execute()) {
            echo json_encode(['error' => false, 'msg' => 'Unit updated successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error updating unit: ' . $conn->error]);
        }
    }
}


function get_properties() {
    header('Content-Type: application/json');
    global $conn;

    // Server-side processing for DataTables
    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    // Base query - join with property_types to get type name
    $sql = "SELECT p.*, u.name as manager_name, pt.type_name 
            FROM properties p 
            LEFT JOIN users u ON p.manager_id = u.id 
            LEFT JOIN property_types pt ON p.type_id = pt.id 
            WHERE 1=1";

    // Search
    if (!empty($search_value)) {
        $search_value = $conn->real_escape_string($search_value);
        $sql .= " AND (p.name LIKE '%$search_value%' OR pt.type_name LIKE '%$search_value%' OR p.city LIKE '%$search_value%' OR u.name LIKE '%$search_value%')";
    }

    // Total records (before filtering)
    $total_records_query = $conn->query("SELECT COUNT(*) as count FROM properties");
    $total_records = $total_records_query->fetch_assoc()['count'];

    // Total filtered records
    $filtered_sql = str_replace("SELECT p.*, u.name as manager_name, pt.type_name", "SELECT COUNT(*) as count", $sql);
    $filtered_records_query = $conn->query($filtered_sql);
    $filtered_records = $filtered_records_query->fetch_assoc()['count'];

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        // Placeholders for units until units table is implemented
        $units_count = 0; 
        $occupied_units = 0;

        // Get all units and occupied units from units table
        $property_id = $row['id'];
        $units_query = $conn->prepare("SELECT COUNT(*) as total_units FROM units WHERE property_id = ?");
        $units_query->bind_param("i", $property_id);
        $units_query->execute();
        $units_result = $units_query->get_result();
        $units_count = $units_result->fetch_assoc()['total_units'];

        $occupied_units_query = $conn->prepare("SELECT COUNT(*) as occupied_units FROM units WHERE property_id = ? AND status = 'occupied'");
        $occupied_units_query->bind_param("i", $property_id);
        $occupied_units_query->execute();
        $occupied_units_result = $occupied_units_query->get_result();
        $occupied_units = $occupied_units_result->fetch_assoc()['occupied_units'];
        
        $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editProperty('.$row['id'].')"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteProperty('.$row['id'].')"><i class="bi bi-trash"></i></button>';

        $data[] = [
            'name' => $row['name'],
            'type' => $row['type_name'] ?? 'N/A',
            'address' => $row['address'] . ', ' . $row['city'],
            'units' => $units_count,
            'occupied_units' => $occupied_units,
            'manager_name' => $row['manager_name'] ?? 'N/A',
            'owner_name' => $row['owner_name'],
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

function save_property() {
    header('Content-Type: application/json');
    global $conn;

    $id = $_POST['property_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $type_id = $_POST['type_id'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $manager_id = !empty($_POST['manager_id']) ? intval($_POST['manager_id']) : null;
    $owner_name = $_POST['owner_name'] ?? '';
    $description = $_POST['description'] ?? '';

    if (empty($name) || empty($city)) {
        echo json_encode(['error' => true, 'msg' => 'Please fill in all required fields.']);
        exit;
    }

    // Handle empty type_id
    $type_id = !empty($type_id) ? intval($type_id) : null;

    if (empty($id)) {
        // Insert
        // Types: s=name, i=type_id, s=address, s=city, i=manager_id, s=owner_name, s=description
        $stmt = $conn->prepare("INSERT INTO properties (name, type_id, address, city, manager_id, owner_name, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sississ", $name, $type_id, $address, $city, $manager_id, $owner_name, $description);

        if ($stmt->execute()) {
            echo json_encode(['error' => false, 'msg' => 'Property added successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error adding property: ' . $conn->error]);
        }
    } else {
        // Update
        $stmt = $conn->prepare("UPDATE properties SET name=?, type_id=?, address=?, city=?, manager_id=?, owner_name=?, description=? WHERE id=?");
        $stmt->bind_param("sisssssi", $name, $type_id, $address, $city, $manager_id, $owner_name, $description, $id);

        if ($stmt->execute()) {
            echo json_encode(['error' => false, 'msg' => 'Property updated successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error updating property: ' . $conn->error]);
        }
    }
}

function delete_property() {
    header('Content-Type: application/json');
    global $conn;
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM properties WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['error' => false, 'msg' => 'Property deleted successfully.']);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Error deleting property: ' . $conn->error]);
    }
}

function get_property() {
    header('Content-Type: application/json');
    global $conn;
    $id = $_GET['id'];
    
    $stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    echo json_encode($result);
}

function get_units() {
    header('Content-Type: application/json');
    global $conn;

    // Server-side processing for DataTables
    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    // Base query
    $sql = "SELECT u.*, p.name as property_name 
            FROM units u 
            LEFT JOIN properties p ON u.property_id = p.id 
            WHERE 1=1";

    // Search
    if (!empty($search_value)) {
        $sql .= " AND (u.unit_number LIKE '%$search_value%' OR u.unit_type LIKE '%$search_value%' OR u.status LIKE '%$search_value%' OR p.name LIKE '%$search_value%')";
    }

    // Total records (before filtering)
    $total_records_query = $conn->query("SELECT COUNT(*) as count FROM units");
    $total_records = $total_records_query->fetch_assoc()['count'];

    // Total filtered records
    $filtered_records_query = $conn->query(str_replace("SELECT u.*, p.name as property_name", "SELECT COUNT(*) as count", $sql));
    $filtered_records = $filtered_records_query->fetch_assoc()['count'];

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editUnit('.$row['id'].')"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteUnit('.$row['id'].')"><i class="bi bi-trash"></i></button>';

        $statusBadge = '';
        if ($row['status'] == 'vacant') {
            $statusBadge = '<span class="badge bg-success">Vacant</span>';
        } elseif ($row['status'] == 'occupied') {
            $statusBadge = '<span class="badge bg-warning">Occupied</span>';
        } else {
            $statusBadge = '<span class="badge bg-danger">Maintenance</span>';
        }

        $data[] = [
            'unit_number' => $row['unit_number'],
            'unit_type' => $row['unit_type'],
            'property_name' => $row['property_name'],
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
 * Get single unit for editing
 */
function get_unit() {
    header('Content-Type: application/json');
    global $conn;
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid ID']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM units WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    echo json_encode($result);
}

/**
 * Delete unit
 */
function delete_unit() {
    header('Content-Type: application/json');
    global $conn;
    
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid ID.']);
        exit;
    }

    // Check if unit has active lease
    $check = $conn->prepare("SELECT id FROM leases WHERE unit_id = ? AND status = 'active' LIMIT 1");
    $check->bind_param("i", $id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['error' => true, 'msg' => 'Cannot delete. This unit has an active lease.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM units WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['error' => false, 'msg' => 'Unit deleted successfully.']);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Error deleting unit: ' . $conn->error]);
    }
}

/**
 * Get all properties for dropdown
 */
function get_all_properties() {
    header('Content-Type: application/json');
    global $conn;

    $result = $conn->query("SELECT id, name FROM properties ORDER BY name");
    $properties = [];
    
    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }

    echo json_encode($properties);
}

/**
 * Get units by property ID for dropdown
 */
function get_units_by_property() {
    header('Content-Type: application/json');
    global $conn;

    $property_id = isset($_GET['property_id']) ? intval($_GET['property_id']) : 0;
    $vacant_only = isset($_GET['vacant_only']) ? $_GET['vacant_only'] == '1' : false;
    
    if ($property_id <= 0) {
        echo json_encode([]);
        exit;
    }

    $sql = "SELECT id, unit_number, unit_type, status, rent_amount FROM units WHERE property_id = ?";
    if ($vacant_only) {
        $sql .= " AND status = 'vacant'";
    }
    $sql .= " ORDER BY unit_number";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $units = [];
    while ($row = $result->fetch_assoc()) {
        $units[] = $row;
    }

    echo json_encode($units);
}
?>
