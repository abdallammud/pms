<?php
$host = 'localhost';
$db_name = 'test_edurdur_1';
$username = 'root';
$password = '';
mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($host, $username, $password, $db_name);
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

$sql = file_get_contents('d:/xampp/htdocs/FileZillaFTP/source/diff/bu/pms/reset.sql');

if (!$conn->multi_query($sql)) {
    echo "MultiQuery Failed: " . $conn->error . "\n";
} else {
    do {
        if ($res = $conn->store_result()) {
            $res->free();
        }
    } while ($conn->more_results() && $conn->next_result());

    if ($conn->errno) {
        echo "Error in batch execution: " . $conn->error . "\n";
    } else {
        echo "Base SQL executed successfully.\n";
    }
}
