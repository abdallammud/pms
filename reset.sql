-- reset.sql
-- Disable foreign key checks for clean truncation
SET FOREIGN_KEY_CHECKS = 0;

-- Truncate transactional and entity tables
DELETE FROM `invoice_items`;
ALTER TABLE `invoice_items` AUTO_INCREMENT = 1;
DELETE FROM `payments_received`;
ALTER TABLE `payments_received` AUTO_INCREMENT = 1;
DELETE FROM `invoices`;
ALTER TABLE `invoices` AUTO_INCREMENT = 1;
DELETE FROM `expenses`;
ALTER TABLE `expenses` AUTO_INCREMENT = 1;
DELETE FROM `maintenance_assignments`;
ALTER TABLE `maintenance_assignments` AUTO_INCREMENT = 1;
DELETE FROM `maintenance_requests`;
ALTER TABLE `maintenance_requests` AUTO_INCREMENT = 1;
DELETE FROM `leases`;
ALTER TABLE `leases` AUTO_INCREMENT = 1;
DELETE FROM `tenants`;
ALTER TABLE `tenants` AUTO_INCREMENT = 1;
DELETE FROM `units`;
ALTER TABLE `units` AUTO_INCREMENT = 1;
DELETE FROM `properties`;
ALTER TABLE `properties` AUTO_INCREMENT = 1;

-- Truncate organizational and user tables (except permissions)
DELETE FROM `organizations`;
ALTER TABLE `organizations` AUTO_INCREMENT = 1;
DELETE FROM `roles`;
ALTER TABLE `roles` AUTO_INCREMENT = 1;
DELETE FROM `user_roles`;
ALTER TABLE `user_roles` AUTO_INCREMENT = 1;
DELETE FROM `users`;
ALTER TABLE `users` AUTO_INCREMENT = 1;
DELETE FROM `role_permissions`;
ALTER TABLE `role_permissions` AUTO_INCREMENT = 1;
DELETE FROM `system_settings`;
ALTER TABLE `system_settings` AUTO_INCREMENT = 1;
DELETE FROM `charge_types`;
ALTER TABLE `charge_types` AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Insert Organizations
INSERT INTO `organizations` (`id`, `name`, `status`, `created_at`) VALUES 
(1, 'Rayaan Property Solutions', 'active', CURRENT_TIMESTAMP),
(2, 'Mogadishu Properties', 'active', CURRENT_TIMESTAMP);

-- 2. Insert Settings for both orgs
INSERT INTO `system_settings` (`org_id`, `setting_key`, `setting_value`) VALUES 
(1, 'org_name', 'Rayaan Property Solutions'),
(1, 'org_phone', '+252 61 000 0001'),
(1, 'org_email', 'info@rayaan.so'),
(1, 'property_types', 'Apartment,Villa,Office,House'),
(1, 'unit_types', 'Studio,1BR,2BR,3BR,Shop'),
(1, 'amenities', 'AC,WiFi,Parking,Backup Generator,Security'),
(2, 'org_name', 'Mogadishu Properties'),
(2, 'org_phone', '+252 61 000 0002'),
(2, 'org_email', 'info@mogadishuprop.so'),
(2, 'property_types', 'Apartment,Villa,Office,House'),
(2, 'unit_types', 'Studio,1BR,2BR,3BR,Shop'),
(2, 'amenities', 'AC,WiFi,Parking,Backup Generator,Security');

-- 3. Insert Charge Types
INSERT INTO `charge_types` (`id`, `org_id`, `name`, `description`) VALUES 
(1, 1, 'Internet', 'Monthly WiFi'), (2, 1, 'Water', 'Monthly Water Bill'), (3, 1, 'Electricity', 'Electricity Bill'),
(4, 2, 'Internet', 'Monthly WiFi'), (5, 2, 'Water', 'Monthly Water Bill'), (6, 2, 'Electricity', 'Electricity Bill');

-- 4. Insert Roles
INSERT INTO `roles` (`id`, `org_id`, `role_name`, `description`) VALUES 
(1, 1, 'Admin', 'Administrator for Rayaan'), (2, 1, 'User', 'Standard User for Rayaan'),
(3, 2, 'Admin', 'Administrator for Mogadishu'), (4, 2, 'User', 'Standard User for Mogadishu');

-- Give admin all permissions (using subquery logic in php or doing it here is hard without knowing permission IDs, so we will handle role_permissions via a small PHP patch next)

-- 5. Insert Users (password: myadmin => $2y$10$zNP./KMq0zMbedMO83t7d1snkoHmt6yPgnldIlXZ.3...)
-- Using a known hash for 'myadmin'
INSERT INTO `users` (`id`, `org_id`, `name`, `username`, `email`, `password`, `is_super_admin`, `status`) VALUES 
(1, 1, 'Rayaan Admin', 'admin_rayaan', 'admin@rayaan.so', '$2y$10$B58lH2T/AByU65sK/0XqQuXQc./u9T69gM15cI6tS520N26n9oWbK', 0, 'active'),
(2, 2, 'Mogadishu Admin', 'admin_mogadishu', 'admin@mogadishuprop.so', '$2y$10$B58lH2T/AByU65sK/0XqQuXQc./u9T69gM15cI6tS520N26n9oWbK', 0, 'active'),
(3, 0, 'Super Admin', 'superadmin', 'super@admin.com', '$2y$10$B58lH2T/AByU65sK/0XqQuXQc./u9T69gM15cI6tS520N26n9oWbK', 1, 'active');

-- Link users to roles
INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES 
(1, 1), (2, 3);

-- 6. Insert Properties
INSERT INTO `properties` (`id`, `org_id`, `name`, `owner_name`, `property_type`, `city`, `address`, `status`) VALUES
-- Rayaan Properties (Org 1)
(1, 1, 'Hodan Heights Apartments', 'Abdullahi Mohamed Hassan', 'Apartment', 'Mogadishu', 'Hodan District', 'active'),
(2, 1, 'Waberi Plaza Residences', 'Ahmed Ali Nur', 'Apartment', 'Mogadishu', 'Waberi District', 'active'),
(3, 1, 'Kismayo View Villas', 'Mohamed Abdirahman Yusuf', 'Villa', 'Kismayo', 'Beach Road', 'active'),
(4, 1, 'Garowe Gardens Estate', 'Abdiqadir Osman Farah', 'House', 'Garowe', 'City Center', 'active'),
(5, 1, 'Hargeisa Skyline Towers', 'Ismail Ahmed Jama', 'Apartment', 'Hargeisa', 'Jigjiga Yar', 'active'),

-- Mogadishu Properties (Org 2)
(6, 2, 'Bosaso Pearl Apartments', 'Said Abdullahi Warsame', 'Apartment', 'Bosaso', 'Port Road', 'active'),
(7, 2, 'Mogadishu Marina Residences', 'Hassan Ali Sheikh', 'Villa', 'Mogadishu', 'Lido Beach', 'active'),
(8, 2, 'Baidoa Green Homes', 'Yusuf Mohamed Abdullahi', 'House', 'Baidoa', 'Isha Center', 'active'),
(9, 2, 'Beledweyne Riverside Apartments', 'Abdirizak Hassan Aden', 'Apartment', 'Beledweyne', 'River Side', 'active'),
(10, 2, 'Jowhar Palm Estate', 'Omar Farah Mohamed', 'House', 'Jowhar', 'Main Street', 'active');

