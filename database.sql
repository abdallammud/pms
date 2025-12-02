-- =========================================================
-- PROPERTY MANAGEMENT SYSTEM + ROLE-BASED ACCESS CONTROL
-- Full Database Schema (MySQL)
-- =========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------
-- USERS, ROLES, PERMISSIONS (RBAC)
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(100) UNIQUE,
    description VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(150) UNIQUE,
    description VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS user_roles (
    user_id INT,
    role_id INT,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT,
    permission_id INT,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- Default Roles
INSERT IGNORE INTO roles (role_name, description) VALUES
('admin', 'Full system access'),
('manager', 'Manages properties, tenants and leases'),
('accountant', 'Manages invoices and payments'),
('staff', 'Handles maintenance requests');

-- ---------------------------------------------------------
-- SYSTEM SETTINGS
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    currency VARCHAR(10) DEFAULT 'USD',
    invoice_prefix VARCHAR(20) DEFAULT 'INV',
    theme VARCHAR(20) DEFAULT 'light',
    logo_path VARCHAR(255)
);

-- ---------------------------------------------------------
-- PROPERTIES & UNITS
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150),
    type VARCHAR(50),
    address VARCHAR(255),
    city VARCHAR(100),
    manager_id INT NULL,
    owner_name VARCHAR(150),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (manager_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    unit_number VARCHAR(50),
    unit_type VARCHAR(50),
    size_sqft INT,
    rent_amount DECIMAL(10,2),
    status ENUM('vacant','occupied','maintenance') DEFAULT 'vacant',
    tenant_id INT NULL,
    FOREIGN KEY (property_id) REFERENCES properties(id)
);

-- ---------------------------------------------------------
-- TENANTS
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150),
    phone VARCHAR(20),
    email VARCHAR(100),
    id_number VARCHAR(50),
    work_info VARCHAR(255),
    emergency_contact VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------
-- LEASES
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS leases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT,
    unit_id INT,
    start_date DATE,
    end_date DATE,
    monthly_rent DECIMAL(10,2),
    deposit DECIMAL(10,2),
    payment_cycle ENUM('monthly','quarterly','yearly') DEFAULT 'monthly',
    auto_invoice TINYINT(1) DEFAULT 1,
    status ENUM('active','pending','expired','terminated') DEFAULT 'active',
    FOREIGN KEY (tenant_id) REFERENCES tenants(id),
    FOREIGN KEY (unit_id) REFERENCES units(id)
);

-- ---------------------------------------------------------
-- RENT INVOICES & PAYMENTS
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS rent_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lease_id INT,
    invoice_date DATE,
    due_date DATE,
    amount DECIMAL(10,2),
    status ENUM('paid','unpaid','partial') DEFAULT 'unpaid',
    FOREIGN KEY (lease_id) REFERENCES leases(id)
);

CREATE TABLE IF NOT EXISTS payments_received (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT,
    amount_paid DECIMAL(10,2),
    payment_method ENUM('cash','mobile','bank'),
    received_date DATE,
    notes TEXT,
    FOREIGN KEY (invoice_id) REFERENCES rent_invoices(id)
);

-- ---------------------------------------------------------
-- EXPENSES
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    category VARCHAR(100),
    amount DECIMAL(10,2),
    description TEXT,
    expense_date DATE,
    FOREIGN KEY (property_id) REFERENCES properties(id)
);

-- ---------------------------------------------------------
-- MAINTENANCE MODULE
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS vendors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_name VARCHAR(150),
    service_type VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS maintenance_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    unit_id INT,
    description TEXT,
    status ENUM('new','in_progress','completed') DEFAULT 'new',
    priority ENUM('low','medium','high') DEFAULT 'medium',
    requester VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id),
    FOREIGN KEY (unit_id) REFERENCES units(id)
);

CREATE TABLE IF NOT EXISTS maintenance_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT,
    vendor_id INT,
    assigned_date DATE,
    expected_completion DATE,
    FOREIGN KEY (request_id) REFERENCES maintenance_requests(id),
    FOREIGN KEY (vendor_id) REFERENCES vendors(id)
);

SET FOREIGN_KEY_CHECKS = 1;


