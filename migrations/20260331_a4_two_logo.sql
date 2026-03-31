-- =============================================================
-- A4: Two-Logo System
-- Adds doc_logo_path setting for document/print logos.
-- Existing logo_path remains the system/sidebar UI logo.
-- Run once per environment.
-- =============================================================

-- For every org that already has a system logo, seed doc_logo_path
-- with the same value so documents continue to show the existing logo
-- until the admin uploads a dedicated document logo.
INSERT INTO system_settings (org_id, setting_key, setting_value)
SELECT org_id, 'doc_logo_path', setting_value
FROM system_settings
WHERE setting_key = 'logo_path'
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- For orgs with no logo at all yet, insert an empty placeholder
-- so the key always exists and the UI can show the upload zone.
INSERT IGNORE INTO system_settings (org_id, setting_key, setting_value)
SELECT DISTINCT org_id, 'doc_logo_path', ''
FROM system_settings
WHERE org_id NOT IN (
    SELECT org_id FROM system_settings WHERE setting_key = 'doc_logo_path'
);
