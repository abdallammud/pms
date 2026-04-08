<?php
require './app/init.php';
$r = $conn->query('DESCRIBE invoices');
while ($row = $r->fetch_assoc()) {
    file_put_contents('columns.txt', $row['Field'] . "\n", FILE_APPEND);
}
