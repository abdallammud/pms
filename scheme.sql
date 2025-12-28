-- =========================================================
-- PROPERTY MANAGEMENT SYSTEM (PMS)
-- Full Database Schema + RBAC + Seeding Data
-- Generated: 2025-12-28
-- =========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------
-- 1. RBAC SYSTEM (Users, Roles, Permissions)
-- ---------------------------------------------------------

DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(100) UNIQUE,
    description VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS permissions;
CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(150) UNIQUE,
    description VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS user_roles;
CREATE TABLE user_roles (
    user_id INT,
    role_id INT,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS role_permissions;
CREATE TABLE role_permissions (
    role_id INT,
    permission_id INT,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 2. SYSTEM CONFIGURATION
-- ---------------------------------------------------------

DROP TABLE IF EXISTS system_settings;
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 3. PROPERTY MANAGEMENT
-- ---------------------------------------------------------

DROP TABLE IF EXISTS property_types;
CREATE TABLE property_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS properties;
CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150),
    type_id INT NULL,
    address VARCHAR(255),
    city VARCHAR(100),
    manager_id INT NULL,
    owner_name VARCHAR(150),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (type_id) REFERENCES property_types(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS units;
CREATE TABLE units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    unit_number VARCHAR(50),
    unit_type VARCHAR(50),
    size_sqft INT,
    rent_amount DECIMAL(10,2),
    status ENUM('vacant','occupied','maintenance') DEFAULT 'vacant',
    tenant_id INT NULL,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 4. TENANTS & GUARANTEES
-- ---------------------------------------------------------

DROP TABLE IF EXISTS tenants;
CREATE TABLE tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150),
    phone VARCHAR(20),
    email VARCHAR(100),
    id_number VARCHAR(50),
    work_info VARCHAR(255),
    emergency_contact VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS guarantees;
CREATE TABLE guarantees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150),
    phone VARCHAR(20),
    email VARCHAR(100),
    id_number VARCHAR(50),
    work_info VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 5. LEASES (CONTRACTS)
-- ---------------------------------------------------------

