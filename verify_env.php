<?php
require_once('app/config.php');
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn)
    die("Connection failed: " . mysqli_connect_error());

function checkTable($conn, $table)
{
    $res = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    return mysqli_num_rows($res) > 0;
}

function checkSetting($conn, $key)
{
    $res = mysqli_query($conn, "SELECT setting_value FROM system_settings WHERE setting_key = '$key'");
    if ($row = mysqli_fetch_assoc($res))
        return $row['setting_value'];
    return null;
}

echo "--- Pre-condition Check ---\n";
echo "sms_log table: " . (checkTable($conn, 'sms_log') ? "EXISTS" : "MISSING") . "\n";
echo "brand_primary_color: " . (checkSetting($conn, 'brand_primary_color') ?? "MISSING") . "\n";
echo "doc_logo_path: " . (checkSetting($conn, 'doc_logo_path') ?? "MISSING") . "\n";
echo "sms_enabled: " . (checkSetting($conn, 'sms_enabled') ?? "MISSING") . "\n";

$res = mysqli_query($conn, "SELECT id FROM permissions WHERE permission_name = 'communication_manage'");
echo "communication_manage permission: " . (mysqli_num_rows($res) > 0 ? "EXISTS" : "MISSING") . "\n";

$res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tenants WHERE phone IS NOT NULL AND phone != ''");
$row = mysqli_fetch_assoc($res);
echo "Tenants with phone: " . $row['cnt'] . "\n";

$res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM invoices");
$row = mysqli_fetch_assoc($res);
echo "Total Invoices: " . $row['cnt'] . "\n";
?>