<?php
require_once 'init.php';
global $conn;

// Check if column exists
$check = $conn->query("SHOW COLUMNS FROM rent_invoices LIKE 'invoice_number'");
if ($check->num_rows == 0) {
    if ($conn->query("ALTER TABLE rent_invoices ADD COLUMN invoice_number VARCHAR(50) AFTER id")) {
        echo "Column added successfully";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column already exists";
}
?>
