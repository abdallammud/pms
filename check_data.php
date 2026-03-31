<?php
require_once 'app/init.php';
$conn = $GLOBALS['conn'];

echo "--- Organizations ---\n";
$res = $conn->query("SELECT * FROM organizations");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "\n--- Users ---\n";
$res = $conn->query("SELECT id, username, org_id, is_super_admin FROM users LIMIT 10");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "\n--- Database State ---\n";
$res = $conn->query("SELECT COUNT(*) FROM properties WHERE org_id IS NULL");
if ($res) {
    echo "Properties with NULL org_id: " . $res->fetch_row()[0] . "\n";
}
?>