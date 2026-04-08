<?php
require './app/db.php';
$r = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'test_edurdur_1'");
while ($row = $r->fetch_row()) {
    echo $row[0] . "\n";
}
