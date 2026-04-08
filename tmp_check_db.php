<?php
require './app/db.php';
$r = $conn->query('SELECT * FROM invoices LIMIT 1');
$row = $r->fetch_assoc();
if ($row) {
    print_r(array_keys($row));
} else {
    // Check if table is empty, use describe
    $r = $conn->query('DESCRIBE invoices');
    while ($row = $r->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
}
