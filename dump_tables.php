<?php
require './app/db.php';
$r = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $r->fetch_row()) {
    $tables[] = $row[0];
}
file_put_contents('all_tables.txt', implode("\n", $tables));