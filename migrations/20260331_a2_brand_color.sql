-- =============================================================
-- A2: Brand Color Selection
-- Seeds brand_primary_color with the default dark-blue value
-- for every existing org that doesn't already have it set.
-- Run once per environment.
-- =============================================================

INSERT IGNORE INTO system_settings (org_id, setting_key, setting_value)
SELECT DISTINCT org_id, 'brand_primary_color', '1d3354'
FROM system_settings
WHERE org_id NOT IN (
    SELECT org_id FROM system_settings WHERE setting_key = 'brand_primary_color'
);
