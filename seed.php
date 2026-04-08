<?php
chdir(__DIR__ . '/app');
$_SERVER['SERVER_PORT'] = '80'; // Mock for CLI
$_SERVER['HTTP_HOST'] = 'localhost'; // Mock for CLI
$_SERVER['SCRIPT_NAME'] = '/index.php'; // Mock for CLI
mysqli_report(MYSQLI_REPORT_OFF);
require_once('init.php');

$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("SET sql_mode=''");

$tables = [
    'property_images',
    'invoice_items',
    'payments_received',
    'invoices',
    'expenses',
    'maintenance_assignments',
    'maintenance_requests',
    'leases',
    'tenants',
    'units',
    'properties',
    'organizations',
    'roles',
    'user_roles',
    'users',
    'role_permissions',
    'system_settings',
    'charge_types',
    'property_types'
];

foreach ($tables as $t) {
    $conn->query("DELETE FROM `$t`");
    $conn->query("ALTER TABLE `$t` AUTO_INCREMENT = 1");
}

$queries = [
    "INSERT INTO `organizations` (`id`, `name`, `status`) VALUES 
    (1, 'Rayaan Property Solutions', 'active'),
    (2, 'Mogadishu Properties', 'active')",

    "INSERT INTO `system_settings` (`org_id`, `setting_key`, `setting_value`) VALUES 
    (1, 'org_name', 'Rayaan Property Solutions'), (1, 'org_phone', '+252 61 000 0001'), (1, 'org_email', 'info@rayaan.so'), (1, 'unit_types', 'Studio,1BR,2BR,3BR,Shop'), (1, 'amenities', 'AC,WiFi,Parking,Backup Generator,Security'),
    (2, 'org_name', 'Mogadishu Properties'), (2, 'org_phone', '+252 61 000 0002'), (2, 'org_email', 'info@mogadishuprop.so'), (2, 'unit_types', 'Studio,1BR,2BR,3BR,Shop'), (2, 'amenities', 'AC,WiFi,Parking,Backup Generator,Security')",

    "INSERT INTO `charge_types` (`id`, `org_id`, `name`, `description`) VALUES 
    (1, 1, 'Internet', 'Monthly WiFi'), (2, 1, 'Water', 'Monthly Water Bill'), (3, 1, 'Electricity', 'Electricity Bill'),
    (4, 2, 'Internet', 'Monthly WiFi'), (5, 2, 'Water', 'Monthly Water Bill'), (6, 2, 'Electricity', 'Electricity Bill')",

    "INSERT INTO `property_types` (`id`, `org_id`, `type_name`, `description`) VALUES
    (1, 1, 'Apartment', 'Residential Apartments'), (2, 1, 'Villa', 'Luxury Villas'), (3, 1, 'House', 'Standard Homes'),
    (4, 2, 'Apartment', 'Residential Apartments'), (5, 2, 'Villa', 'Luxury Villas'), (6, 2, 'House', 'Standard Homes')",

    "INSERT INTO `users` (`id`, `org_id`, `name`, `username`, `email`, `password`, `is_super_admin`, `status`) VALUES 
    (1, 1, 'Rayaan Admin', 'admin_rayaan', 'admin@rayaan.so', '$2y$10\$zNP./KMq0zMbedMO83t7d1snkoHmt6yPgnldIlXZ.33G8uLqM0wz6', 0, 'active'),
    (2, 2, 'Mogadishu Admin', 'admin_mogadishu', 'admin@mogadishuprop.so', '$2y$10\$zNP./KMq0zMbedMO83t7d1snkoHmt6yPgnldIlXZ.33G8uLqM0wz6', 0, 'active'),
    (3, 0, 'Super Admin', 'superadmin', 'super@admin.com', '$2y$10\$zNP./KMq0zMbedMO83t7d1snkoHmt6yPgnldIlXZ.33G8uLqM0wz6', 1, 'active')",

    "INSERT INTO `roles` (`id`, `org_id`, `role_name`, `description`) VALUES 
    (1, 1, 'Admin', 'Administrator for Rayaan'), (2, 1, 'User', 'Standard User for Rayaan'),
    (3, 2, 'Admin', 'Administrator for Mogadishu'), (4, 2, 'User', 'Standard User for Mogadishu')",

    "INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES (1, 1), (2, 3)",

    "INSERT INTO `properties` (`id`, `org_id`, `name`, `owner_name`, `type_id`, `city`, `address`, `description`) VALUES
    (1, 1, 'Hodan Heights Apartments', 'Abdullahi Mohamed Hassan', 1, 'Mogadishu', 'Hodan District', 'Premium real estate'),
    (2, 1, 'Waberi Plaza Residences', 'Ahmed Ali Nur', 1, 'Mogadishu', 'Waberi District', 'Modern downtown apartment'),
    (3, 1, 'Kismayo View Villas', 'Mohamed Abdirahman Yusuf', 2, 'Kismayo', 'Beach Road', 'Beautiful beach view villa'),
    (4, 1, 'Garowe Gardens Estate', 'Abdiqadir Osman Farah', 3, 'Garowe', 'City Center', 'Large family house with garden'),
    (5, 1, 'Hargeisa Skyline Towers', 'Ismail Ahmed Jama', 1, 'Hargeisa', 'Jigjiga Yar', 'High rise luxury appartment'),
    (6, 2, 'Bosaso Pearl Apartments', 'Said Abdullahi Warsame', 4, 'Bosaso', 'Port Road', 'Close to port amenities'),
    (7, 2, 'Mogadishu Marina Residences', 'Hassan Ali Sheikh', 5, 'Mogadishu', 'Lido Beach', 'Exquisite beachside living'),
    (8, 2, 'Baidoa Green Homes', 'Yusuf Mohamed Abdullahi', 6, 'Baidoa', 'Isha Center', 'Spacious residential home'),
    (9, 2, 'Beledweyne Riverside Apartments', 'Abdirizak Hassan Aden', 4, 'Beledweyne', 'River Side', 'Scenic riverside view'),
    (10, 2, 'Jowhar Palm Estate', 'Omar Farah Mohamed', 6, 'Jowhar', 'Main Street', 'Affordable residential property')"
];

