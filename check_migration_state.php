<?php
require_once 'app/init.php';
$conn = $GLOBALS['conn'];

$tables = ['organizations', 'users', 'roles', 'properties'];
foreach ($tables as $table) {
    echo "Checking table: $table\n";
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'org_id'");
    if ($res && $res->num_rows > 0) {
        echo "  - org_id exists\n";
    } else {
        echo "  - org_id does NOT exist\n";
    }
}

$res = $conn->query("SELECT COUNT(*) FROM organizations");
if ($res) {
    echo "Organizations count: " . $res->fetch_row()[0] . "\n";
} else {
    echo "Organizations table does not exist.\n";
}
?>