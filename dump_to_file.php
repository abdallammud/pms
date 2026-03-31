<?php
require_once 'app/init.php';
$conn = $GLOBALS['conn'];

function dumpTable($conn, $table)
{
    echo "--- $table ---\n";
    $res = $conn->query("SELECT * FROM `$table` LIMIT 100");
    if (!$res) {
        echo "Error querying $table: " . $conn->error . "\n";
        return;
    }
    while ($row = $res->fetch_assoc()) {
        echo json_encode($row, JSON_PRETTY_PRINT) . "\n";
    }
}

ob_start();
dumpTable($conn, 'organizations');
dumpTable($conn, 'users');
$output = ob_get_clean();
file_put_contents('dump_output.txt', $output);
echo "Dump written to dump_output.txt\n";
?>