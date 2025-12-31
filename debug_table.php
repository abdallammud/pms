<?php
require_once 'app/db.php';
$res = $mysqli->query('DESCRIBE expenses');
if (!$res) {
    die("Table 'expenses' does not exist or error: " . $mysqli->error);
}
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
?>