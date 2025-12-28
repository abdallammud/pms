<?php
require_once 'app/init.php';
$r = $conn->query("SELECT * FROM system_settings");
while($row = $r->fetch_assoc()) {
    echo $row['setting_key'] . ': ' . $row['setting_value'] . PHP_EOL;
}
?>
