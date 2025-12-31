<?php
require_once 'app/db.php';

// Add expense_type if missing
$check = $mysqli->query("SHOW COLUMNS FROM `expenses` LIKE 'expense_type'");
if ($check->num_rows == 0) {
    if ($mysqli->query("ALTER TABLE `expenses` ADD COLUMN `expense_type` varchar(50) DEFAULT 'Property' AFTER `property_id`")) {
        echo "Column 'expense_type' added.<br>";
    } else {
        echo "Error adding 'expense_type': " . $mysqli->error . "<br>";
    }
}

// Make property_id nullable
if ($mysqli->query("ALTER TABLE `expenses` MODIFY COLUMN `property_id` int(11) DEFAULT NULL")) {
    echo "Column 'property_id' changed to NULL.<br>";
} else {
    echo "Error changing 'property_id': " . $mysqli->error . "<br>";
}

?>