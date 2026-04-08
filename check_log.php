<?php
require './app/db.php';
$r = $conn->query('DESCRIBE auto_invoice_log');
while ($row = $r->fetch_row()) {
    echo $row[0] . "\n";
}
