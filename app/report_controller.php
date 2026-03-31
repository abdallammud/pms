<?php
class ReportController extends Model
{

    public function __construct()
    {
        parent::__construct('system_settings'); // Pass any existing table to satisfy the Model
    }

    /**
     * Common method to fetch data for reports
     * Placeholder for now, will be expanded per report
     */
    public function getReportData($type, $filters = [])
    {
        // Logic for each report will go here
        return [];
    }

    /**
     * Fetch rent collection data based on date range and property
     */
    public function getRentCollectionData($filters)
    {
        $startDate = $filters['startDate'] ?? null;
        $endDate = $filters['endDate'] ?? null;
        $propertyId = $filters['property_id'] ?? null;

        $sql = "
            SELECT 
                pr.received_date,
                pr.amount_paid,
                pr.receipt_number,
                t.full_name AS tenant_name,
                p.name AS property_name,
                u.unit_number
            FROM payments_received pr
            JOIN invoices i ON pr.invoice_id = i.id
            JOIN leases l ON i.lease_id = l.id
            JOIN tenants t ON l.tenant_id = t.id
            JOIN properties p ON l.property_id = p.id
            JOIN units u ON l.unit_id = u.id
            WHERE " . tenant_where_clause('pr');
        $params = [];
        $types = '';
        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND pr.received_date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
            $types .= 'ss';
        }
        if (!empty($propertyId)) {
            $sql .= " AND p.id = ?";
            $params[] = $propertyId;
            $types .= 'i';
        }
        $sql .= " ORDER BY pr.received_date DESC";
        return $this->query($sql, $params, $types);
    }

    /**
     * Fetch unit occupancy data
     */
    public function getUnitOccupancyData($filters)
    {
        $propertyId = $filters['property_id'] ?? null;

        $sql = "
            SELECT 
                p.name AS property_name,
                u.unit_number,
                u.unit_type,
                u.status,
                t.full_name AS tenant_name
            FROM units u
            JOIN properties p ON u.property_id = p.id
            LEFT JOIN tenants t ON u.tenant_id = t.id
            WHERE " . tenant_where_clause('u') . "
        ";

        $params = [];
        $types = '';

        if (!empty($propertyId)) {
            $sql .= " AND p.id = ?";
            $params[] = $propertyId;
            $types .= 'i';
        }

        $sql .= " ORDER BY p.name ASC, u.unit_number ASC";

        return $this->query($sql, $params, $types);
    }

    /**
     * Fetch tenant report data
     */
    public function getTenantReportData($filters)
    {
        $status = $filters['tenant_status'] ?? 'all';

        $sql = "SELECT full_name, phone, email, status, created_at FROM tenants WHERE " . tenant_where_clause();
        $params = [];
        $types = '';

        if ($status != 'all') {
            $sql .= " AND status = ?";
            $params[] = $status;
            $types .= 's';
        }

        $sql .= " ORDER BY full_name ASC";

        return $this->query($sql, $params, $types);
    }

    /**
     * Fetch outstanding balance data
     */
    public function getOutstandingBalanceData($filters)
    {
        $startDate = $filters['startDate'];
        $endDate = $filters['endDate'];
        $propertyId = $filters['property_id'] ?? null;

        $sql = "
            SELECT 
                i.invoice_number,
                i.invoice_date,
                i.due_date,
                i.amount,
                i.status,
                t.full_name AS tenant_name,
                p.name AS property_name,
                u.unit_number
            FROM invoices i
            JOIN leases l ON i.lease_id = l.id
            JOIN tenants t ON l.tenant_id = t.id
            JOIN properties p ON l.property_id = p.id
            JOIN units u ON l.unit_id = u.id
            WHERE i.status != 'paid'
            AND " . tenant_where_clause('i') . "
            AND i.invoice_date BETWEEN ? AND ?
        ";

        $params = [$startDate, $endDate];
        $types = 'ss';

        if (!empty($propertyId)) {
            $sql .= " AND p.id = ?";
            $params[] = $propertyId;
            $types .= 'i';
        }

        $sql .= " ORDER BY i.due_date ASC";

        return $this->query($sql, $params, $types);
    }

    /**
     * Fetch income vs expense data (Ledger view)
     */
    public function getIncomeExpenseData($filters)
    {
        $startDate = $filters['startDate'];
        $endDate = $filters['endDate'];
        $propertyId = $filters['property_id'] ?? null;

        // UNION query to get both types of transactions
        $sql = "
            (SELECT 
                pr.received_date AS trans_date,
                'Income' AS type,
                t.full_name AS details,
                pr.amount_paid AS amount,
                p.name AS property_name
            FROM payments_received pr
            JOIN invoices i ON pr.invoice_id = i.id
            JOIN leases l ON i.lease_id = l.id
            JOIN tenants t ON l.tenant_id = t.id
            JOIN properties p ON l.property_id = p.id
            WHERE " . tenant_where_clause('pr') . "
            AND pr.received_date BETWEEN ? AND ?
            " . (!empty($propertyId) ? " AND p.id = ?" : "") . ")
            
            UNION ALL
            
            (SELECT 
                e.expense_date AS trans_date,
                'Expense' AS type,
                CONCAT(e.category, ': ', e.description) AS details,
                e.amount * -1 AS amount,
                p.name AS property_name
            FROM expenses e
            JOIN properties p ON e.property_id = p.id
            WHERE " . tenant_where_clause('e') . "
            AND e.expense_date BETWEEN ? AND ?
            " . (!empty($propertyId) ? " AND p.id = ?" : "") . ")
            
            ORDER BY trans_date ASC
        ";

        $params = [];
        $types = '';

        // Income params
        $params[] = $startDate;
        $params[] = $endDate;
        $types .= 'ss';
        if (!empty($propertyId)) {
            $params[] = $propertyId;
            $types .= 'i';
        }

        // Expense params
        $params[] = $startDate;
        $params[] = $endDate;
        $types .= 'ss';
        if (!empty($propertyId)) {
            $params[] = $propertyId;
            $types .= 'i';
        }

        return $this->query($sql, $params, $types);
    }

    /**
     * Fetch maintenance report data
     */
    public function getMaintenanceReportData($filters)
    {
        $startDate = $filters['startDate'];
        $endDate = $filters['endDate'];
        $propertyId = $filters['property_id'] ?? null;

        $sql = "
            SELECT 
                mr.reference_number,
                mr.description,
                mr.status,
                mr.priority,
                mr.created_at,
                p.name AS property_name,
                u.unit_number
            FROM maintenance_requests mr
            JOIN properties p ON mr.property_id = p.id
            LEFT JOIN units u ON mr.unit_id = u.id
            WHERE " . tenant_where_clause('mr') . "
            AND mr.created_at BETWEEN ? AND ?
        ";

        $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
        $types = 'ss';

        if (!empty($propertyId)) {
            $sql .= " AND p.id = ?";
            $params[] = $propertyId;
            $types .= 'i';
        }

        $sql .= " ORDER BY mr.created_at DESC";

        return $this->query($sql, $params, $types);
    }

    /**
     * Fetch maintenance expense report data
     */
    public function getMaintenanceExpenseData($filters)
    {
        $startDate = $filters['startDate'];
        $endDate = $filters['endDate'];
        $propertyId = $filters['property_id'] ?? null;

        $sql = "
            SELECT 
                e.reference_number,
                e.category,
                e.amount,
                e.description,
                e.expense_date,
                p.name AS property_name
            FROM expenses e
            JOIN properties p ON e.property_id = p.id
            WHERE " . tenant_where_clause('e') . "
            AND (e.category LIKE '%maintenance%' OR e.category LIKE '%repair%')
            AND e.expense_date BETWEEN ? AND ?
        ";

        $params = [$startDate, $endDate];
        $types = 'ss';

        if (!empty($propertyId)) {
            $sql .= " AND p.id = ?";
            $params[] = $propertyId;
            $types .= 'i';
        }

        $sql .= " ORDER BY e.expense_date DESC";

        return $this->query($sql, $params, $types);
    }
}

// Handler for AJAX requests
if (isset($_GET['action'])) {
    require_once 'init.php';
    $report = new ReportController();

    if ($_GET['action'] == 'get_report_data') {
        $type = $_GET['report_type'] ?? '';
        $filters = $_GET;

        $data = [];
        if ($type == 'rent_collection') {
            $data = $report->getRentCollectionData($filters);
        } elseif ($type == 'unit_occupancy') {
            $data = $report->getUnitOccupancyData($filters);
        } elseif ($type == 'tenant_report') {
            $data = $report->getTenantReportData($filters);
        } elseif ($type == 'outstanding_balance') {
            $data = $report->getOutstandingBalanceData($filters);
        } elseif ($type == 'income_expense') {
            $data = $report->getIncomeExpenseData($filters);
        } elseif ($type == 'maintenance_report') {
            $data = $report->getMaintenanceReportData($filters);
        } elseif ($type == 'maintenance_expense') {
            $data = $report->getMaintenanceExpenseData($filters);
        }

        echo json_encode(['data' => $data]);
        exit;
    }
}
?>