<?php
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get_requests') {
        get_requests();
    } elseif ($action == 'save_request') {
        save_request();
    } elseif ($action == 'delete_request') {
        delete_request();
    } elseif ($action == 'get_request') {
        get_request();
    } elseif ($action == 'get_available_units') {
        get_available_units();
    } elseif ($action == 'get_pending_requests') {
        get_pending_requests();
    } elseif ($action == 'assign_request') {
        assign_request();
    } elseif ($action == 'get_assignment') {
        get_assignment();
    }
}

function get_requests()
{
    $conn = $GLOBALS['conn'];

    // Server-side processing for DataTables
    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    // Base query
    $sql = "SELECT m.*, p.name as property_name, u.unit_number, v.vendor_name 
            FROM maintenance_requests m 
            LEFT JOIN properties p ON m.property_id = p.id 
            LEFT JOIN units u ON m.unit_id = u.id 
            LEFT JOIN maintenance_assignments ma ON m.id = ma.request_id 
            LEFT JOIN vendors v ON ma.vendor_id = v.id 
            WHERE 1=1";

    // Search
    if (!empty($search_value)) {
        $search_value = $conn->real_escape_string($search_value);
        $sql .= " AND (p.name LIKE '%$search_value%' 
                    OR u.unit_number LIKE '%$search_value%' 
                    OR m.description LIKE '%$search_value%' 
                    OR m.reference_number LIKE '%$search_value%'
                    OR v.vendor_name LIKE '%$search_value%'
                    OR m.requester LIKE '%$search_value%')";
    }

    // Total records (before filtering)
    $total_records_res = $conn->query("SELECT COUNT(*) as count FROM maintenance_requests");
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
    $sql .= " ORDER BY m.id DESC";

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $actionBtn = '<button class="btn btn-sm btn-success me-1" onclick="assignRequest(' . $row['id'] . ')" title="Assign"><i class="bi bi-person-plus"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-primary me-1" onclick="editRequest(' . $row['id'] . ')" title="Edit"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteRequest(' . $row['id'] . ')" title="Delete"><i class="bi bi-trash"></i></button>';

        $statusBadge = '';
        if ($row['status'] == 'new') {
            $statusBadge = '<span class="badge bg-info">New</span>';
        } elseif ($row['status'] == 'in_progress') {
            $statusBadge = '<span class="badge bg-warning">In Progress</span>';
        } elseif ($row['status'] == 'completed') {
            $statusBadge = '<span class="badge bg-success">Completed</span>';
        }

        $priorityBadge = '';
        if ($row['priority'] == 'low') {
            $priorityBadge = '<span class="badge bg-success">Low</span>';
        } elseif ($row['priority'] == 'medium') {
            $priorityBadge = '<span class="badge bg-warning">Medium</span>';
        } elseif ($row['priority'] == 'high') {
            $priorityBadge = '<span class="badge bg-danger">High</span>';
        }

        $data[] = [
            'id' => $row['id'],
            'reference_number' => $row['reference_number'],
            'property_name' => $row['property_name'],
            'unit_number' => $row['unit_number'] ?? 'N/A',
            'priority' => $priorityBadge,
            'description' => htmlspecialchars($row['description']),
            'assigned_to' => $row['vendor_name'] ?? '<span class="text-muted">Unassigned</span>',
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

function save_request()
{
    $conn = $GLOBALS['conn'];

    $id = $_POST['request_id'] ?? '';
    $property_id = $_POST['property_id'] ?? '';
    $unit_id = !empty($_POST['unit_id']) ? $_POST['unit_id'] : null;
    $priority = $_POST['priority'] ?? 'medium';
    $requester = trim($_POST['requester'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'new';

    if (empty($property_id) || empty($description) || empty($requester)) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => true, 'msg' => 'Property, requester, and description are required.']);
        exit;
    }

    if (empty($id)) {
        // Insert
        // Generate reference number
        $reference_number = generate_reference_number('maintenance');

        $stmt = $conn->prepare("INSERT INTO maintenance_requests (reference_number, property_id, unit_id, priority, requester, description, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siissss", $reference_number, $property_id, $unit_id, $priority, $requester, $description, $status);

        if ($stmt->execute()) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => false, 'msg' => 'Maintenance request created successfully.', 'id' => $conn->insert_id]);
        } else {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => true, 'msg' => 'Error creating request: ' . $conn->error]);
        }
    } else {
        // Update
        $stmt = $conn->prepare("UPDATE maintenance_requests SET property_id=?, unit_id=?, priority=?, requester=?, description=?, status=? WHERE id=?");
        $stmt->bind_param("iissssi", $property_id, $unit_id, $priority, $requester, $description, $status, $id);

        if ($stmt->execute()) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => false, 'msg' => 'Maintenance request updated successfully.']);
        } else {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => true, 'msg' => 'Error updating request: ' . $conn->error]);
        }
    }
}

