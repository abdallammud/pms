<?php
class UserClass
{
    private $conn;

    public function __construct()
    {
        $this->conn = $GLOBALS['conn'];
    }

    public function login($email, $password)
    {
        $stmt = $this->conn->prepare("SELECT id, name, username, email, password, status, org_id, is_super_admin FROM users WHERE email = ? OR username = ?");
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

    public function get($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getPermissions($userId)
    {
        $permissions = [];

        // Get the user's org_id so we only load permissions from roles
        // that belong to their own organization
        $user = $this->get($userId);
        if (!$user) {
            return [];
        }

        $org_id = (int) ($user['org_id'] ?? 0);

        // Super admins get all permissions without restriction
        if (!empty($user['is_super_admin'])) {
            $result = $this->conn->query("SELECT permission_name FROM permissions ORDER BY permission_name");
            while ($row = $result->fetch_assoc()) {
                $permissions[] = $row['permission_name'];
            }
            return array_unique($permissions);
        }

        // Regular users: only permissions from roles belonging to their org
        $stmt = $this->conn->prepare("
            SELECT p.permission_name 
            FROM user_roles ur
            JOIN roles r ON ur.role_id = r.id
            JOIN role_permissions rp ON r.id = rp.role_id
            JOIN permissions p ON rp.permission_id = p.id
            WHERE ur.user_id = ?
              AND r.org_id = ?
        ");
        $stmt->bind_param("ii", $userId, $org_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row['permission_name'];
        }

        return array_unique($permissions);
    }

    public function getRoleInfo($userId)
    {
        // Join through roles to ensure we only return role info from the user's own org
        $stmt = $this->conn->prepare("
            SELECT ur.role_id, r.role_name 
            FROM user_roles ur 
            INNER JOIN roles r ON ur.role_id = r.id 
            INNER JOIN users u ON ur.user_id = u.id
            WHERE ur.user_id = ?
              AND (r.org_id = u.org_id OR u.is_super_admin = 1)
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}

$GLOBALS['userClass'] = new UserClass();
?>