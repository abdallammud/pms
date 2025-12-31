<?php
require_once 'init.php';

// Define action from GET
$action = $_GET['action'] ?? '';

if ($action) {
    if (function_exists($action)) {
        $action();
    } else {
        echo json_encode(['error' => true, 'msg' => 'Invalid action']);
    }
}

function get_stats()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    if (!$conn) {
        echo json_encode(['error' => true, 'msg' => 'Database connection failed']);
        exit;
    }

    // 1. Summary Stats
    $total_properties = $conn->query("SELECT COUNT(*) FROM properties")->fetch_row()[0] ?? 0;
    $occupied_units = $conn->query("SELECT COUNT(*) FROM units WHERE status = 'occupied'")->fetch_row()[0] ?? 0;
    $vacant_units = $conn->query("SELECT COUNT(*) FROM units WHERE status = 'vacant'")->fetch_row()[0] ?? 0;
    $active_tenants = $conn->query("SELECT COUNT(DISTINCT tenant_id) FROM leases WHERE status = 'active'")->fetch_row()[0] ?? 0;

    $rent_collected = $conn->query("SELECT SUM(amount_paid) FROM payments_received")->fetch_row()[0] ?? 0;
    $total_invoiced = $conn->query("SELECT SUM(amount) FROM invoices")->fetch_row()[0] ?? 0;
    $outstanding_amount = max(0, $total_invoiced - $rent_collected);

    // 2. Income vs Expense (Last 6 months)
    $months = [];
    $income_data = [];
    $expense_data = [];

    for ($i = 5; $i >= 0; $i--) {
        $month_start = date('Y-m-01', strtotime("-$i months"));
        $month_end = date('Y-m-t', strtotime("-$i months"));
        $month_name = date('M', strtotime("-$i months"));

        $months[] = $month_name;

        $income_query = $conn->query("SELECT SUM(amount_paid) FROM payments_received WHERE received_date BETWEEN '$month_start' AND '$month_end'");
        $income = $income_query ? $income_query->fetch_row()[0] : 0;

        $expense_query = $conn->query("SELECT SUM(amount) FROM expenses WHERE expense_date BETWEEN '$month_start' AND '$month_end'");
        $expense = $expense_query ? $expense_query->fetch_row()[0] : 0;

        $income_data[] = floatval($income ?? 0);
        $expense_data[] = floatval($expense ?? 0);
    }

    // 3. Upcoming Lease Expirations (Next 30 days)
    $upcoming_leases = [];
    $sql = "SELECT l.*, t.full_name as tenant_name, p.name as property_name, u.unit_number 
            FROM leases l 
            JOIN tenants t ON l.tenant_id = t.id 
            JOIN properties p ON l.property_id = p.id 
            JOIN units u ON l.unit_id = u.id 
            WHERE l.end_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY) 
            AND l.status = 'active' 
            ORDER BY l.end_date ASC LIMIT 5";
    $res = $conn->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $upcoming_leases[] = [
                'id' => $row['id'],
                'tenant_name' => $row['tenant_name'],
                'property_name' => $row['property_name'],
                'unit_number' => $row['unit_number'],
                'end_date' => date('M d, Y', strtotime($row['end_date']))
            ];
        }
    }

    // 4. Recently Received Payments
    $recent_payments = [];
    $sql_pay = "SELECT r.*, t.full_name as tenant_name, p.name as property_name 
                FROM payments_received r 
                LEFT JOIN invoices i ON r.invoice_id = i.id
                LEFT JOIN leases l ON i.lease_id = l.id
                LEFT JOIN tenants t ON l.tenant_id = t.id 
                LEFT JOIN properties p ON l.property_id = p.id 
                ORDER BY r.received_date DESC, r.id DESC LIMIT 5";
    $res_pay = $conn->query($sql_pay);
    if ($res_pay) {
        while ($row = $res_pay->fetch_assoc()) {
            $recent_payments[] = [
                'tenant_name' => $row['tenant_name'] ?? 'N/A',
                'property_name' => $row['property_name'] ?? 'N/A',
                'amount_paid' => number_format($row['amount_paid'], 2),
                'received_date' => date('M d, Y', strtotime($row['received_date'])),
                'payment_method' => ucfirst($row['payment_method'])
            ];
        }
    }

    // 5. Recent Maintenance Requests
    $recent_maintenance = [];
    $sql_maint = "SELECT m.*, p.name as property_name, u.unit_number 
                  FROM maintenance_requests m 
                  LEFT JOIN properties p ON m.property_id = p.id 
                  LEFT JOIN units u ON m.unit_id = u.id 
                  ORDER BY m.created_at DESC LIMIT 5";
    $res_maint = $conn->query($sql_maint);
    if ($res_maint) {
        while ($row = $res_maint->fetch_assoc()) {
            $status_class = 'warning';
            if ($row['status'] == 'completed')
                $status_class = 'success';
            if ($row['status'] == 'new')
                $status_class = 'danger';

            $recent_maintenance[] = [
                'property_name' => $row['property_name'] ?? 'N/A',
                'unit_number' => $row['unit_number'] ?? 'N/A',
                'description' => substr($row['description'], 0, 30) . (strlen($row['description']) > 30 ? '...' : ''),
                'status' => ucfirst($row['status']),
                'status_class' => $status_class
            ];
        }
    }

    // 6. Occupancy Rate
    $occupancy_data = [intval($occupied_units), intval($vacant_units)];

    echo json_encode([
        'stats' => [
            'total_properties' => $total_properties,
            'occupied_units' => $occupied_units,
            'vacant_units' => $vacant_units,
            'active_tenants' => $active_tenants,
            'rent_collected' => '$' . number_format($rent_collected, 2),
            'outstanding_amount' => '$' . number_format($outstanding_amount, 2)
        ],
        'charts' => [
            'income_expense' => [
                'labels' => $months,
                'income' => $income_data,
                'expense' => $expense_data
            ],
            'occupancy' => $occupancy_data
        ],
        'recent' => [
            'leases' => $upcoming_leases,
            'payments' => $recent_payments,
            'maintenance' => $recent_maintenance
        ]
    ]);
}
?>