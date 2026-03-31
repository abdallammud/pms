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
        } elseif ($action == 'bulk_action') {
                bulk_action();
        } elseif ($action == 'bulk_action_unit') {
                bulk_action_unit();
        } elseif ($action == 'get_unit_amenities') {
                get_unit_amenities();
        } elseif ($action == 'get_unit_images') {
                get_unit_images();
        } elseif ($action == 'upload_unit_image') {
                upload_unit_image();
        } elseif ($action == 'delete_unit_image') {
                delete_unit_image();
        } elseif ($action == 'set_unit_cover_image') {
                set_unit_cover_image();
        } elseif ($action == 'get_property_show') {
                get_property_show();
        } elseif ($action == 'get_property_images') {
                get_property_images();
        } elseif ($action == 'upload_property_image') {
                upload_property_image();
        } elseif ($action == 'delete_property_image') {
                delete_property_image();
        } elseif ($action == 'set_cover_image') {
                set_cover_image();
        }
}

// ... existing functions ...

function save_unit()
{
        ob_clean();
        header('Content-Type: application/json');
        global $conn;

        $id = $_POST['unit_id'] ?? '';
        $property_id = $_POST['property_id'] ?? '';
        $unit_number = trim($_POST['unit_number'] ?? '');
        $unit_type = trim($_POST['unit_type'] ?? '');
        $unit_type_id = !empty($_POST['unit_type_id']) ? (int) $_POST['unit_type_id'] : null;
        $size_sqft = (float) ($_POST['size_sqft'] ?? 0);
        $rent_amount = (float) ($_POST['rent_amount'] ?? 0);
        $status = $_POST['status'] ?? 'vacant';
        $floor_number = isset($_POST['floor_number']) && $_POST['floor_number'] !== '' ? (int) $_POST['floor_number'] : null;
        $room_count = isset($_POST['room_count']) && $_POST['room_count'] !== '' ? (int) $_POST['room_count'] : null;
        $is_listed = isset($_POST['is_listed']) && $_POST['is_listed'] == '1' ? 1 : 0;
        $tenant_id = !empty($_POST['tenant_id']) ? (int) $_POST['tenant_id'] : null;
        $amenity_ids = isset($_POST['amenity_ids']) && is_array($_POST['amenity_ids']) ? array_map('intval', $_POST['amenity_ids']) : [];
        $org_id = resolve_request_org_id();

        // Derive unit_type label from unit_type_id if provided
        if ($unit_type_id && empty($unit_type)) {
                $utRes = $conn->prepare("SELECT type_name FROM unit_types WHERE id = ? AND " . tenant_where_clause());
                $utRes->bind_param("i", $unit_type_id);
                $utRes->execute();
                $utRow = $utRes->get_result()->fetch_assoc();
                if ($utRow)
                        $unit_type = $utRow['type_name'];
        }

        if (empty($property_id) || empty($unit_number)) {
                echo json_encode(['error' => true, 'msg' => 'Property and unit number are required.']);
                exit;
        }

        // Business rule: cannot be occupied AND listed at the same time
        if ($status === 'occupied' && $is_listed) {
                echo json_encode(['error' => true, 'msg' => 'A unit cannot be "occupied" and "listed on website" at the same time.']);
                exit;
        }

        if (empty($id)) {
                $stmt = $conn->prepare("INSERT INTO units (org_id, property_id, unit_number, unit_type, unit_type_id, size_sqft, rent_amount, status, floor_number, room_count, is_listed, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissidssiiis", $org_id, $property_id, $unit_number, $unit_type, $unit_type_id, $size_sqft, $rent_amount, $status, $floor_number, $room_count, $is_listed, $tenant_id);

                if ($stmt->execute()) {
                        $unit_id = $conn->insert_id;
                        save_unit_amenities($unit_id, $amenity_ids);
                        echo json_encode(['error' => false, 'msg' => 'Unit added successfully.', 'id' => $unit_id]);
                } else {
                        echo json_encode(['error' => true, 'msg' => 'Error adding unit: ' . $conn->error]);
                }
        } else {
                $stmt = $conn->prepare("UPDATE units SET property_id=?, unit_number=?, unit_type=?, unit_type_id=?, size_sqft=?, rent_amount=?, status=?, floor_number=?, room_count=?, is_listed=?, tenant_id=? WHERE id=? AND " . tenant_where_clause());
                $stmt->bind_param("issidssiiisi", $property_id, $unit_number, $unit_type, $unit_type_id, $size_sqft, $rent_amount, $status, $floor_number, $room_count, $is_listed, $tenant_id, $id);

                if ($stmt->execute()) {
                        save_unit_amenities((int) $id, $amenity_ids);
                        echo json_encode(['error' => false, 'msg' => 'Unit updated successfully.']);
                } else {
                        echo json_encode(['error' => true, 'msg' => 'Error updating unit: ' . $conn->error]);
                }
        }
}

