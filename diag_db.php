<?php
require_once('app/db.php');
echo "Connected to DB: " . $db_name . "\n";
echo "Tables:\n";
$res = $conn->query("SHOW TABLES");
while ($row = $res->fetch_array()) {
    echo "- " . $row[0] . "\n";
}

echo "\nSettings Count: ";
$res = $conn->query("SELECT COUNT(*) as count FROM system_settings");
echo ($res ? $res->fetch_assoc()['count'] : "ERROR") . "\n";

$critical_settings = ['brand_primary_color', 'doc_logo_path', 'sms_enabled'];
foreach ($critical_settings as $s) {
    $res = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = '$s'");
    $val = ($res && $row = $res->fetch_assoc()) ? $row['setting_value'] : "NOT SET";
    echo "$s: $val\n";
}
?>