-- ============================================================== 
-- PERMISSIONS SEEDER 
-- ============================================================== 

INSERT IGNORE INTO permissions (permission_name, description) VALUES
-- Dashboard
('dashboard_view', 'View dashboard analytics'),

-- Properties
('property_manage', 'Manage all properties'),
('property_create', 'Create new property'),
('property_update', 'Update property'),
('property_delete', 'Delete property'),

('unit_manage', 'Manage units'),
('unit_create', 'Create unit'),
('unit_update', 'Update unit'),
('unit_delete', 'Delete unit'),

-- Tenants
('tenant_manage', 'Manage tenant records'),
('tenant_create', 'Create tenant'),
('tenant_update', 'Update tenant'),
('tenant_delete', 'Delete tenant'),

('lease_manage', 'Manage leases'),
('lease_create', 'Create lease'),
('lease_update', 'Update lease'),
('lease_terminate', 'Terminate lease'),

-- Accounting
('invoice_manage', 'Manage invoices'),
('invoice_create', 'Create invoice'),
('invoice_update', 'Update invoice'),
('invoice_delete', 'Delete invoice'),

('payment_manage', 'Manage payments'),
('payment_create', 'Record payment'),
('payment_update', 'Update payment'),
('payment_delete', 'Delete payment'),

('expense_manage', 'Manage expenses'),
('expense_create', 'Create expense'),
('expense_update', 'Update expense'),
('expense_delete', 'Delete expense'),

('bill_manage', 'Manage bills'),
('bill_create', 'Create bill'),
('bill_update', 'Update bill'),
('bill_delete', 'Delete bill'),

-- Maintenance
('maintenance_manage', 'Manage maintenance'),
('maintenance_create', 'Create maintenance request'),
('maintenance_update', 'Update maintenance request'),
('maintenance_delete', 'Delete maintenance request'),

('maintenance_assign', 'Assign maintenance tasks'),

('vendor_manage', 'Manage vendors'),
('vendor_create', 'Create vendor'),
('vendor_update', 'Update vendor'),
('vendor_delete', 'Delete vendor'),

-- Reports
('reports_view', 'View system reports'),
('reports_export', 'Export system reports'),

-- Settings
('settings_manage', 'Access system settings'),
('user_manage', 'Manage users'),
('user_create', 'Create user'),
('user_update', 'Update user'),
('user_delete', 'Delete user'),

('system_customize', 'Customize system branding and theme'),

('property_type_manage', 'Manage property types'),
('property_type_create', 'Create property type'),
('property_type_update', 'Update property type'),
('property_type_delete', 'Delete property type');

-- ============================================================== 
-- ASSIGN PERMISSIONS TO ROLES
-- ============================================================== 

-- ADMIN gets all permissions
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1 AS role_id, id AS permission_id FROM permissions;

-- MANAGER (role_id = 2)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions
WHERE permission_name IN (
    'dashboard_view',

    'property_manage','property_create','property_update',
    'unit_manage','unit_create','unit_update',

    'tenant_manage','tenant_create','tenant_update',

    'lease_manage','lease_create','lease_update',

    'maintenance_manage','maintenance_create','maintenance_update',
    'maintenance_assign','vendor_manage','vendor_update'
);

-- ACCOUNTANT (role_id = 3)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions
WHERE permission_name IN (
    'dashboard_view',

    'invoice_manage','invoice_create','invoice_update','invoice_delete',
    'payment_manage','payment_create','payment_update','payment_delete',

    'expense_manage','expense_create','expense_update','expense_delete',
    'bill_manage','bill_create','bill_update','bill_delete',

    'reports_view','reports_export'
);

-- STAFF (role_id = 4)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions
WHERE permission_name IN (
    'maintenance_manage',
    'maintenance_create',
    'maintenance_update',
    'vendor_manage',
    'vendor_update'
);

-- ============================================================== 
-- STARTER USER SEEDER
-- ============================================================== 

-- Password is 'password' (hashed with PASSWORD_DEFAULT)
INSERT IGNORE INTO users (name, email, password, status) VALUES 
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');

INSERT IGNORE INTO user_roles (user_id, role_id) VALUES 
(1, 1);
