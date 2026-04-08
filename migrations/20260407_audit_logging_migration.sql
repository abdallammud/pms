-- Audit Logging Migration (2026-04-07)
-- Standardizing created_by and updated_by across core tables

-- 1. Properties
ALTER TABLE properties ADD COLUMN IF NOT EXISTS created_by INT NULL;
ALTER TABLE properties ADD COLUMN IF NOT EXISTS updated_by INT NULL;

-- 2. Units
ALTER TABLE units ADD COLUMN IF NOT EXISTS created_by INT NULL;
ALTER TABLE units ADD COLUMN IF NOT EXISTS updated_by INT NULL;

-- 3. Tenants
ALTER TABLE tenants ADD COLUMN IF NOT EXISTS created_by INT NULL;
ALTER TABLE tenants ADD COLUMN IF NOT EXISTS updated_by INT NULL;

-- 4. Guarantees (Guarantors)
ALTER TABLE guarantees ADD COLUMN IF NOT EXISTS created_by INT NULL;
ALTER TABLE guarantees ADD COLUMN IF NOT EXISTS updated_by INT NULL;

-- 5. Leases
ALTER TABLE leases ADD COLUMN IF NOT EXISTS created_by INT NULL;
ALTER TABLE leases ADD COLUMN IF NOT EXISTS updated_by INT NULL;

-- 6. Invoices
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS created_by INT NULL;
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS updated_by INT NULL;

-- 7. Payments Received
ALTER TABLE payments_received ADD COLUMN IF NOT EXISTS created_by INT NULL;
ALTER TABLE payments_received ADD COLUMN IF NOT EXISTS updated_by INT NULL;

-- 8. Payment Allocations
ALTER TABLE payment_allocations ADD COLUMN IF NOT EXISTS created_by INT NULL;
ALTER TABLE payment_allocations ADD COLUMN IF NOT EXISTS updated_by INT NULL;

-- 9. Expenses
ALTER TABLE expenses ADD COLUMN IF NOT EXISTS created_by INT NULL;
ALTER TABLE expenses ADD COLUMN IF NOT EXISTS updated_by INT NULL;

-- 10. Maintenance Requests
ALTER TABLE maintenance_requests ADD COLUMN IF NOT EXISTS created_by INT NULL;
ALTER TABLE maintenance_requests ADD COLUMN IF NOT EXISTS updated_by INT NULL;

-- 11. Maintenance Assignments
ALTER TABLE maintenance_assignments ADD COLUMN IF NOT EXISTS created_by INT NULL;
ALTER TABLE maintenance_assignments ADD COLUMN IF NOT EXISTS updated_by INT NULL;

-- 12. Vendors
ALTER TABLE vendors ADD COLUMN IF NOT EXISTS created_by INT NULL;
ALTER TABLE vendors ADD COLUMN IF NOT EXISTS updated_by INT NULL;

-- 13. Users
ALTER TABLE users ADD COLUMN IF NOT EXISTS created_by INT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS updated_by INT NULL;

-- 14. Organizations
ALTER TABLE organizations ADD COLUMN IF NOT EXISTS created_by INT NULL;
ALTER TABLE organizations ADD COLUMN IF NOT EXISTS updated_by INT NULL;

-- 15. Invoice Items
ALTER TABLE invoice_items ADD COLUMN IF NOT EXISTS created_by INT NULL;
ALTER TABLE invoice_items ADD COLUMN IF NOT EXISTS updated_by INT NULL;
