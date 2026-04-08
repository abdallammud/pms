<?php
chdir(__DIR__ . '/app');
require_once('init.php');
$res = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $res->fetch_array())
    $tables[] = $row[0];
echo implode(", ", $tables);
