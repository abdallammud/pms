<?php
require_once('app/db.php');

// 1. Ensure org_id column exists
$check_org_id = $conn->query("SHOW COLUMNS FROM system_settings LIKE 'org_id'");
if ($check_org_id->num_rows == 0) {
    echo "Adding org_id column...\n";
    $conn->query("ALTER TABLE system_settings ADD COLUMN org_id INT UNSIGNED NOT NULL DEFAULT 0 AFTER id");
}

// 2. Drop the problematic unique index on setting_key
// We need to find the exact name. The error said 'setting_key'.
$conn->query("ALTER TABLE system_settings DROP INDEX setting_key");

// 3. Add the composite unique index (org_id, setting_key)
echo "Adding composite unique index (org_id, setting_key)...\n";
$conn->query("ALTER TABLE system_settings ADD UNIQUE KEY idx_org_setting (org_id, setting_key)");

echo "Migration complete.\n";
?>