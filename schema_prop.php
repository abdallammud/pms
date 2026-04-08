<?php
require('d:/xampp/htdocs/FileZillaFTP/source/diff/bu/pms/app/init.php');
$res = $conn->query("DESCRIBE properties");
while ($r = $res->fetch_assoc())
    echo $r['Field'] . " ";
echo "\n";
