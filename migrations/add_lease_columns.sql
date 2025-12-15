-- =========================================================
-- LEASES TABLE MIGRATION
-- Add missing columns for extended lease information
-- =========================================================

-- Add guarantee_id column
ALTER TABLE leases ADD COLUMN IF NOT EXISTS guarantee_id INT NULL AFTER tenant_id;

-- Add property_id column (for direct property reference)
ALTER TABLE leases ADD COLUMN IF NOT EXISTS property_id INT NULL AFTER guarantee_id;

-- Add lease_conditions column (for storing custom conditions per lease)
ALTER TABLE leases ADD COLUMN IF NOT EXISTS lease_conditions TEXT NULL AFTER status;

-- Add vehicle_info column
ALTER TABLE leases ADD COLUMN IF NOT EXISTS vehicle_info TEXT NULL AFTER lease_conditions;

-- Add legal_weapons column
ALTER TABLE leases ADD COLUMN IF NOT EXISTS legal_weapons TEXT NULL AFTER vehicle_info;

-- Add witnesses column (JSON format for storing multiple witnesses)
ALTER TABLE leases ADD COLUMN IF NOT EXISTS witnesses JSON NULL AFTER legal_weapons;

-- Add created_at timestamp
ALTER TABLE leases ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER witnesses;

-- Add updated_at timestamp
ALTER TABLE leases ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Add foreign key for guarantee_id (optional, run if needed)
-- ALTER TABLE leases ADD CONSTRAINT fk_lease_guarantee FOREIGN KEY (guarantee_id) REFERENCES guarantees(id);

-- Add foreign key for property_id (optional, run if needed)  
-- ALTER TABLE leases ADD CONSTRAINT fk_lease_property FOREIGN KEY (property_id) REFERENCES properties(id);

-- =========================================================
-- ALTERNATIVE: If IF NOT EXISTS doesn't work, use these:
-- =========================================================
-- 
-- Run these one by one and ignore errors for columns that already exist:
--
-- ALTER TABLE leases ADD COLUMN guarantee_id INT NULL AFTER tenant_id;
-- ALTER TABLE leases ADD COLUMN property_id INT NULL AFTER guarantee_id;
-- ALTER TABLE leases ADD COLUMN lease_conditions TEXT NULL;
-- ALTER TABLE leases ADD COLUMN vehicle_info TEXT NULL;
-- ALTER TABLE leases ADD COLUMN legal_weapons TEXT NULL;
-- ALTER TABLE leases ADD COLUMN witnesses JSON NULL;
-- ALTER TABLE leases ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
-- ALTER TABLE leases ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
