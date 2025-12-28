<?php
$c = new mysqli('localhost', 'root', '', 'test_edurdur');
$r = $c->query('SHOW TABLES');
while($row = $r->fetch_row()) echo $row[0].PHP_EOL;
?>
