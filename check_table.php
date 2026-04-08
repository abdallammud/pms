<?php
require_once('app/init.php');
$res = $conn->query("DESCRIBE expenses");
$columns = [];
while ($row = $res->fetch_assoc()) {
    $columns[] = $row['Field'];
}
header('Content-Type: application/json');
echo json_encode($columns);
