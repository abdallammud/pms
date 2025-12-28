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
        } elseif ($action == 'bulk_action') {
                bulk_action();
        }
}


/**
 * Get leases for DataTable
 */
function get_leases()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];

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
        $total_records_res = $conn->query("SELECT COUNT(*) as count FROM leases");
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
        $sql .= " ORDER BY l.id DESC";

        // Pagination
        $sql .= " LIMIT $start, $length";

        $result = $conn->query($sql);
        $data = [];

        while ($row = $result->fetch_assoc()) {
                // Action buttons
                $actionBtn = '<button class="btn btn-sm btn-info me-1" onclick="viewLease(' . $row['id'] . ')" title="View"><i
                class="bi bi-eye"></i></button>';
                $actionBtn .= '<button class="btn btn-sm btn-primary me-1" onclick="editLease(' . $row['id'] . ')" title="Edit"><i
                class="bi bi-pencil"></i></button>';
                $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteLease(' . $row['id'] . ')" title="Delete"><i
                class="bi bi-trash"></i></button>';

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
                        'id' => $row['id'],
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
 * Save lease (create or update)
 */
function save_lease()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];

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
                                'name' =>
                                        $witness_names[$i],
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
function delete_lease()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];

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
function get_lease()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];

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
 * Bulk actions
 */
function bulk_action()
{
        ob_clean();
        header('Content-Type: application/json');
        $conn = $GLOBALS['conn'];

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
                // Free up units first
                // Update units associated with these leases
                $update_units = "UPDATE units u
                        INNER JOIN leases l ON u.id = l.unit_id
                        SET u.status = 'vacant', u.tenant_id = NULL
                        WHERE l.id IN ($ids_str)";
                $conn->query($update_units);

                // Delete leases
                if ($conn->query("DELETE FROM leases WHERE id IN ($ids_str)")) {
                        echo json_encode(['error' => false, 'msg' => 'Selected leases deleted successfully.']);
                } else {
                        echo json_encode(['error' => true, 'msg' => 'Error deleting leases: ' . $conn->error]);
                }

        } elseif ($action_type == 'terminate') {
                if ($conn->query("UPDATE leases SET status = 'terminated' WHERE id IN ($ids_str)")) {
                        echo json_encode(['error' => false, 'msg' => 'Selected leases terminated successfully.']);
                } else {
                        echo json_encode(['error' => true, 'msg' => 'Error terminating leases: ' . $conn->error]);
                }

        } elseif ($action_type == 'auto_rent_invoice') {
                // Generate rent invoices for selected leases
                $billing_month = intval($_POST['billing_month'] ?? date('m'));
                $billing_year = intval($_POST['billing_year'] ?? date('Y'));

                $results = [];
                $success_count = 0;
                $skipped_count = 0;
                $failed_count = 0;

                foreach ($ids as $lease_id) {
                        $lease_id = intval($lease_id);
                        $result = ['lease_id' => $lease_id, 'status' => '', 'message' => ''];

                        // Get lease info
                        $lease = $conn->query("
                        SELECT l.id, l.monthly_rent, l.status, l.auto_invoice,
                        t.full_name as tenant_name, u.unit_number
                        FROM leases l
                        LEFT JOIN tenants t ON l.tenant_id = t.id
                        LEFT JOIN units u ON l.unit_id = u.id
                        WHERE l.id = $lease_id
                        ")->fetch_assoc();

                        if (!$lease) {
                                $result['status'] = 'failed';
                                $result['message'] = 'Lease not found';
                                $failed_count++;
                                $results[] = $result;
                                continue;
                        }

                        $result['tenant_name'] = $lease['tenant_name'];
                        $result['unit_number'] = $lease['unit_number'];

                        // Check lease is active
                        if ($lease['status'] != 'active') {
                                $result['status'] = 'skipped';
                                $result['message'] = 'Lease is not active';
                                $skipped_count++;
                                $results[] = $result;
                                continue;
                        }

                        // Check for duplicate rent invoice
                        $dup_check = $conn->query("
                        SELECT id FROM invoices
                        WHERE lease_id = $lease_id
                        AND invoice_type = 'rent'
                        AND billing_month = $billing_month
                        AND billing_year = $billing_year
                        LIMIT 1
                        ");

                        if ($dup_check->num_rows > 0) {
                                $result['status'] = 'skipped';
                                $result['message'] = 'Already invoiced for this period';
                                $skipped_count++;
                                $results[] = $result;
                                continue;
                        }

                        // Generate invoice reference
                        $reference_number = generate_reference_number('rent_invoice');

                        $amount = floatval($lease['monthly_rent']);
                        $invoice_date = date('Y-m-d');
                        $due_date = sprintf('%04d-%02d-01', $billing_year, $billing_month);

                        $sql = "INSERT INTO invoices (
                        invoice_type, lease_id, reference_number, amount,
                        invoice_date, due_date, billing_month, billing_year, status
                        ) VALUES (
                        'rent', $lease_id, '$reference_number', $amount,
                        '$invoice_date', '$due_date', $billing_month, $billing_year, 'unpaid'
                        )";

                        if ($conn->query($sql)) {
                                $result['status'] = 'success';
                                $result['message'] = 'Invoice created: ' . $reference_number;
                                $result['invoice_id'] = $conn->insert_id;
                                $result['reference_number'] = $reference_number;
                                $success_count++;
                        } else {
                                $result['status'] = 'failed';
                                $result['message'] = 'Database error';
                                $failed_count++;
                        }

                        $results[] = $result;
                }

                echo json_encode([
                        'error' => false,
                        'msg' => "$success_count generated, $skipped_count skipped, $failed_count failed",
                        'summary' => [
                                'success' => $success_count,
                                'skipped' => $skipped_count,
                                'failed' => $failed_count,
                                'total' => count($ids)
                        ],
                        'results' => $results
                ]);

        } else {
                echo json_encode(['error' => true, 'msg' => 'Invalid action type.']);
        }
}