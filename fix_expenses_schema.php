<?php
chdir(__DIR__ . '/app');
require_once('init.php');
$sql = "ALTER TABLE expenses ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL AFTER updated_by";
if ($conn->query($sql)) {
    echo "Success: Column updated_at added to expenses table.\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
