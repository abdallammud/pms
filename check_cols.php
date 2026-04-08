<?php
chdir(__DIR__ . '/app');
require_once('init.php');
$res = $conn->query("DESCRIBE expenses");
if (!$res) {
    echo "Error: " . $conn->error;
    exit;
}
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
