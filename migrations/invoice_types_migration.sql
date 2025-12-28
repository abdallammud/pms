-- =========================================================
-- INVOICE TYPES ARCHITECTURE ENHANCEMENT
-- Migration Script for Property Management System
-- Run this in phpMyAdmin
-- =========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------
-- 1. CREATE CHARGE TYPES TABLE
-- For defining custom invoice charge types (Other Charges)
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS charge_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    default_amount DECIMAL(10,2) NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default charge types
INSERT INTO charge_types (name, description, default_amount) VALUES
('Maintenance Fee', 'General maintenance charges', NULL),
('Cleaning Fee', 'Cleaning service charges', NULL),
('Electricity', 'Electricity consumption charges', NULL),
('Water', 'Water consumption charges', NULL),
('Late Payment Penalty', 'Penalty for late rent payment', NULL),
('Parking Fee', 'Monthly parking space allocation', NULL),
('Security Deposit Top-up', 'Additional security deposit', NULL),
('Garbage Collection', 'Waste management charges', NULL);


-- ---------------------------------------------------------
-- 2. RENAME rent_invoices TO invoices
-- ---------------------------------------------------------

-- Check if table exists and rename
RENAME TABLE rent_invoices TO invoices;


-- ---------------------------------------------------------
-- 3. ALTER INVOICES TABLE
-- Add new columns for invoice type support
-- ---------------------------------------------------------

-- Add invoice_type column
ALTER TABLE invoices 
ADD COLUMN invoice_type ENUM('rent', 'other_charge') NOT NULL DEFAULT 'rent' AFTER id;

-- Add charge_type_id for linking to charge_types table
ALTER TABLE invoices 
ADD COLUMN charge_type_id INT NULL AFTER invoice_type;

-- Add billing period columns (for tracking which month/year rent is for)
ALTER TABLE invoices 
ADD COLUMN billing_month TINYINT NULL AFTER due_date;

ALTER TABLE invoices 
ADD COLUMN billing_year SMALLINT NULL AFTER billing_month;

-- Add notes column for additional information
ALTER TABLE invoices 
ADD COLUMN notes TEXT NULL AFTER status;

-- Add created_at timestamp
ALTER TABLE invoices 
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER notes;

-- Add foreign key for charge_type_id
ALTER TABLE invoices 
ADD CONSTRAINT fk_invoice_charge_type 
FOREIGN KEY (charge_type_id) REFERENCES charge_types(id) ON DELETE SET NULL;


-- ---------------------------------------------------------
-- 4. BACKFILL EXISTING DATA
-- Set billing_month and billing_year for existing invoices
-- ---------------------------------------------------------

UPDATE invoices 
SET billing_month = MONTH(invoice_date), 
    billing_year = YEAR(invoice_date)
WHERE billing_month IS NULL AND billing_year IS NULL;


-- ---------------------------------------------------------
-- 5. CREATE INDEXES FOR PERFORMANCE
-- ---------------------------------------------------------

-- Index for invoice type filtering
CREATE INDEX idx_invoice_type ON invoices (invoice_type);

-- Index for billing period lookups
CREATE INDEX idx_billing_period ON invoices (billing_month, billing_year);

-- Composite index for duplicate rent invoice check
CREATE INDEX idx_rent_invoice_check ON invoices (lease_id, invoice_type, billing_month, billing_year);


-- ---------------------------------------------------------
-- 6. UPDATE payments_received FOREIGN KEY
-- Reference updated table name
-- ---------------------------------------------------------

-- First drop the existing foreign key (name may vary)
-- If this fails, check the actual constraint name in your database

-- Re-add foreign key referencing invoices table
ALTER TABLE payments_received 
ADD CONSTRAINT fk_payment_invoice 
FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE;


-- ---------------------------------------------------------
-- 7. ADD receipt_number COLUMN IF NOT EXISTS
-- Some systems may not have this
-- ---------------------------------------------------------

-- Check and add if not exists (run separately if this errors)
-- ALTER TABLE payments_received ADD COLUMN receipt_number VARCHAR(50) NULL AFTER id;


-- ---------------------------------------------------------
-- 8. CREATE AUTO INVOICE LOG TABLE
-- For tracking automated rent invoice generation
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS auto_invoice_log (
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
);

-- Index for log lookups
CREATE INDEX idx_auto_invoice_log_period ON auto_invoice_log (billing_month, billing_year);
CREATE INDEX idx_auto_invoice_log_lease ON auto_invoice_log (lease_id);


-- ---------------------------------------------------------
-- 9. ADD AUTO INVOICE SETTINGS
-- Global settings for automatic rent invoicing
-- ---------------------------------------------------------

-- Add auto invoice enabled setting
INSERT INTO system_settings (setting_key, setting_value) 
VALUES ('auto_invoice_enabled', 'no')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- Add auto invoice day setting (1-28)
INSERT INTO system_settings (setting_key, setting_value) 
VALUES ('auto_invoice_day', '1')
ON DUPLICATE KEY UPDATE setting_key = setting_key;


-- ---------------------------------------------------------
-- 10. UPDATE TRANSACTION SERIES SETTINGS
-- Add separate series for rent and other invoices
-- with auto_reset support
-- ---------------------------------------------------------

-- Note: This will need to be handled carefully to preserve existing numbers
-- The application code will handle the migration of the JSON structure
-- Below is just for reference - the actual update happens via PHP

-- New structure will be:
-- {
--   "rent_invoice": {
--     "prefix": "RNT-",
--     "suffix": "",
--     "starting_number": "00001",
--     "current_number": 0,
--     "include_year": true,
--     "auto_reset": true,
--     "last_reset_year": 2025
--   },
--   "other_invoice": {
--     "prefix": "CHR-",
--     "suffix": "",
--     "starting_number": "00001", 
--     "current_number": 0,
--     "include_year": true,
--     "auto_reset": true,
--     "last_reset_year": 2025
--   },
--   "payment": { ... },
--   "expense": { ... },
--   "maintenance": { ... },
--   "lease": { ... }
-- }


-- ---------------------------------------------------------
-- 11. ADD PERMISSIONS FOR CHARGE TYPE MANAGEMENT
-- ---------------------------------------------------------

INSERT IGNORE INTO permissions (permission_name, description) VALUES
('charge_type_manage', 'Manage charge types'),
('charge_type_create', 'Create charge type'),
('charge_type_update', 'Update charge type'),
('charge_type_delete', 'Delete charge type');

-- Assign to admin role (role_id = 1)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions WHERE permission_name IN (
    'charge_type_manage', 'charge_type_create', 'charge_type_update', 'charge_type_delete'
);

-- Assign to accountant role (role_id = 3)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE permission_name IN (
    'charge_type_manage', 'charge_type_create', 'charge_type_update'
);


SET FOREIGN_KEY_CHECKS = 1;


-- =========================================================
-- VERIFICATION QUERIES (Optional - run to verify migration)
-- =========================================================

-- Check invoices table structure
-- DESCRIBE invoices;

-- Check charge_types table
-- SELECT * FROM charge_types;

-- Check auto invoice settings
-- SELECT * FROM system_settings WHERE setting_key LIKE 'auto_invoice%';

-- Verify invoice foreign key
-- SHOW CREATE TABLE invoices;

-- Verify payments_received foreign key
-- SHOW CREATE TABLE payments_received;