DROP TABLE IF EXISTS leases;
CREATE TABLE leases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference_number VARCHAR(50) NULL,
    tenant_id INT NOT NULL,
    unit_id INT NOT NULL,
    property_id INT NULL,
    guarantee_id INT NULL,
    start_date DATE,
    end_date DATE,
    monthly_rent DECIMAL(10,2),
    deposit DECIMAL(10,2),
    payment_cycle ENUM('monthly','quarterly','yearly') DEFAULT 'monthly',
    auto_invoice TINYINT(1) DEFAULT 1,
    status ENUM('active','pending','expired','terminated') DEFAULT 'active',
    lease_conditions TEXT NULL,
    vehicle_info TEXT NULL,
    legal_weapons TEXT NULL,
    witnesses JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (guarantee_id) REFERENCES guarantees(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 6. ACCOUNTING (Invoices, Payments, Expenses)
-- ---------------------------------------------------------

DROP TABLE IF EXISTS charge_types;
CREATE TABLE charge_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    default_amount DECIMAL(10,2) NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS invoices;
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) NULL,
    reference_number VARCHAR(50) NULL,
    invoice_type ENUM('rent', 'other_charge') NOT NULL DEFAULT 'rent',
    charge_type_id INT NULL,
    lease_id INT NOT NULL,
    invoice_date DATE,
    due_date DATE,
    billing_month TINYINT NULL,
    billing_year SMALLINT NULL,
    amount DECIMAL(10,2),
    status ENUM('paid','unpaid','partial') DEFAULT 'unpaid',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lease_id) REFERENCES leases(id) ON DELETE CASCADE,
    FOREIGN KEY (charge_type_id) REFERENCES charge_types(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS payments_received;
CREATE TABLE payments_received (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_number VARCHAR(50) NULL,
    reference_number VARCHAR(50) NULL,
    invoice_id INT NOT NULL,
    amount_paid DECIMAL(10,2),
    payment_method ENUM('cash','mobile','bank'),
    received_date DATE,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS expenses;
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference_number VARCHAR(50) NULL,
    property_id INT NOT NULL,
    category VARCHAR(100),
    amount DECIMAL(10,2),
    description TEXT,
    expense_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS auto_invoice_log;
CREATE TABLE auto_invoice_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lease_id INT NOT NULL,
    invoice_id INT NULL,
    billing_month TINYINT NOT NULL,
    billing_year SMALLINT NOT NULL,
    status ENUM('success', 'skipped', 'failed') NOT NULL,
    message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lease_id) REFERENCES leases(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 7. MAINTENANCE
-- ---------------------------------------------------------

DROP TABLE IF EXISTS vendors;
CREATE TABLE vendors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_name VARCHAR(150),
    service_type VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    status ENUM('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS maintenance_requests;
CREATE TABLE maintenance_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference_number VARCHAR(50) NULL,
    property_id INT NOT NULL,
    unit_id INT NULL,
    description TEXT,
    status ENUM('new','in_progress','completed') DEFAULT 'new',
    priority ENUM('low','medium','high') DEFAULT 'medium',
    requester VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS maintenance_assignments;
CREATE TABLE maintenance_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    vendor_id INT NOT NULL,
    assigned_date DATE,
    expected_completion DATE,
    notes TEXT,
    FOREIGN KEY (request_id) REFERENCES maintenance_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 8. INDEXES
-- ---------------------------------------------------------

CREATE INDEX idx_invoice_type ON invoices (invoice_type);
CREATE INDEX idx_billing_period ON invoices (billing_month, billing_year);
CREATE INDEX idx_rent_invoice_check ON invoices (lease_id, invoice_type, billing_month, billing_year);
CREATE INDEX idx_auto_invoice_log_period ON auto_invoice_log (billing_month, billing_year);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================== 
-- SEEDING DATA
-- ============================================================== 

-- 1. Default Roles
INSERT INTO roles (id, role_name, description) VALUES
(1, 'admin', 'Full system access'),
(2, 'manager', 'Manages properties, tenants and leases'),
(3, 'accountant', 'Manages invoices and payments'),
(4, 'staff', 'Handles maintenance requests');

-- 2. Permissions
INSERT INTO permissions (permission_name, description) VALUES
-- Dashboard
('dashboard_view', 'View dashboard analytics'),

-- Properties
('property_manage', 'Manage all properties'),
('property_create', 'Create new property'),
('property_update', 'Update property'),
('property_delete', 'Delete property'),
('property_type_manage', 'Manage property types'),

-- Units
('unit_manage', 'Manage units'),
('unit_create', 'Create unit'),
('unit_update', 'Update unit'),
('unit_delete', 'Delete unit'),

-- Tenants
('tenant_manage', 'Manage tenant records'),
('tenant_create', 'Create tenant'),
('tenant_update', 'Update tenant'),
('tenant_delete', 'Delete tenant'),

-- Leases
('lease_manage', 'Manage leases'),
('lease_create', 'Create lease'),
('lease_update', 'Update lease'),
('lease_terminate', 'Terminate lease'),

-- Invoices & Charging
('invoice_manage', 'Manage invoices'),
('invoice_create', 'Create invoice'),
('invoice_update', 'Update invoice'),
('invoice_delete', 'Delete invoice'),
('charge_type_manage', 'Manage charge types'),

-- Payments
('payment_manage', 'Manage payments'),
('payment_create', 'Record payment'),
('payment_update', 'Update payment'),
('payment_delete', 'Delete payment'),

-- Expenses
('expense_manage', 'Manage expenses'),
('expense_create', 'Create expense'),
('expense_update', 'Update expense'),
('expense_delete', 'Delete expense'),

-- Maintenance
('maintenance_manage', 'Manage maintenance'),
('maintenance_create', 'Create maintenance request'),
('maintenance_assign', 'Assign maintenance tasks'),
('vendor_manage', 'Manage vendors'),

-- RBAC & Users
('user_manage', 'Manage users'),
('user_view', 'View users list'),
('user_create', 'Create system user'),
('user_update', 'Update user info'),
('user_delete', 'Delete system user'),
('role_manage', 'Manage roles & permissions'),

-- Settings
('settings_manage', 'Access system settings'),
('system_customize', 'Customize system branding'),
('reports_view', 'View system reports');

-- 3. Assign Permissions to Roles

-- ADMIN (role_id = 1) gets everything
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- MANAGER (role_id = 2)
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions
WHERE permission_name IN (
    'dashboard_view', 'property_manage', 'property_create', 'property_update',
    'unit_manage', 'unit_create', 'unit_update',
    'tenant_manage', 'tenant_create', 'tenant_update',
    'lease_manage', 'lease_create', 'lease_update',
    'maintenance_manage', 'maintenance_create', 'maintenance_assign', 'vendor_manage'
);

-- ACCOUNTANT (role_id = 3)
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions
WHERE permission_name IN (
    'dashboard_view', 'invoice_manage', 'invoice_create', 'invoice_update',
    'payment_manage', 'payment_create', 'payment_update',
    'charge_type_manage', 'expense_manage', 'expense_create', 'reports_view'
);

-- STAFF (role_id = 4)
INSERT INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions
WHERE permission_name IN (
    'maintenance_manage', 'maintenance_create', 'vendor_manage'
);

-- 4. Default Users (Password is 'password')
-- Hashed using PASSWORD_DEFAULT
INSERT INTO users (id, name, email, password, status) VALUES 
(1, 'Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active'),
(2, 'Test Manager', 'manager@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active'),
(3, 'Test Accountant', 'accountant@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');

-- Assign Roles to Users
INSERT INTO user_roles (user_id, role_id) VALUES 
(1, 1), -- Admin
(2, 2), -- Manager
(3, 3); -- Accountant

-- 5. System Settings
INSERT INTO system_settings (setting_key, setting_value) VALUES
('org_name', 'PMS Property Management'),
('org_email', 'info@pms.com'),
('auto_invoice_enabled', 'no'),
('auto_invoice_day', '1'),
('transaction_series', '{"rent_invoice":{"prefix":"RNT-","suffix":"","starting_number":"00001","current_number":0,"include_year":true,"auto_reset":true},"other_invoice":{"prefix":"CHR-","suffix":"","starting_number":"00001","current_number":0,"include_year":true,"auto_reset":true},"payment":{"prefix":"RCT-","suffix":"","starting_number":"00001","current_number":0},"expense":{"prefix":"EXP-","suffix":"","starting_number":"00001","current_number":0},"maintenance":{"prefix":"MR-","suffix":"","starting_number":"00001","current_number":0},"lease":{"prefix":"LS-","suffix":"","starting_number":"00001","current_number":0}}');

-- 6. Property Types
INSERT INTO property_types (type_name, description) VALUES
('Apartment', 'Multi-unit residential building'),
('House', 'Single-family residential property'),
('Commercial', 'Business or retail property'),
('Office', 'Office building or space'),
('Warehouse', 'Storage or industrial property');

-- 7. Charge Types
INSERT INTO charge_types (name, description, default_amount) VALUES
('Maintenance Fee', 'General maintenance charges', NULL),
('Cleaning Fee', 'Cleaning service charges', NULL),
('Electricity', 'Electricity consumption charges', NULL),
('Water', 'Water consumption charges', NULL),
('Late Payment Penalty', 'Penalty for late rent payment', NULL),
('Garbage Collection', 'Waste management charges', NULL);
