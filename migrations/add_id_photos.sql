-- =====================================================
-- ID Photo Upload Migration for Tenants and Guarantees
-- Run this script in phpMyAdmin or MySQL CLI
-- =====================================================

-- Add ID photo columns to tenants table
ALTER TABLE tenants 
  ADD COLUMN id_photo VARCHAR(255) DEFAULT NULL AFTER id_number,
  ADD COLUMN work_id_photo VARCHAR(255) DEFAULT NULL AFTER id_photo;

-- Make id_number NOT NULL for tenants (for new records)
-- Note: This will fail if existing records have NULL id_number
-- If you have existing data without id_number, run this first:
-- UPDATE tenants SET id_number = CONCAT('TEMP_', id) WHERE id_number IS NULL OR id_number = '';
ALTER TABLE tenants MODIFY COLUMN id_number VARCHAR(50) NOT NULL;

-- Add ID photo columns to guarantees table
ALTER TABLE guarantees 
  ADD COLUMN id_photo VARCHAR(255) DEFAULT NULL AFTER id_number,
  ADD COLUMN work_id_photo VARCHAR(255) DEFAULT NULL AFTER id_photo;

-- Make id_number NOT NULL for guarantees (for new records)
-- Note: This will fail if existing records have NULL id_number
-- If you have existing data without id_number, run this first:
-- UPDATE guarantees SET id_number = CONCAT('TEMP_', id) WHERE id_number IS NULL OR id_number = '';
ALTER TABLE guarantees MODIFY COLUMN id_number VARCHAR(50) NOT NULL;
