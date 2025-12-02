<?php
require_once 'app/init.php';

global $conn;

// 1. Create Roles Table
$sql_roles = "CREATE TABLE IF NOT EXISTS `roles` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `role_name` varchar(100) DEFAULT NULL,
 `description` varchar(255) DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

if ($conn->query($sql_roles) === TRUE) {
    echo "Table 'roles' created successfully.<br>";
} else {
    echo "Error creating table 'roles': " . $conn->error . "<br>";
}

// 2. Create Permissions Table
$sql_permissions = "CREATE TABLE IF NOT EXISTS `permissions` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `permission_name` varchar(150) DEFAULT NULL,
 `description` varchar(255) DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `permission_name` (`permission_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

if ($conn->query($sql_permissions) === TRUE) {
    echo "Table 'permissions' created successfully.<br>";
} else {
    echo "Error creating table 'permissions': " . $conn->error . "<br>";
}

// 3. Create Role Permissions Table
$sql_role_permissions = "CREATE TABLE IF NOT EXISTS `role_permissions` (
 `role_id` int(11) NOT NULL,
 `permission_id` int(11) NOT NULL,
 PRIMARY KEY (`role_id`,`permission_id`),
 KEY `permission_id` (`permission_id`),
 CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
 CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

if ($conn->query($sql_role_permissions) === TRUE) {
    echo "Table 'role_permissions' created successfully.<br>";
} else {
    echo "Error creating table 'role_permissions': " . $conn->error . "<br>";
}

// 4. Insert Default Permissions
$permissions = [
    'user_view' => 'View Users',
    'user_create' => 'Create Users',
    'user_edit' => 'Edit Users',
    'user_delete' => 'Delete Users',
    'role_view' => 'View Roles',
    'role_create' => 'Create Roles',
    'role_edit' => 'Edit Roles',
    'role_delete' => 'Delete Roles',
];

foreach ($permissions as $name => $desc) {
    $stmt = $conn->prepare("INSERT IGNORE INTO permissions (permission_name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $desc);
    if ($stmt->execute()) {
        // echo "Permission '$name' inserted/exists.<br>";
    } else {
        echo "Error inserting permission '$name': " . $stmt->error . "<br>";
    }
}
echo "Default permissions inserted.<br>";

// 5. Insert Admin Role if not exists
$stmt = $conn->prepare("INSERT IGNORE INTO roles (role_name, description) VALUES ('Admin', 'Administrator with full access')");
if ($stmt->execute()) {
    echo "Admin role inserted/exists.<br>";
    $admin_role_id = $stmt->insert_id;
    if ($admin_role_id == 0) {
        // If it was ignored, fetch the id
        $res = $conn->query("SELECT id FROM roles WHERE role_name = 'Admin'");
        $admin_role_id = $res->fetch_assoc()['id'];
    }

    // Assign all permissions to Admin
    $all_perms = $conn->query("SELECT id FROM permissions");
    while ($perm = $all_perms->fetch_assoc()) {
        $perm_id = $perm['id'];
        $conn->query("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES ($admin_role_id, $perm_id)");
    }
    echo "All permissions assigned to Admin role.<br>";
} else {
    echo "Error inserting Admin role: " . $stmt->error . "<br>";
}

echo "Database setup completed.";
?>