function save_unit_amenities(int $unit_id, array $amenity_ids)
{
        $conn = $GLOBALS['conn'];
        $conn->query("DELETE FROM unit_amenities WHERE unit_id = $unit_id");
        if (empty($amenity_ids))
                return;

        $stmt = $conn->prepare("INSERT IGNORE INTO unit_amenities (unit_id, amenity_id) VALUES (?, ?)");
        foreach ($amenity_ids as $aid) {
                $stmt->bind_param("ii", $unit_id, $aid);
                $stmt->execute();
        }
}


function get_properties()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];

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
WHERE " . tenant_where_clause('p');

        // Search
        if (!empty($search_value)) {
                $search_value = $conn->real_escape_string($search_value);
                $sql .= " AND (p.name LIKE '%$search_value%' OR pt.type_name LIKE '%$search_value%' OR p.city LIKE '%$search_value%' OR
u.name LIKE '%$search_value%')";
        }

        // Total records (before filtering)
        $total_records_res = $conn->query("SELECT COUNT(*) as count FROM properties p WHERE " . tenant_where_clause('p'));
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
                // Placeholders for units until units table is implemented
                $units_count = 0;
                $occupied_units = 0;

                // Get all units and occupied units from units table
                $property_id = $row['id'];
                $units_query = $conn->prepare("SELECT COUNT(*) as total_units FROM units WHERE property_id = ? AND " . tenant_where_clause());
                $units_query->bind_param("i", $property_id);
                $units_query->execute();
                $units_result = $units_query->get_result();
                $units_count = $units_result->fetch_assoc()['total_units'];

                $occupied_units_query = $conn->prepare("SELECT COUNT(*) as occupied_units FROM units WHERE property_id = ? AND status = 'occupied' AND " . tenant_where_clause());
                $occupied_units_query->bind_param("i", $property_id);
                $occupied_units_query->execute();
                $occupied_units_result = $occupied_units_query->get_result();
                $occupied_units = $occupied_units_result->fetch_assoc()['occupied_units'];

                // Get cover image
                $cover_img = null;
                $img_q = $conn->prepare("SELECT image_path FROM property_images WHERE property_id = ? AND is_cover = 1 AND " . tenant_where_clause() . " LIMIT 1");
                $img_q->bind_param("i", $property_id);
                $img_q->execute();
                $img_row = $img_q->get_result()->fetch_assoc();
                if ($img_row)
                        $cover_img = $img_row['image_path'];

                $data[] = [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'type' => $row['type_name'] ?? 'N/A',
                        'address' => $row['address'],
                        'city' => $row['city'],
                        'region' => $row['region'] ?? '',
                        'district' => $row['district'] ?? '',
                        'units' => (int) $units_count,
                        'occupied_units' => (int) $occupied_units,
                        'vacant_units' => (int) $units_count - (int) $occupied_units,
                        'manager_name' => $row['manager_name'] ?? 'N/A',
                        'owner_name' => $row['owner_name'],
                        'cover_image' => $cover_img,
                        'description' => $row['description'] ?? '',
                ];
        }

        echo json_encode([
                "draw" => intval($draw),
                "recordsTotal" => intval($total_records),
                "recordsFiltered" => intval($filtered_records),
                "data" => $data
        ]);
}

