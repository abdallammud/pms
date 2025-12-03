<?php
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'save') {
        if (isset($_GET['endpoint']) && $_GET['endpoint'] == 'user') {
            save_user();
        } elseif (isset($_GET['endpoint']) && $_GET['endpoint'] == 'role') {
            save_role();
        }
    } elseif ($action == 'get_users') {
        get_users();
    } elseif ($action == 'get_roles') {
        get_roles();
    } elseif ($action == 'get_user') {
        get_user();
    } elseif ($action == 'get_role') {
        get_role();
    } elseif ($action == 'delete_user') {
        delete_user();
    } elseif ($action == 'delete_role') {
        delete_role();
    } elseif ($action == 'get_all_permissions') {
        get_all_permissions();
    } elseif ($action == 'get_role_permissions') {
        get_role_permissions();
    }
}

function save_user() {
    header('Content-Type: application/json');
    global $conn;

    $id = $_POST['user_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role_id = $_POST['role_id'] ?? '';
    $status = $_POST['status'] ?? 'active';
    $current_user_id = $_SESSION['user_id'];

    if (empty($name) || empty($username) || empty($email) || empty($role_id)) {
        echo json_encode(['error' => true, 'msg' => 'Please fill in all required fields.']);
        exit;
    }

    if (empty($id)) {
        // Insert
        if (empty($password)) {
            echo json_encode(['error' => true, 'msg' => 'Password is required for new users.']);
            exit;
        }
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (name, username, email, password, status, created_by, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssii", $name, $username, $email, $hashed_password, $status, $current_user_id, $current_user_id);
        
        if ($stmt->execute()) {
            $new_user_id = $stmt->insert_id;
            // Assign Role
            $stmt_role = $conn->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $stmt_role->bind_param("ii", $new_user_id, $role_id);
            $stmt_role->execute();

            echo json_encode(['error' => false, 'msg' => 'User added successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error adding user: ' . $conn->error]);
        }
    } else {
        // Update
        $sql = "UPDATE users SET name=?, username=?, email=?, status=?, updated_by=?, updated_date=NOW()";
        $params = [$name, $username, $email, $status, $current_user_id];
        $types = "ssssi";

        if (!empty($password)) {
            $sql .= ", password=?";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
            $types .= "s";
        }

        $sql .= " WHERE id=?";
        $params[] = $id;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            // Update Role
            $conn->query("DELETE FROM user_roles WHERE user_id = $id");
            $stmt_role = $conn->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $stmt_role->bind_param("ii", $id, $role_id);
            $stmt_role->execute();

            echo json_encode(['error' => false, 'msg' => 'User updated successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error updating user: ' . $conn->error]);
        }
    }
}

function get_users() {
    header('Content-Type: application/json');
    global $conn;

    // Server-side processing for DataTables
    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    // Base query
    $sql = "SELECT u.id, u.name, u.username, u.email, u.status, r.role_name 
            FROM users u 
            LEFT JOIN user_roles ur ON u.id = ur.user_id 
            LEFT JOIN roles r ON ur.role_id = r.id 
            WHERE 1=1";

    // Search
    if (!empty($search_value)) {
        $sql .= " AND (u.name LIKE '%$search_value%' OR u.username LIKE '%$search_value%' OR u.email LIKE '%$search_value%' OR r.role_name LIKE '%$search_value%')";
    }

    // Total records (before filtering)
    $total_records_query = $conn->query("SELECT COUNT(*) as count FROM users");
    $total_records = $total_records_query->fetch_assoc()['count'];

    // Total filtered records
    $filtered_records_query = $conn->query(str_replace("SELECT u.id, u.name, u.username, u.email, u.status, r.role_name", "SELECT COUNT(*) as count", $sql));
    $filtered_records = $filtered_records_query->fetch_assoc()['count'];

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $actionBtn = '<button class="btn btn-sm btn-primary me-1" onclick="editUserModal('.$row['id'].')"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteUser('.$row['id'].')"><i class="bi bi-trash"></i></button>';

        $data[] = [
            'name' => $row['name'],
            'username' => $row['username'],
            'email' => $row['email'],
            'role_name' => $row['role_name'],
            'status' => '<span class="badge bg-'.($row['status'] == 'active' ? 'success' : 'danger').'">'.ucfirst($row['status']).'</span>',
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

function get_user() {
    header('Content-Type: application/json');
    global $conn;
    $id = $_GET['id'];
    
    $stmt = $conn->prepare("SELECT u.*, ur.role_id FROM users u LEFT JOIN user_roles ur ON u.id = ur.user_id WHERE u.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    echo json_encode($result);
}

function delete_user() {
    header('Content-Type: application/json');
    global $conn;
    $id = $_POST['id'];

    // Optional: Check if trying to delete self
    if ($id == $_SESSION['user_id']) {
        echo json_encode(['error' => true, 'msg' => 'You cannot delete yourself.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['error' => false, 'msg' => 'User deleted successfully.']);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Error deleting user: ' . $conn->error]);
    }
}

// Roles
function save_role() {
    header('Content-Type: application/json');
    global $conn;

    $id = isset($_POST['role_id']) && is_numeric($_POST['role_id']) ? (int)$_POST['role_id'] : 0;
    $role_name = trim($_POST['role_name'] ?? '');
    $description = $_POST['description'] ?? '';
    $permissions = $_POST['permissions'] ?? []; // Array of permission IDs

    if (empty($role_name)) {
        echo json_encode(['error' => true, 'msg' => 'Role Name is required.']);
        exit;
    }

    if ($id === 0) {
        // Insert
        // Check if role name exists
        $check = $conn->prepare("SELECT id FROM roles WHERE role_name = ?");
        $check->bind_param("s", $role_name);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(['error' => true, 'msg' => 'Role Name already exists.']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO roles (role_name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $role_name, $description);

        if ($stmt->execute()) {
            $new_role_id = $stmt->insert_id;
            
            // Insert Permissions
            if (!empty($permissions)) {
                $stmt_perm = $conn->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                foreach ($permissions as $perm_id) {
                    $stmt_perm->bind_param("ii", $new_role_id, $perm_id);
                    $stmt_perm->execute();
                }
            }

            echo json_encode(['error' => false, 'msg' => 'Role added successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error adding role: ' . $conn->error]);
        }
    } else {
        // Update
        // Check if role name exists for other roles
        $check = $conn->prepare("SELECT id FROM roles WHERE role_name = ? AND id != ?");
        $check->bind_param("si", $role_name, $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(['error' => true, 'msg' => 'Role Name already exists.']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE roles SET role_name=?, description=? WHERE id=?");
        $stmt->bind_param("ssi", $role_name, $description, $id);

        if ($stmt->execute()) {
            // Update Permissions: Delete all and re-insert
            $conn->query("DELETE FROM role_permissions WHERE role_id = $id");

            if (!empty($permissions)) {
                $stmt_perm = $conn->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                foreach ($permissions as $perm_id) {
                    $stmt_perm->bind_param("ii", $id, $perm_id);
                    $stmt_perm->execute();
                }
            }

            echo json_encode(['error' => false, 'msg' => 'Role updated successfully.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error updating role: ' . $conn->error]);
        }
    }
}

function get_roles() {
    header('Content-Type: application/json');
    global $conn;

    // Server-side processing for DataTables
    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    // Base query
    $sql = "SELECT * FROM roles WHERE 1=1";

    // Search
    if (!empty($search_value)) {
        $sql .= " AND (role_name LIKE '%$search_value%' OR description LIKE '%$search_value%')";
    }

    // Total records (before filtering)
    $total_records_query = $conn->query("SELECT COUNT(*) as count FROM roles");
    $total_records = $total_records_query->fetch_assoc()['count'];

    // Total filtered records
    $filtered_records_query = $conn->query(str_replace("SELECT *", "SELECT COUNT(*) as count", $sql));
    $filtered_records = $filtered_records_query->fetch_assoc()['count'];

    // Pagination
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $actionBtn = '<button class="btn btn-sm btn-info me-1" onclick="viewPermissions('.$row['id'].')" title="View Permissions"><i class="bi bi-eye"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-primary me-1" onclick="editRole('.$row['id'].')"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteRole('.$row['id'].')"><i class="bi bi-trash"></i></button>';

        $data[] = [
            'role_name' => $row['role_name'],
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

function get_role() {
    header('Content-Type: application/json');
    global $conn;
    $id = $_GET['id'];
    
    // Get Role Details
    $stmt = $conn->prepare("SELECT * FROM roles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $role = $stmt->get_result()->fetch_assoc();

    // Get Role Permissions
    $stmt_perms = $conn->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
    $stmt_perms->bind_param("i", $id);
    $stmt_perms->execute();
    $result_perms = $stmt_perms->get_result();
    $permissions = [];
    while ($row = $result_perms->fetch_assoc()) {
        $permissions[] = $row['permission_id'];
    }

    $role['permissions'] = $permissions;
    
    echo json_encode($role);
}

function delete_role() {
    header('Content-Type: application/json');
    global $conn;
    $id = $_POST['id'];

    // Prevent deleting Admin role (assuming ID 1 or name Admin)
    // Let's check name
    $check = $conn->query("SELECT role_name FROM roles WHERE id = $id")->fetch_assoc();
    if ($check && $check['role_name'] == 'Admin') {
        echo json_encode(['error' => true, 'msg' => 'Cannot delete Admin role.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM roles WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['error' => false, 'msg' => 'Role deleted successfully.']);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Error deleting role: ' . $conn->error]);
    }
}

function get_all_permissions() {
    header('Content-Type: application/json');
    global $conn;
    
    $result = $conn->query("SELECT * FROM permissions ORDER BY permission_name");
    $permissions = [];
    while ($row = $result->fetch_assoc()) {
        $permissions[] = $row;
    }
    
    echo json_encode($permissions);
}

function get_role_permissions() {
    header('Content-Type: application/json');
    global $conn;
    $id = $_GET['id'];
    
    $stmt = $conn->prepare("SELECT r.role_name, p.permission_name, p.description 
                           FROM role_permissions rp 
                           JOIN permissions p ON rp.permission_id = p.id 
                           JOIN roles r ON rp.role_id = r.id
                           WHERE rp.role_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $permissions = [];
    $role_name = '';
    
    while ($row = $result->fetch_assoc()) {
        $role_name = $row['role_name'];
        $permissions[] = $row;
    }
    
    echo json_encode(['role_name' => $role_name, 'permissions' => $permissions]);
}
?>