file_put_contents('../err.txt', '');

foreach ($queries as $sql) {
    if (!$conn->query($sql)) {
        file_put_contents('../err.txt', "Error query: " . $conn->error . "\n" . $sql . "\n", FILE_APPEND);
    }
}

$props = [];
$res = $conn->query("SELECT p.id, p.org_id, pt.type_name FROM properties p LEFT JOIN property_types pt ON pt.id = p.type_id");
if ($res) {
    while ($row = $res->fetch_assoc())
        $props[] = $row;
} else {
    file_put_contents('../err.txt', "Failed to fetch properties: " . $conn->error . "\n", FILE_APPEND);
}

$unit_types = ['Studio', '1BR', '2BR', '3BR', 'Shop'];
$statuses = ['vacant', 'occupied', 'maintenance'];

foreach ($props as $p) {
    $num_units = rand(3, 8);
    for ($i = 1; $i <= $num_units; $i++) {
        $u_type = $unit_types[array_rand($unit_types)];
        $rent = rand(150, 600);
        $status = $statuses[array_rand($statuses)];
        $unit_no = ($p['type_name'] === 'Apartment') ? 'Apt-' . sprintf('%02d', $i) : 'Unit-' . $i;
        $sqlu = "INSERT INTO units (org_id, property_id, unit_number, unit_type, rent_amount, status) VALUES ({$p['org_id']}, {$p['id']}, '$unit_no', '$u_type', $rent, '$status')";
        if (!$conn->query($sqlu)) {
            file_put_contents('../err.txt', "Unit error: " . $conn->error . "\n", FILE_APPEND);
        }
    }
}

$first_names = ['Ahmed', 'Mohamed', 'Abdi', 'Hassan', 'Ali', 'Omar', 'Fartun', 'Asha', 'Halima', 'Khadija', 'Faduma', 'Yusuf', 'Ibrahim', 'Mahad', 'Abdullahi'];
$last_names = ['Jama', 'Warsame', 'Farah', 'Shirwa', 'Gedi', 'Samatar', 'Nur', 'Hashi', 'Dirie', 'Abdi', 'Hassan', 'Mohamud'];

for ($org = 1; $org <= 2; $org++) {
    for ($i = 0; $i < 10; $i++) {
        $fn = $first_names[array_rand($first_names)];
        $ln = $last_names[array_rand($last_names)];
        $t_name = $conn->real_escape_string($fn . ' ' . $ln);
        $t_phone = '+25261' . rand(1000000, 9999999);
        $t_email = strtolower($fn . $ln) . rand(10, 99) . '@example.com';

        $sqlt = "INSERT INTO tenants (org_id, full_name, phone, email, status) VALUES ($org, '$t_name', '$t_phone', '$t_email', 'active')";
        if (!$conn->query($sqlt)) {
            file_put_contents('../err.txt', "Tenant error: " . $conn->error . "\n", FILE_APPEND);
        }
    }
}

$res = $conn->query("SELECT id FROM permissions");
$perms = [];
if ($res) {
    while ($row = $res->fetch_assoc())
        $perms[] = $row['id'];
}
foreach ([1, 3] as $role_id) {
    foreach ($perms as $p_id) {
        $conn->query("INSERT INTO role_permissions (role_id, permission_id) VALUES ($role_id, $p_id)");
    }
}

$conn->query("SET FOREIGN_KEY_CHECKS = 1");
echo "Data Seeding completed successfully. Check err.txt if debugging.\n";