function save_property()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];

        $id = $_POST['property_id'] ?? '';
        $name = $_POST['name'] ?? '';
        $type_id = $_POST['type_id'] ?? '';
        $address = $_POST['address'] ?? '';
        $city = $_POST['city'] ?? '';
        $manager_id = !empty($_POST['manager_id']) ? intval($_POST['manager_id']) : null;
        $owner_name = $_POST['owner_name'] ?? '';
        $description = $_POST['description'] ?? '';
        $region = trim($_POST['region'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $org_id = resolve_request_org_id();

        if (empty($name) || empty($city)) {
                echo json_encode(['error' => true, 'msg' => 'Please fill in all required fields.']);
                exit;
        }

        // Handle empty type_id
        $type_id = !empty($type_id) ? intval($type_id) : null;

        if (empty($id)) {
                $stmt = $conn->prepare("INSERT INTO properties (org_id, name, type_id, address, city, region, district, manager_id, owner_name, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isisssssss", $org_id, $name, $type_id, $address, $city, $region, $district, $manager_id, $owner_name, $description);

                if ($stmt->execute()) {
                        echo json_encode(['error' => false, 'msg' => 'Property added successfully.', 'id' => $conn->insert_id]);
                } else {
                        echo json_encode(['error' => true, 'msg' => 'Error adding property: ' . $conn->error]);
                }
        } else {
                $stmt = $conn->prepare("UPDATE properties SET name=?, type_id=?, address=?, city=?, region=?, district=?, manager_id=?, owner_name=?, description=? WHERE id=? AND " . tenant_where_clause());
                $stmt->bind_param("sisssssssi", $name, $type_id, $address, $city, $region, $district, $manager_id, $owner_name, $description, $id);

                if ($stmt->execute()) {
                        echo json_encode(['error' => false, 'msg' => 'Property updated successfully.']);
                } else {
                        echo json_encode(['error' => true, 'msg' => 'Error updating property: ' . $conn->error]);
                }
        }
}

function delete_property()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];
        $id = $_POST['id'];

        $stmt = $conn->prepare("DELETE FROM properties WHERE id = ? AND " . tenant_where_clause());
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
                echo json_encode(['error' => false, 'msg' => 'Property deleted successfully.']);
        } else {
                echo json_encode(['error' => true, 'msg' => 'Error deleting property: ' . $conn->error]);
        }
}

function get_property()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];
        $id = $_GET['id'];

        $stmt = $conn->prepare("SELECT * FROM properties WHERE id = ? AND " . tenant_where_clause());
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        echo json_encode($result);
}

function get_units()
{
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
WHERE " . tenant_where_clause('u');

        // Search
        if (!empty($search_value)) {
                $sql .= " AND (u.unit_number LIKE '%$search_value%' OR u.unit_type LIKE '%$search_value%' OR u.status LIKE
'%$search_value%' OR p.name LIKE '%$search_value%')";
        }

        // Total records (before filtering)
        $total_records_res = $conn->query("SELECT COUNT(*) as count FROM units u WHERE " . tenant_where_clause('u'));
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
                $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editUnit(' . $row['id'] . ')"><i
                class="bi bi-pencil"></i></button>';
                $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteUnit(' . $row['id'] . ')"><i
                class="bi bi-trash"></i></button>';

                $statusBadge = '';
                if ($row['status'] == 'vacant') {
                        $statusBadge = '<span class="badge bg-success">Vacant</span>';
                } elseif ($row['status'] == 'occupied') {
                        $statusBadge = '<span class="badge bg-warning">Occupied</span>';
                } else {
                        $statusBadge = '<span class="badge bg-danger">Maintenance</span>';
                }

                $data[] = [
                        'id' => $row['id'],
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
function get_unit()
{
        ob_clean();
        header('Content-Type: application/json');
        global $conn;

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id <= 0) {
                echo json_encode(['error' => true, 'msg' => 'Invalid ID']);
                exit;
        }

        $stmt = $conn->prepare("SELECT * FROM units WHERE id = ? AND " . tenant_where_clause());
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $unit = $stmt->get_result()->fetch_assoc();

        if ($unit) {
                // Fetch selected amenity IDs
                $ares = $conn->query("SELECT amenity_id FROM unit_amenities WHERE unit_id = $id");
                $unit['amenity_ids'] = [];
                while ($ar = $ares->fetch_assoc()) {
                        $unit['amenity_ids'][] = (int) $ar['amenity_id'];
                }
        }

        echo json_encode($unit);
}

