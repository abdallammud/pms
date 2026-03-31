<?php
require_once 'init.php';

// Define action from GET
$action = $_GET['action'] ?? '';

$allowed_actions = ['get_stats', 'get_receivables', 'get_lease_summary', 'get_maintenance_queue'];
if ($action && in_array($action, $allowed_actions)) {
    $action();
} elseif ($action) {
    echo json_encode(['error' => true, 'msg' => 'Invalid action']);
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
    $tw = tenant_where_clause();
    $total_properties = $conn->query("SELECT COUNT(*) FROM properties WHERE $tw")->fetch_row()[0] ?? 0;
    $occupied_units = $conn->query("SELECT COUNT(*) FROM units WHERE status = 'occupied' AND $tw")->fetch_row()[0] ?? 0;
    $vacant_units = $conn->query("SELECT COUNT(*) FROM units WHERE status = 'vacant' AND $tw")->fetch_row()[0] ?? 0;
    $active_tenants = $conn->query("SELECT COUNT(DISTINCT tenant_id) FROM leases WHERE status = 'active' AND $tw")->fetch_row()[0] ?? 0;

    $rent_collected = $conn->query("SELECT SUM(amount_paid) FROM payments_received WHERE $tw")->fetch_row()[0] ?? 0;
    $total_invoiced = $conn->query("SELECT SUM(amount) FROM invoices WHERE $tw")->fetch_row()[0] ?? 0;
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

        $income_query = $conn->query("SELECT SUM(amount_paid) FROM payments_received WHERE received_date BETWEEN '$month_start' AND '$month_end' AND $tw");
        $income = $income_query ? $income_query->fetch_row()[0] : 0;

        $expense_query = $conn->query("SELECT SUM(amount) FROM expenses WHERE expense_date BETWEEN '$month_start' AND '$month_end' AND $tw");
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
            AND l.status = 'active' AND " . tenant_where_clause('l') . "
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
                WHERE " . tenant_where_clause('r') . "
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
                  WHERE " . tenant_where_clause('m') . "
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

    // 7. This-month rent collected
    $cur_month_start = date('Y-m-01');
    $cur_month_end   = date('Y-m-t');
    $this_month_income = $conn->query("SELECT COALESCE(SUM(amount_paid),0) FROM payments_received WHERE received_date BETWEEN '$cur_month_start' AND '$cur_month_end' AND $tw")->fetch_row()[0] ?? 0;

    // 8. Maintenance counts
    $maint_new  = $conn->query("SELECT COUNT(*) FROM maintenance_requests WHERE status='new' AND $tw")->fetch_row()[0] ?? 0;
    $maint_prog = $conn->query("SELECT COUNT(*) FROM maintenance_requests WHERE status='in_progress' AND $tw")->fetch_row()[0] ?? 0;

    // 9. Total units
    $total_units = $conn->query("SELECT COUNT(*) FROM units WHERE $tw")->fetch_row()[0] ?? 0;
    $occupancy_pct = $total_units > 0 ? round(($occupied_units / $total_units) * 100) : 0;

    echo json_encode([
        'stats' => [
            'total_properties'   => intval($total_properties),
            'total_units'        => intval($total_units),
            'occupied_units'     => intval($occupied_units),
            'vacant_units'       => intval($vacant_units),
            'active_tenants'     => intval($active_tenants),
            'rent_collected'     => '$' . number_format($rent_collected, 2),
            'this_month_income'  => '$' . number_format($this_month_income, 2),
            'outstanding_amount' => '$' . number_format($outstanding_amount, 2),
            'occupancy_pct'      => $occupancy_pct,
            'maint_new'          => intval($maint_new),
            'maint_in_progress'  => intval($maint_prog),
        ],
        'charts' => [
            'income_expense' => [
                'labels'  => $months,
                'income'  => $income_data,
                'expense' => $expense_data
            ],
            'occupancy' => $occupancy_data
        ],
        'recent' => [
            'leases'      => $upcoming_leases,
            'payments'    => $recent_payments,
            'maintenance' => $recent_maintenance
        ]
    ]);
}

/**
 * Unpaid / partial invoices for Receivables widget
 */
function get_receivables()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];
    $tw = tenant_where_clause('i');

    $res = $conn->query("
        SELECT i.id, i.reference_number, i.amount, i.status, i.due_date,
               t.full_name AS tenant_name, u.unit_number,
               COALESCE(SUM(ii.amount_paid),0) AS paid,
               (i.amount - COALESCE(SUM(ii.amount_paid),0)) AS balance
        FROM invoices i
        LEFT JOIN leases l  ON l.id  = i.lease_id
        LEFT JOIN tenants t ON t.id  = l.tenant_id
        LEFT JOIN units u   ON u.id  = l.unit_id
        LEFT JOIN invoice_items ii ON ii.invoice_id = i.id
        WHERE i.status IN ('unpaid','partial') AND $tw
        GROUP BY i.id
        ORDER BY i.due_date ASC
        LIMIT 10
    ");

    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $r['balance'] = max(0, floatval($r['balance']));
        $rows[] = $r;
    }
    echo json_encode($rows);
}

/**
 * Lease status counts
 */
function get_lease_summary()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];
    $tw = tenant_where_clause();

    $active    = $conn->query("SELECT COUNT(*) FROM leases WHERE status='active' AND $tw")->fetch_row()[0] ?? 0;
    $expiring  = $conn->query("SELECT COUNT(*) FROM leases WHERE status='active' AND end_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY) AND $tw")->fetch_row()[0] ?? 0;
    $expired   = $conn->query("SELECT COUNT(*) FROM leases WHERE status='expired' AND $tw")->fetch_row()[0] ?? 0;
    $terminated= $conn->query("SELECT COUNT(*) FROM leases WHERE status='terminated' AND $tw")->fetch_row()[0] ?? 0;

    echo json_encode([
        'active'     => intval($active),
        'expiring'   => intval($expiring),
        'expired'    => intval($expired),
        'terminated' => intval($terminated),
    ]);
}

/**
 * Top open maintenance requests
 */
function get_maintenance_queue()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $res = $conn->query("
        SELECT m.id, m.reference_number, m.description, m.priority, m.status,
               p.name AS property_name, u.unit_number,
               v.vendor_name AS assigned_to
        FROM maintenance_requests m
        LEFT JOIN properties p ON p.id = m.property_id
        LEFT JOIN units u      ON u.id  = m.unit_id
        LEFT JOIN maintenance_assignments ma ON ma.request_id = m.id
        LEFT JOIN vendors v ON v.id = ma.vendor_id
        WHERE m.status != 'completed' AND " . tenant_where_clause('m') . "
        ORDER BY FIELD(m.priority,'high','medium','low'), m.created_at ASC
        LIMIT 5
    ");

    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode($rows);
}
?>