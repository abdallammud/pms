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

function get_expenses() {
    header('Content-Type: application/json');
    global $conn;

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
        $sql .= " AND (p.name LIKE '%$search_value%' OR e.category LIKE '%$search_value%' OR e.description LIKE '%$search_value%')";
    }

    // Total records (before filtering)
    $total_records_query = $conn->query("SELECT COUNT(*) as count FROM expenses");
    $total_records = $total_records_query->fetch_assoc()['count'];

    // Total filtered records
    $filtered_records_query = $conn->query(str_replace("SELECT e.*, p.name as property_name", "SELECT COUNT(*) as count", $sql));
    $filtered_records = $filtered_records_query->fetch_assoc()['count'];

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editExpense('.$row['id'].')"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteExpense('.$row['id'].')"><i class="bi bi-trash"></i></button>';

        $data[] = [
            'id' => $row['id'],
            'property_name' => $row['property_name'],
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

function save_expense() {
    // Placeholder for save functionality
}

function delete_expense() {
    // Placeholder for delete functionality
}

function get_expense() {
    // Placeholder for get single expense functionality
}
?>
