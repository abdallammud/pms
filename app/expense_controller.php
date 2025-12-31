<?php
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get_expenses') {
        get_expenses();
    } elseif ($action == 'save_expense') {
        save_expense();
    } elseif ($action == 'delete_expense') {
        delete_expense();
    } elseif ($action == 'get_expense') {
        get_expense();
    }
}

function get_expenses()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    // Server-side processing for DataTables
    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    // Base query
    $sql = "SELECT e.*, p.name as property_name 
            FROM expenses e 
            LEFT JOIN properties p ON e.property_id = p.id 
            WHERE 1=1";

    // Search
    if (!empty($search_value)) {
        $search_value = $conn->real_escape_string($search_value);
        $sql .= " AND (p.name LIKE '%$search_value%' 
                  OR e.category LIKE '%$search_value%' 
                  OR e.description LIKE '%$search_value%'
                  OR e.expense_type LIKE '%$search_value%'
                  OR e.reference_number LIKE '%$search_value%')";
    }

    // Total records (before filtering)
    $total_records_query = $conn->query("SELECT COUNT(*) as count FROM expenses");
    $total_records = ($total_records_query) ? $total_records_query->fetch_assoc()['count'] : 0;

    // Total filtered records
    $filtered_sql = preg_replace('/SELECT e\.\*.*?FROM/s', 'SELECT COUNT(*) as count FROM', $sql);
    $filtered_records_query = $conn->query($filtered_sql);
    $filtered_records = ($filtered_records_query) ? $filtered_records_query->fetch_assoc()['count'] : 0;

    // Order
    $sql .= " ORDER BY e.expense_date DESC, e.id DESC";

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editExpense(' . $row['id'] . ')" title="Edit"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteExpense(' . $row['id'] . ')" title="Delete"><i class="bi bi-trash"></i></button>';

        $data[] = [
            'id' => $row['id'],
            'reference_number' => $row['reference_number'] ?? 'EXP-' . $row['id'],
            'property_name' => $row['expense_type'] == 'Property' ? ($row['property_name'] ?? 'N/A') : 'Aayatiin',
            'expense_type' => $row['expense_type'],
            'category' => $row['category'],
            'amount' => number_format($row['amount'], 2),
            'expense_date' => $row['expense_date'],
            'description' => $row['description'],
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

function save_expense()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $id = $_POST['expense_id'] ?? '';
    $expense_type = $_POST['expense_type'] ?? 'Property';
    $property_id = ($expense_type == 'Property') ? ($_POST['property_id'] ?? null) : null;
    $category = $_POST['category'] ?? '';
    $amount = floatval($_POST['amount'] ?? 0);
    $expense_date = $_POST['expense_date'] ?? date('Y-m-d');
    $description = $_POST['description'] ?? '';

    // Validation
    if ($expense_type == 'Property' && empty($property_id)) {
        echo json_encode(['error' => true, 'msg' => 'Please select a property.']);
        exit;
    }
    if (empty($category) || $amount <= 0 || empty($expense_date)) {
        echo json_encode(['error' => true, 'msg' => 'Please fill in all required fields and enter a valid amount.']);
        exit;
    }

    $category = $conn->real_escape_string($category);
    $description = $conn->real_escape_string($description);
    $expense_date = $conn->real_escape_string($expense_date);
    $property_id_val = $property_id ? intval($property_id) : "NULL";

    if (empty($id)) {
        // INSERT
        // Generate reference number (simple logic for now, or use same pattern as invoices)
        $reference_number = 'EXP-' . date('Ymd') . '-' . rand(100, 999);

        $sql = "INSERT INTO expenses (property_id, expense_type, category, amount, description, expense_date, reference_number) 
                VALUES ($property_id_val, '$expense_type', '$category', $amount, '$description', '$expense_date', '$reference_number')";

        if ($conn->query($sql)) {
            echo json_encode(['error' => false, 'msg' => 'Expense added successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error adding expense: ' . $conn->error]);
        }
    } else {
        // UPDATE
        $id = intval($id);
        $sql = "UPDATE expenses SET 
                    property_id = $property_id_val, 
                    expense_type = '$expense_type', 
                    category = '$category', 
                    amount = $amount, 
                    description = '$description', 
                    expense_date = '$expense_date' 
                WHERE id = $id";

        if ($conn->query($sql)) {
            echo json_encode(['error' => false, 'msg' => 'Expense updated successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error updating expense: ' . $conn->error]);
        }
    }
}

function delete_expense()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid ID.']);
        exit;
    }

    $sql = "DELETE FROM expenses WHERE id = $id";

    if ($conn->query($sql)) {
        echo json_encode(['error' => false, 'msg' => 'Expense deleted successfully.']);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Error deleting expense: ' . $conn->error]);
    }
}

function get_expense()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid ID']);
        exit;
    }

    $sql = "SELECT * FROM expenses WHERE id = $id";
    $result = $conn->query($sql);
    $data = $result->fetch_assoc();

    if ($data) {
        echo json_encode(['error' => false, 'data' => $data]);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Expense not found.']);
    }
}
?>