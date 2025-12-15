<?php
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get_leases') {
        get_leases();
    } elseif ($action == 'save_lease') {
        save_lease();
    } elseif ($action == 'delete_lease') {
        delete_lease();
    } elseif ($action == 'get_lease') {
        get_lease();
    }
}

/**
 * Get leases for DataTable
 */
function get_leases() {
    header('Content-Type: application/json');
    global $conn;

    // Server-side processing for DataTables
    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    // Base query with joins
    $sql = "SELECT l.*, 
                   t.full_name as tenant_name, 
                   u.unit_number,
                   p.name as property_name,
                   g.full_name as guarantee_name
            FROM leases l 
            LEFT JOIN tenants t ON l.tenant_id = t.id 
            LEFT JOIN units u ON l.unit_id = u.id 
            LEFT JOIN properties p ON l.property_id = p.id
            LEFT JOIN guarantees g ON l.guarantee_id = g.id
            WHERE 1=1";

    // Search
    if (!empty($search_value)) {
        $search_value = $conn->real_escape_string($search_value);
        $sql .= " AND (t.full_name LIKE '%$search_value%' 
                  OR u.unit_number LIKE '%$search_value%' 
                  OR p.name LIKE '%$search_value%'
                  OR l.reference_number LIKE '%$search_value%'
                  OR l.status LIKE '%$search_value%')";
    }

    // Total records (before filtering)
    $total_records_query = $conn->query("SELECT COUNT(*) as count FROM leases");
    $total_records = $total_records_query->fetch_assoc()['count'];

    // Total filtered records
    $filtered_sql = preg_replace('/SELECT l\.\*.*?FROM/s', 'SELECT COUNT(*) as count FROM', $sql);
    $filtered_records_query = $conn->query($filtered_sql);
    $filtered_records = $filtered_records_query ? $filtered_records_query->fetch_assoc()['count'] : 0;

    // Order
    $sql .= " ORDER BY l.id DESC";

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        // Action buttons
        $actionBtn = '<button class="btn btn-sm btn-info me-1" onclick="viewLease('.$row['id'].')" title="View"><i class="bi bi-eye"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-primary me-1" onclick="editLease('.$row['id'].')" title="Edit"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteLease('.$row['id'].')" title="Delete"><i class="bi bi-trash"></i></button>';

        // Status badge
        $statusBadge = '';
        if ($row['status'] == 'active') {
            $statusBadge = '<span class="badge bg-success">Active</span>';
        } elseif ($row['status'] == 'pending') {
            $statusBadge = '<span class="badge bg-warning">Pending</span>';
        } elseif ($row['status'] == 'expired') {
            $statusBadge = '<span class="badge bg-danger">Expired</span>';
        } elseif ($row['status'] == 'terminated') {
            $statusBadge = '<span class="badge bg-secondary">Terminated</span>';
        }

        // Format rent
        $monthlyRent = '$' . number_format($row['monthly_rent'], 2);

        // Combine property and unit
        $propertyUnit = ($row['property_name'] ?? 'N/A') . ' - ' . ($row['unit_number'] ?? 'N/A');

        $data[] = [
            'reference_number' => $row['reference_number'] ?? 'N/A',
            'tenant_name' => $row['tenant_name'] ?? 'N/A',
            'property_unit' => $propertyUnit,
            'monthly_rent' => $monthlyRent,
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
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
 * Save lease (create or update)
 */
function save_lease() {
    header('Content-Type: application/json');
    global $conn;

    // Get form data
    $id = $_POST['lease_id'] ?? '';
    $tenant_id = intval($_POST['tenant_id'] ?? 0);
    $guarantee_id = intval($_POST['guarantee_id'] ?? 0);
    $property_id = intval($_POST['property_id'] ?? 0);
    $unit_id = intval($_POST['unit_id'] ?? 0);
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $monthly_rent = floatval($_POST['monthly_rent'] ?? 0);
    $deposit = floatval($_POST['deposit'] ?? 0);
    $rent_cycle = $_POST['rent_cycle'] ?? 'monthly';
    $auto_invoice = intval($_POST['auto_invoice'] ?? 1);
    $status = $_POST['status'] ?? 'active';
    $lease_conditions = $_POST['lease_conditions'] ?? '';
    $vehicle_info = $_POST['vehicle_info'] ?? '';
    $legal_weapons = $_POST['legal_weapons'] ?? '';

    // Witnesses (arrays)
    $witness_names = $_POST['witness_name'] ?? [];
    $witness_phones = $_POST['witness_phone'] ?? [];
    $witness_ids = $_POST['witness_id'] ?? [];

    // Combine witnesses into JSON
    $witnesses = [];
    for ($i = 0; $i < count($witness_names); $i++) {
        if (!empty($witness_names[$i])) {
            $witnesses[] = [
                'name' => $witness_names[$i],
                'phone' => $witness_phones[$i] ?? '',
                'id_card' => $witness_ids[$i] ?? ''
            ];
        }
    }
    $witnesses_json = json_encode($witnesses);

    // Validation
    if (empty($tenant_id) || empty($unit_id) || empty($start_date) || empty($end_date)) {
        echo json_encode(['error' => true, 'msg' => 'Please fill in all required fields.']);
        exit;
    }

    // Escape strings
    $lease_conditions = $conn->real_escape_string($lease_conditions);
    $vehicle_info = $conn->real_escape_string($vehicle_info);
    $legal_weapons = $conn->real_escape_string($legal_weapons);
    $witnesses_json = $conn->real_escape_string($witnesses_json);

    if (empty($id)) {
        // Generate reference number
        $reference_number = generate_reference_number('lease');

        // Insert new lease
        $sql = "INSERT INTO leases (
                    reference_number, tenant_id, guarantee_id, property_id, unit_id, 
                    start_date, end_date, monthly_rent, deposit, payment_cycle, 
                    auto_invoice, status, lease_conditions, vehicle_info, legal_weapons, witnesses, created_at
                ) VALUES (
                    '$reference_number', $tenant_id, $guarantee_id, $property_id, $unit_id,
                    '$start_date', '$end_date', $monthly_rent, $deposit, '$rent_cycle',
                    $auto_invoice, '$status', '$lease_conditions', '$vehicle_info', '$legal_weapons', '$witnesses_json', NOW()
                )";

        if ($conn->query($sql)) {
            $lease_id = $conn->insert_id;

            // Update unit status to occupied
            $conn->query("UPDATE units SET status = 'occupied', tenant_id = $tenant_id WHERE id = $unit_id");

            echo json_encode(['error' => false, 'msg' => 'Lease created successfully.', 'id' => $lease_id]);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error creating lease: ' . $conn->error]);
        }
    } else {
        // Update existing lease
        $id = intval($id);
        $sql = "UPDATE leases SET 
                    tenant_id = $tenant_id,
                    guarantee_id = $guarantee_id,
                    property_id = $property_id,
                    unit_id = $unit_id,
                    start_date = '$start_date',
                    end_date = '$end_date',
                    monthly_rent = $monthly_rent,
                    deposit = $deposit,
                    payment_cycle = '$rent_cycle',
                    auto_invoice = $auto_invoice,
                    status = '$status',
                    lease_conditions = '$lease_conditions',
                    vehicle_info = '$vehicle_info',
                    legal_weapons = '$legal_weapons',
                    witnesses = '$witnesses_json'
                WHERE id = $id";

        if ($conn->query($sql)) {
            echo json_encode(['error' => false, 'msg' => 'Lease updated successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error updating lease: ' . $conn->error]);
        }
    }
}