function get_unit_amenities()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];
        $unit_id = (int) ($_GET['unit_id'] ?? 0);
        $res = $conn->query("SELECT amenity_id FROM unit_amenities WHERE unit_id = $unit_id");
        $ids = [];
        while ($r = $res->fetch_assoc())
                $ids[] = (int) $r['amenity_id'];
        echo json_encode(['error' => false, 'amenity_ids' => $ids]);
}

/**
 * Delete unit
 */
function delete_unit()
{
        header('Content-Type: application/json');
        global $conn;

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($id <= 0) {
                echo json_encode(['error' => true, 'msg' => 'Invalid ID.']);
                exit;
        }

        // Check if unit has active lease
        $check = $conn->prepare("SELECT id FROM leases WHERE unit_id = ? AND status = 'active' AND " . tenant_where_clause() . " LIMIT 1");
        $check->bind_param("i", $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
                echo json_encode(['error' => true, 'msg' => 'Cannot delete. This unit has an active lease.']);
                exit;
        }

        $stmt = $conn->prepare("DELETE FROM units WHERE id = ? AND " . tenant_where_clause());
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
function get_all_properties()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];

        $result = $conn->query("SELECT id, name FROM properties WHERE " . tenant_where_clause() . " ORDER BY name");
        $properties = [];

        while ($row = $result->fetch_assoc()) {
                $properties[] = $row;
        }

        echo json_encode($properties);
}

/**
 * Get units by property ID for dropdown
 */
function get_units_by_property()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];

        $property_id = isset($_GET['property_id']) ? intval($_GET['property_id']) : 0;
        $vacant_only = isset($_GET['vacant_only']) ? $_GET['vacant_only'] == '1' : false;

        if ($property_id <= 0) {
                echo json_encode([]);
                exit;
        }
        $sql = "SELECT id, unit_number, unit_type, status, rent_amount FROM units WHERE property_id = ? AND " . tenant_where_clause();
        if ($vacant_only) {
                $sql .= " AND status = 'vacant'";
        }
        $sql .= " ORDER BY unit_number";
        $stmt = $conn->
                prepare($sql);
        $stmt->bind_param("i", $property_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $units = [];
        while ($row = $result->fetch_assoc()) {
                $units[] = $row;
        }

        echo json_encode($units);
}

/**
 * Bulk actions
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
                // Check if any property has active units/leases or other dependencies
                // For simplicity, we'll try to delete and let FK constraints or manual checks handle it.
                // But let's check for units first to be safe/informative.
                $check_units = $conn->query("SELECT id FROM units WHERE property_id IN ($ids_str) AND " . tenant_where_clause() . " LIMIT 1");
                if ($check_units->num_rows > 0) {
                        echo json_encode([
                                'error' => true,
                                'msg' => 'Cannot delete selected properties because one or
                        more have
                        units associated with them. Please delete units first.'
                        ]);
                        exit;
                }

                if ($conn->query("DELETE FROM properties WHERE id IN ($ids_str) AND " . tenant_where_clause())) {
                        echo json_encode(['error' => false, 'msg' => 'Selected properties deleted successfully.']);
                } else {
                        echo json_encode(['error' => true, 'msg' => 'Error deleting properties: ' . $conn->error]);
                }

        } else {
                echo json_encode(['error' => true, 'msg' => 'Invalid action type.']);
        }
}

/**
 * Bulk actions for units
 */
function bulk_action_unit()
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
                // Check if any unit has active lease
                // We can't delete units with active leases.
                $check_leases = $conn->query("SELECT id FROM leases WHERE unit_id IN ($ids_str) AND status = 'active' AND " . tenant_where_clause() . " LIMIT 1");

                if ($check_leases && $check_leases->num_rows > 0) {
                        echo json_encode([
                                'error' => true,
                                'msg' => 'Cannot delete selected units because one or more
                        have active
                        leases.'
                        ]);
                        exit;
                }

                if ($conn->query("DELETE FROM units WHERE id IN ($ids_str) AND " . tenant_where_clause())) {
                        echo json_encode(['error' => false, 'msg' => 'Selected units deleted successfully.']);
                } else {
                        echo json_encode(['error' => true, 'msg' => 'Error deleting units: ' . $conn->error]);
                }

        } else {
                echo json_encode(['error' => true, 'msg' => 'Invalid action type.']);
        }
}

