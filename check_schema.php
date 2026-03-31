<?php
require_once('app/db.php');
$res = $conn->query("SHOW CREATE TABLE system_settings");
$row = $res->fetch_array();
echo $row[1];
?>