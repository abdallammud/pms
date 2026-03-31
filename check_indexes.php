<?php
require_once('app/db.php');
$res = $conn->query("SHOW INDEX FROM system_settings");
while ($row = $res->fetch_assoc()) {
    echo "Index: " . $row['Key_name'] . " - Column: " . $row['Column_name'] . " - Non-Unique: " . $row['Non_unique'] . "\n";
}
?>