/**
 * Delete lease
 */
function delete_lease() {
    header('Content-Type: application/json');
    global $conn;
    
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid lease ID.']);
        exit;
    }

    // Get unit_id before deleting to update unit status
    $lease = $conn->query("SELECT unit_id FROM leases WHERE id = $id")->fetch_assoc();

    $stmt = $conn->prepare("DELETE FROM leases WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Update unit status back to vacant
        if ($lease && $lease['unit_id']) {
            $conn->query("UPDATE units SET status = 'vacant', tenant_id = NULL WHERE id = " . $lease['unit_id']);
        }
        echo json_encode(['error' => false, 'msg' => 'Lease deleted successfully.']);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Error deleting lease: ' . $conn->error]);
    }
}

/**
 * Get single lease for editing/viewing
 */
function get_lease() {
    header('Content-Type: application/json');
    global $conn;
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid lease ID.']);
        exit;
    }

    $sql = "SELECT l.*, 
                   t.full_name as tenant_name,
                   g.full_name as guarantee_name,
                   p.name as property_name,
                   u.unit_number
            FROM leases l
            LEFT JOIN tenants t ON l.tenant_id = t.id
            LEFT JOIN guarantees g ON l.guarantee_id = g.id
            LEFT JOIN properties p ON l.property_id = p.id
            LEFT JOIN units u ON l.unit_id = u.id
            WHERE l.id = $id";

    $result = $conn->query($sql);
    $lease = $result->fetch_assoc();

    if ($lease) {
        // Decode witnesses JSON
        $lease['witnesses'] = json_decode($lease['witnesses'], true) ?? [];
        echo json_encode(['error' => false, 'data' => $lease]);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Lease not found.']);
    }
}

/**
 * Generate reference number for a module
 */
function generate_reference_number($module) {
    global $conn;

    // Get transaction series settings
    $result = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'transaction_series'");
    $row = $result->fetch_assoc();

    if ($row && !empty($row['setting_value'])) {
        $series = json_decode($row['setting_value'], true);
        
        if (isset($series[$module])) {
            $config = $series[$module];
            $prefix = $config['prefix'] ?? '';
            $suffix = $config['suffix'] ?? '';
            $starting = intval($config['starting_number'] ?? 1);
            $current = intval($config['current_number'] ?? 0);

            // Calculate next number
            $next_number = ($current > 0) ? $current + 1 : $starting;

            // Update current number
            $series[$module]['current_number'] = $next_number;
            $updated_series = $conn->real_escape_string(json_encode($series));
            $conn->query("UPDATE system_settings SET setting_value = '$updated_series' WHERE setting_key = 'transaction_series'");

            // Format number with leading zeros
            $formatted_number = str_pad($next_number, 5, '0', STR_PAD_LEFT);

            return $prefix . $formatted_number . $suffix;
        }
    }

    // Fallback
    return strtoupper(substr($module, 0, 3)) . '-' . date('Ymd') . rand(100, 999);
}
?>
