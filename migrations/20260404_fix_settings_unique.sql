-- Fix unique constraint for multitenancy in system_settings
SET FOREIGN_KEY_CHECKS = 0;

-- Drop the old global unique constraint
-- Note: In older MySQL versions, the constraint name might be the column name
ALTER TABLE system_settings DROP INDEX IF EXISTS setting_key;

-- Drop the non-unique index if it was created by the previous migration
ALTER TABLE system_settings DROP INDEX IF EXISTS idx_system_settings_org_key;

-- Add the correct composite unique index
ALTER TABLE system_settings ADD UNIQUE INDEX idx_system_settings_org_key (org_id, setting_key);

SET FOREIGN_KEY_CHECKS = 1;
