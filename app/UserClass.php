<?php
class UserClass {
    private $conn;

    public function __construct() {
        $this->conn = $GLOBALS['conn'];
    }

    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT id, name, email, password, status FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                if ($user['status'] === 'active') {
                    return $user;
                } else {
                    return ['error' => 'Account is inactive.'];
                }
            } else {
                return ['error' => 'Invalid password.'];
            }
        } else {
            return ['error' => 'User not found.'];
        }
    }

    public function get($id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getPermissions($userId) {
        $permissions = [];
        
        // Get roles for the user
        $stmt = $this->conn->prepare("
            SELECT p.permission_name 
            FROM user_roles ur
            JOIN role_permissions rp ON ur.role_id = rp.role_id
            JOIN permissions p ON rp.permission_id = p.id
            WHERE ur.user_id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row['permission_name'];
        }
        
        return array_unique($permissions);
    }

    public function getRoleInfo($userId) {
        $stmt = $this->conn->prepare("SELECT role_id, role_name FROM user_roles ur INNER JOIN roles r ON ur.role_id = r.id WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}

$GLOBALS['userClass'] = new UserClass();
?>
