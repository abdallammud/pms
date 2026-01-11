<?php
require_once('app/db.php');
if (!$conn)
    die("No connection");
echo "DB: " . $conn->host_info . "\n";
$res = $conn->query("SELECT setting_key, setting_value FROM system_settings");
if (!$res)
    die("Query failed: " . $conn->error);
echo "Rows: " . $res->num_rows . "\n";
while ($r = $res->fetch_assoc()) {
    echo $r['setting_key'] . "=" . $r['setting_value'] . "\n";
}
?>