// =============================================================
// Unit Images
// =============================================================

function upload_unit_image()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];
        $org_id = resolve_request_org_id();
        $unit_id = (int) ($_POST['unit_id'] ?? 0);
        $caption = trim($_POST['caption'] ?? '');

        if ($unit_id <= 0) {
                echo json_encode(['error' => true, 'msg' => 'Invalid unit ID.']);
                exit;
        }

        $own = $conn->prepare("SELECT id FROM units WHERE id = ? AND " . tenant_where_clause());
        $own->bind_param("i", $unit_id);
        $own->execute();
        if ($own->get_result()->num_rows === 0) {
                echo json_encode(['error' => true, 'msg' => 'Unit not found.']);
                exit;
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['error' => true, 'msg' => 'No file uploaded.']);
                exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])) {
                echo json_encode(['error' => true, 'msg' => 'Invalid file type.']);
                exit;
        }
        if ($_FILES['image']['size'] > 8 * 1024 * 1024) {
                echo json_encode(['error' => true, 'msg' => 'File too large (max 8 MB).']);
                exit;
        }

        $dir = dirname(__DIR__) . '/public/uploads/units/';
        if (!is_dir($dir))
                mkdir($dir, 0755, true);
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $filename = 'unit_' . $unit_id . '_' . uniqid() . '.' . $ext;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $dir . $filename)) {
                echo json_encode(['error' => true, 'msg' => 'Failed to save file.']);
                exit;
        }

        $path = 'public/uploads/units/' . $filename;
        $cntRes = $conn->prepare("SELECT COUNT(*) as c FROM unit_images WHERE unit_id = ? AND " . tenant_where_clause());
        $cntRes->bind_param("i", $unit_id);
        $cntRes->execute();
        $is_cover = ($cntRes->get_result()->fetch_assoc()['c'] == 0) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO unit_images (org_id, unit_id, image_path, is_cover, caption) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisis", $org_id, $unit_id, $path, $is_cover, $caption);
        echo $stmt->execute()
                ? json_encode(['error' => false, 'msg' => 'Image uploaded.', 'image_id' => $conn->insert_id, 'path' => $path, 'is_cover' => $is_cover])
                : json_encode(['error' => true, 'msg' => 'DB error: ' . $conn->error]);
}

function get_unit_images()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];
        $unit_id = (int) ($_GET['unit_id'] ?? 0);
        $stmt = $conn->prepare("SELECT * FROM unit_images WHERE unit_id = ? AND " . tenant_where_clause() . " ORDER BY is_cover DESC, uploaded_at ASC");
        $stmt->bind_param("i", $unit_id);
        $stmt->execute();
        $images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['error' => false, 'data' => $images]);
}

function delete_unit_image()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];
        $id = (int) ($_POST['id'] ?? 0);

        $stmt = $conn->prepare("SELECT * FROM unit_images WHERE id = ? AND " . tenant_where_clause());
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $img = $stmt->get_result()->fetch_assoc();
        if (!$img) {
                echo json_encode(['error' => true, 'msg' => 'Image not found.']);
                exit;
        }

        $full = dirname(__DIR__) . '/' . $img['image_path'];
        if (file_exists($full))
                @unlink($full);

        $del = $conn->prepare("DELETE FROM unit_images WHERE id = ? AND " . tenant_where_clause());
        $del->bind_param("i", $id);
        if ($del->execute()) {
                if ($img['is_cover']) {
                        $next = $conn->prepare("SELECT id FROM unit_images WHERE unit_id = ? AND " . tenant_where_clause() . " ORDER BY uploaded_at ASC LIMIT 1");
                        $next->bind_param("i", $img['unit_id']);
                        $next->execute();
                        $nr = $next->get_result()->fetch_assoc();
                        if ($nr)
                                $conn->query("UPDATE unit_images SET is_cover = 1 WHERE id = " . $nr['id']);
                }
                echo json_encode(['error' => false, 'msg' => 'Image deleted.']);
        } else {
                echo json_encode(['error' => true, 'msg' => 'Error: ' . $conn->error]);
        }
}