function delete_request()
{
    $conn = $GLOBALS['conn'];
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => true, 'msg' => 'Invalid ID.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM maintenance_requests WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => false, 'msg' => 'Maintenance request deleted successfully.']);
    } else {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => true, 'msg' => 'Error deleting request: ' . $conn->error]);
    }
}

function get_request()
{
    $conn = $GLOBALS['conn'];
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => true, 'msg' => 'Invalid ID.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM maintenance_requests WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    ob_clean();
    header('Content-Type: application/json');
    if ($result) {
        echo json_encode(['error' => false, 'data' => $result]);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Request not found.']);
    }
}

function get_available_units()
{
    $conn = $GLOBALS['conn'];
    $property_id = isset($_POST['property_id']) ? intval($_POST['property_id']) : 0;

    $stmt = $conn->prepare("SELECT id, unit_number FROM units WHERE property_id = ? ORDER BY unit_number");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $units = [];
    while ($row = $result->fetch_assoc()) {
        $units[] = $row;
    }

    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['error' => false, 'data' => $units]);
}

function get_pending_requests()
{
    $conn = $GLOBALS['conn'];

    // Get requests that are not completed
    $sql = "SELECT m.id, m.reference_number, m.description, p.name as property_name, u.unit_number 
            FROM maintenance_requests m 
            LEFT JOIN properties p ON m.property_id = p.id 
            LEFT JOIN units u ON m.unit_id = u.id 
            WHERE m.status != 'completed' 
            ORDER BY m.created_at DESC";

    $result = $conn->query($sql);
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = [
            'id' => $row['id'],
            'reference_number' => $row['reference_number'],
            'label' => $row['reference_number'] . ' - ' . $row['property_name'] . ($row['unit_number'] ? ' / ' . $row['unit_number'] : '') . ' - ' . substr($row['description'], 0, 50)
        ];
    }

    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['error' => false, 'data' => $requests]);
}

function assign_request()
{
    $conn = $GLOBALS['conn'];

    $assignment_id = $_POST['assignment_id'] ?? '';
    $request_id = $_POST['request_id'] ?? '';
    $vendor_id = $_POST['vendor_id'] ?? '';
    $assigned_date = $_POST['assigned_date'] ?? '';
    $expected_completion = $_POST['expected_completion'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if (empty($request_id) || empty($vendor_id) || empty($assigned_date)) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => true, 'msg' => 'Request, vendor, and assigned date are required.']);
        exit;
    }

    if (empty($assignment_id)) {
        // Check if already assigned
        $check = $conn->prepare("SELECT id FROM maintenance_assignments WHERE request_id = ?");
        $check->bind_param("i", $request_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => true, 'msg' => 'This request is already assigned. Please edit the existing assignment.']);
            exit;
        }

        // Insert new assignment
        $stmt = $conn->prepare("INSERT INTO maintenance_assignments (request_id, vendor_id, assigned_date, expected_completion, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $request_id, $vendor_id, $assigned_date, $expected_completion, $notes);

        if ($stmt->execute()) {
            // Update request status to in_progress
            $conn->query("UPDATE maintenance_requests SET status = 'in_progress' WHERE id = $request_id");

            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => false, 'msg' => 'Request assigned successfully.']);
        } else {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => true, 'msg' => 'Error assigning request: ' . $conn->error]);
        }
    } else {
        // Update existing assignment
        $stmt = $conn->prepare("UPDATE maintenance_assignments SET vendor_id=?, assigned_date=?, expected_completion=?, notes=? WHERE id=?");
        $stmt->bind_param("isssi", $vendor_id, $assigned_date, $expected_completion, $notes, $assignment_id);

        if ($stmt->execute()) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => false, 'msg' => 'Assignment updated successfully.']);
        } else {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => true, 'msg' => 'Error updating assignment: ' . $conn->error]);
        }
    }
}

function get_assignment()
{
    $conn = $GLOBALS['conn'];
    $request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;

    if ($request_id <= 0) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => true, 'msg' => 'Invalid request ID.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT ma.*, v.vendor_name FROM maintenance_assignments ma 
                            LEFT JOIN vendors v ON ma.vendor_id = v.id 
                            WHERE ma.request_id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    ob_clean();
    header('Content-Type: application/json');
    if ($result) {
        echo json_encode(['error' => false, 'data' => $result]);
    } else {
        echo json_encode(['error' => false, 'data' => null, 'msg' => 'No assignment found for this request.']);
    }
}
