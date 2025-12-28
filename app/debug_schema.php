<?php
require_once 'init.php';
global $conn;
$result = $conn->query("DESCRIBE rent_invoices");
while($row = $result->fetch_assoc()) {
    print_r($row);
}
?>