function set_unit_cover_image()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];
        $id = (int) ($_POST['id'] ?? 0);
        $unit_id = (int) ($_POST['unit_id'] ?? 0);

        $own = $conn->prepare("SELECT id FROM unit_images WHERE id = ? AND unit_id = ? AND " . tenant_where_clause());
        $own->bind_param("ii", $id, $unit_id);
        $own->execute();
        if ($own->get_result()->num_rows === 0) {
                echo json_encode(['error' => true, 'msg' => 'Image not found.']);
                exit;
        }

        $conn->query("UPDATE unit_images SET is_cover = 0 WHERE unit_id = $unit_id AND " . tenant_where_clause());
        $conn->query("UPDATE unit_images SET is_cover = 1 WHERE id = $id");
        echo json_encode(['error' => false, 'msg' => 'Cover image updated.']);
}

// =============================================================
// Property Images
// =============================================================

function upload_property_image()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];
        $org_id = resolve_request_org_id();
        $property_id = (int) ($_POST['property_id'] ?? 0);
        $caption = trim($_POST['caption'] ?? '');

        if ($property_id <= 0) {
                echo json_encode(['error' => true, 'msg' => 'Invalid property ID.']);
                exit;
        }

        // Verify ownership
        $own = $conn->prepare("SELECT id FROM properties WHERE id = ? AND " . tenant_where_clause());
        $own->bind_param("i", $property_id);
        $own->execute();
        if ($own->get_result()->num_rows === 0) {
                echo json_encode(['error' => true, 'msg' => 'Property not found.']);
                exit;
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['error' => true, 'msg' => 'No file uploaded or upload error.']);
                exit;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed)) {
                echo json_encode(['error' => true, 'msg' => 'Invalid file type. Only JPG, PNG, WebP, GIF allowed.']);
                exit;
        }

        if ($_FILES['image']['size'] > 8 * 1024 * 1024) {
                echo json_encode(['error' => true, 'msg' => 'File too large. Maximum 8 MB.']);
                exit;
        }

        $upload_dir = dirname(__DIR__) . '/public/uploads/properties/';
        if (!is_dir($upload_dir))
                mkdir($upload_dir, 0755, true);

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'prop_' . $property_id . '_' . uniqid() . '.' . strtolower($ext);
        $target = $upload_dir . $filename;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                echo json_encode(['error' => true, 'msg' => 'Failed to save file.']);
                exit;
        }

        $path = 'public/uploads/properties/' . $filename;

        // If this is the first image for the property, make it the cover
        $count_res = $conn->prepare("SELECT COUNT(*) as c FROM property_images WHERE property_id = ? AND " . tenant_where_clause());
        $count_res->bind_param("i", $property_id);
        $count_res->execute();
        $count = $count_res->get_result()->fetch_assoc()['c'];
        $is_cover = ($count == 0) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO property_images (org_id, property_id, image_path, is_cover, caption) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisis", $org_id, $property_id, $path, $is_cover, $caption);

        if ($stmt->execute()) {
                echo json_encode(['error' => false, 'msg' => 'Image uploaded.', 'image_id' => $conn->insert_id, 'path' => $path, 'is_cover' => $is_cover]);
        } else {
                echo json_encode(['error' => true, 'msg' => 'DB error: ' . $conn->error]);
        }
}

function get_property_images()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];
        $property_id = (int) ($_GET['property_id'] ?? 0);

        if ($property_id <= 0) {
                echo json_encode(['error' => true, 'msg' => 'Invalid property ID.']);
                exit;
        }

        $stmt = $conn->prepare("SELECT * FROM property_images WHERE property_id = ? AND " . tenant_where_clause() . " ORDER BY is_cover DESC, uploaded_at ASC");
        $stmt->bind_param("i", $property_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $images = [];
        while ($row = $result->fetch_assoc())
                $images[] = $row;

        echo json_encode(['error' => false, 'data' => $images]);
}

