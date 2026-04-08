<?php
$sql = file_get_contents('d:/xampp/htdocs/FileZillaFTP/source/diff/bu/pms/reset.sql');
$sql = preg_replace('/TRUNCATE TABLE `([^`]+)`;/', "DELETE FROM `$1`;\nALTER TABLE `$1` AUTO_INCREMENT = 1;", $sql);
file_put_contents('d:/xampp/htdocs/FileZillaFTP/source/diff/bu/pms/reset.sql', $sql);
echo "SQL updated.\n";
