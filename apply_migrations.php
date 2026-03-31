<?php
require_once('app/db.php');

$files = [
    'migrations/20260331_a4_two_logo.sql',
    'migrations/20260331_a2_brand_color.sql',
    'migrations/20260331_a3_communication.sql'
];

foreach ($files as $file) {
    echo "Running $file...\n";
    $sql = file_get_contents($file);
    if ($conn->multi_query($sql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        echo "Finished $file.\n";
    } else {
        echo "Error in $file: " . $conn->error . "\n";
    }
}
?>