function delete_property_image()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];
        $id = (int) ($_POST['id'] ?? 0);

        $stmt = $conn->prepare("SELECT * FROM property_images WHERE id = ? AND " . tenant_where_clause());
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $img = $stmt->get_result()->fetch_assoc();

        if (!$img) {
                echo json_encode(['error' => true, 'msg' => 'Image not found.']);
                exit;
        }

        // Delete physical file
        $full_path = dirname(__DIR__) . '/' . $img['image_path'];
        if (file_exists($full_path))
                @unlink($full_path);

        $del = $conn->prepare("DELETE FROM property_images WHERE id = ? AND " . tenant_where_clause());
        $del->bind_param("i", $id);

        if ($del->execute()) {
                // If deleted image was the cover, auto-assign cover to next available
                if ($img['is_cover']) {
                        $next = $conn->prepare("SELECT id FROM property_images WHERE property_id = ? AND " . tenant_where_clause() . " ORDER BY uploaded_at ASC LIMIT 1");
                        $next->bind_param("i", $img['property_id']);
                        $next->execute();
                        $next_row = $next->get_result()->fetch_assoc();
                        if ($next_row) {
                                $conn->query("UPDATE property_images SET is_cover = 1 WHERE id = " . $next_row['id']);
                        }
                }
                echo json_encode(['error' => false, 'msg' => 'Image deleted.']);
        } else {
                echo json_encode(['error' => true, 'msg' => 'Error: ' . $conn->error]);
        }
}

function set_cover_image()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];
        $id = (int) ($_POST['id'] ?? 0);
        $property_id = (int) ($_POST['property_id'] ?? 0);

        // Verify ownership
        $own = $conn->prepare("SELECT id FROM property_images WHERE id = ? AND property_id = ? AND " . tenant_where_clause());
        $own->bind_param("ii", $id, $property_id);
        $own->execute();
        if ($own->get_result()->num_rows === 0) {
                echo json_encode(['error' => true, 'msg' => 'Image not found.']);
                exit;
        }

        // Unset all covers for this property, then set new one
        $conn->query("UPDATE property_images SET is_cover = 0 WHERE property_id = $property_id AND " . tenant_where_clause());
        $conn->query("UPDATE property_images SET is_cover = 1 WHERE id = $id");

        echo json_encode(['error' => false, 'msg' => 'Cover image updated.']);
}

// =============================================================
// Property Show Page Data
// =============================================================

function get_property_show()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
                echo json_encode(['error' => true, 'msg' => 'Invalid ID.']);
                exit;
        }

        $stmt = $conn->prepare("SELECT p.*, pt.type_name, u.name as manager_name FROM properties p LEFT JOIN property_types pt ON p.type_id = pt.id LEFT JOIN users u ON p.manager_id = u.id WHERE p.id = ? AND " . tenant_where_clause('p'));
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $property = $stmt->get_result()->fetch_assoc();

        if (!$property) {
                echo json_encode(['error' => true, 'msg' => 'Property not found.']);
                exit;
        }

        // Unit stats
        $stats = $conn->query("SELECT COUNT(*) as total, SUM(status='occupied') as occupied, SUM(status='vacant') as vacant, SUM(status='maintenance') as maintenance FROM units WHERE property_id = $id AND " . tenant_where_clause())->fetch_assoc();

        // Active leases
        $active_leases = $conn->query("SELECT COUNT(*) as c FROM leases WHERE property_id = $id AND status = 'active' AND " . tenant_where_clause())->fetch_assoc()['c'] ?? 0;

        // Images
        $img_stmt = $conn->prepare("SELECT * FROM property_images WHERE property_id = ? AND " . tenant_where_clause() . " ORDER BY is_cover DESC, uploaded_at ASC");
        $img_stmt->bind_param("i", $id);
        $img_stmt->execute();
        $images = $img_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Recent units
        $units_stmt = $conn->prepare("SELECT u.*, t.full_name as tenant_name FROM units u LEFT JOIN tenants t ON u.tenant_id = t.id WHERE u.property_id = ? AND " . tenant_where_clause('u') . " ORDER BY u.unit_number LIMIT 10");
        $units_stmt->bind_param("i", $id);
        $units_stmt->execute();
        $units = $units_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode([
                'error' => false,
                'property' => $property,
                'stats' => [
                        'total_units' => (int) ($stats['total'] ?? 0),
                        'occupied' => (int) ($stats['occupied'] ?? 0),
                        'vacant' => (int) ($stats['vacant'] ?? 0),
                        'maintenance' => (int) ($stats['maintenance'] ?? 0),
                        'active_leases' => (int) $active_leases,
                ],
                'images' => $images,
                'units' => $units,
        ]);
}

?>