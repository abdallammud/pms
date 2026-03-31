SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS organizations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO organizations (id, name, code, status)
SELECT 1, 'Default Company', 'DEFAULT', 'active'
WHERE NOT EXISTS (SELECT 1 FROM organizations WHERE id = 1);

ALTER TABLE users ADD COLUMN IF NOT EXISTS org_id INT NULL AFTER email;
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_super_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER status;

ALTER TABLE users
    ADD INDEX IF NOT EXISTS idx_users_org_id (org_id),
    ADD CONSTRAINT fk_users_org_id FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE SET NULL;

ALTER TABLE system_settings ADD COLUMN IF NOT EXISTS org_id INT NULL AFTER id;
ALTER TABLE property_types ADD COLUMN IF NOT EXISTS org_id INT NULL AFTER id;
ALTER TABLE charge_types ADD COLUMN IF NOT EXISTS org_id INT NULL AFTER id;
ALTER TABLE properties ADD COLUMN IF NOT EXISTS org_id INT NULL AFTER id;
ALTER TABLE units ADD COLUMN IF NOT EXISTS org_id INT NULL AFTER id;
ALTER TABLE tenants ADD COLUMN IF NOT EXISTS org_id INT NULL AFTER id;
ALTER TABLE guarantees ADD COLUMN IF NOT EXISTS org_id INT NULL AFTER id;
ALTER TABLE leases ADD COLUMN IF NOT EXISTS org_id INT NULL AFTER id;
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS org_id INT NULL AFTER id;
ALTER TABLE payments_received ADD COLUMN IF NOT EXISTS org_id INT NULL AFTER id;
ALTER TABLE expenses ADD COLUMN IF NOT EXISTS org_id INT NULL AFTER id;
ALTER TABLE vendors ADD COLUMN IF NOT EXISTS org_id INT NULL AFTER id;
ALTER TABLE maintenance_requests ADD COLUMN IF NOT EXISTS org_id INT NULL AFTER id;
ALTER TABLE maintenance_assignments ADD COLUMN IF NOT EXISTS org_id INT NULL AFTER id;
ALTER TABLE auto_invoice_log ADD COLUMN IF NOT EXISTS org_id INT NULL AFTER id;

-- -------------------------------------------------------
-- Roles: add org_id so each company owns its own roles
-- -------------------------------------------------------
-- 1. Add org_id column (nullable first so we can backfill)
ALTER TABLE roles ADD COLUMN IF NOT EXISTS org_id INT NULL AFTER id;

-- 2. Drop the old global unique constraint on role_name alone
--    (MySQL doesn't support DROP INDEX IF EXISTS before 8.0.28; safe to ignore the error if already dropped)
ALTER TABLE roles DROP INDEX IF EXISTS role_name;

-- 3. Backfill existing roles to Default Company
UPDATE roles SET org_id = 1 WHERE org_id IS NULL;

-- 4. Make org_id NOT NULL
ALTER TABLE roles MODIFY org_id INT NOT NULL DEFAULT 1;

-- 5. Add new composite unique: a company cannot have two roles with the same name
ALTER TABLE roles ADD UNIQUE IF NOT EXISTS idx_roles_org_name (org_id, role_name);

-- 6. Add foreign key to organizations
ALTER TABLE roles ADD CONSTRAINT IF NOT EXISTS fk_roles_org FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE CASCADE;

-- 7. Index for fast per-org lookups
CREATE INDEX IF NOT EXISTS idx_roles_org ON roles(org_id);

UPDATE users SET org_id = 1 WHERE org_id IS NULL;
UPDATE system_settings SET org_id = 1 WHERE org_id IS NULL;
UPDATE property_types SET org_id = 1 WHERE org_id IS NULL;
UPDATE charge_types SET org_id = 1 WHERE org_id IS NULL;
UPDATE properties SET org_id = 1 WHERE org_id IS NULL;
UPDATE units SET org_id = 1 WHERE org_id IS NULL;
UPDATE tenants SET org_id = 1 WHERE org_id IS NULL;
UPDATE guarantees SET org_id = 1 WHERE org_id IS NULL;
UPDATE leases SET org_id = 1 WHERE org_id IS NULL;
UPDATE invoices SET org_id = 1 WHERE org_id IS NULL;
UPDATE payments_received SET org_id = 1 WHERE org_id IS NULL;
UPDATE expenses SET org_id = 1 WHERE org_id IS NULL;
UPDATE vendors SET org_id = 1 WHERE org_id IS NULL;
UPDATE maintenance_requests SET org_id = 1 WHERE org_id IS NULL;
UPDATE maintenance_assignments SET org_id = 1 WHERE org_id IS NULL;
UPDATE auto_invoice_log SET org_id = 1 WHERE org_id IS NULL;

ALTER TABLE users MODIFY org_id INT NOT NULL;
ALTER TABLE system_settings MODIFY org_id INT NOT NULL;
ALTER TABLE property_types MODIFY org_id INT NOT NULL;
ALTER TABLE charge_types MODIFY org_id INT NOT NULL;
ALTER TABLE properties MODIFY org_id INT NOT NULL;
ALTER TABLE units MODIFY org_id INT NOT NULL;
ALTER TABLE tenants MODIFY org_id INT NOT NULL;
ALTER TABLE guarantees MODIFY org_id INT NOT NULL;
ALTER TABLE leases MODIFY org_id INT NOT NULL;
ALTER TABLE invoices MODIFY org_id INT NOT NULL;
ALTER TABLE payments_received MODIFY org_id INT NOT NULL;
ALTER TABLE expenses MODIFY org_id INT NOT NULL;
ALTER TABLE vendors MODIFY org_id INT NOT NULL;
ALTER TABLE maintenance_requests MODIFY org_id INT NOT NULL;
ALTER TABLE maintenance_assignments MODIFY org_id INT NOT NULL;
ALTER TABLE auto_invoice_log MODIFY org_id INT NOT NULL;

CREATE INDEX IF NOT EXISTS idx_system_settings_org_key ON system_settings(org_id, setting_key);
CREATE INDEX IF NOT EXISTS idx_property_types_org ON property_types(org_id);
CREATE INDEX IF NOT EXISTS idx_charge_types_org ON charge_types(org_id);
CREATE INDEX IF NOT EXISTS idx_properties_org ON properties(org_id);
CREATE INDEX IF NOT EXISTS idx_units_org ON units(org_id);
CREATE INDEX IF NOT EXISTS idx_tenants_org ON tenants(org_id);
CREATE INDEX IF NOT EXISTS idx_guarantees_org ON guarantees(org_id);
CREATE INDEX IF NOT EXISTS idx_leases_org ON leases(org_id);
CREATE INDEX IF NOT EXISTS idx_invoices_org ON invoices(org_id);
CREATE INDEX IF NOT EXISTS idx_payments_received_org ON payments_received(org_id);
CREATE INDEX IF NOT EXISTS idx_expenses_org ON expenses(org_id);
CREATE INDEX IF NOT EXISTS idx_vendors_org ON vendors(org_id);
CREATE INDEX IF NOT EXISTS idx_maintenance_requests_org ON maintenance_requests(org_id);
CREATE INDEX IF NOT EXISTS idx_maintenance_assignments_org ON maintenance_assignments(org_id);
CREATE INDEX IF NOT EXISTS idx_auto_invoice_log_org ON auto_invoice_log(org_id);

SET FOREIGN_KEY_CHECKS = 1;
