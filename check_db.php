<?php
require_once 'app/init.php';
echo "Connected to database: " . $db_name . "\n";
$result = $conn->query("SELECT DATABASE()");
$row = $result->fetch_row();
echo "Actual database: " . $row[0] . "\n";
?>
