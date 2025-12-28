<?php
$host = 'localhost'; // Extract from DSN or define directly
<<<<<<< HEAD
$db_name = 'test_edurdur_1'; // Extract from DSN or define directly
=======
$db_name = 'test_edurdur'; // Extract from DSN or define directly
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
$charset = 'utf8mb4'; // Extract from DSN or define directly
$username = 'root';
$password = '';

// $username = 'u562838275_pms';
// $db_name = 'u562838275_pms';
<<<<<<< HEAD
// $password = 'M@7yHJM8';
=======
// $password = 'kBH/6|c7N/';
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6

// Check this

$mysqli = new mysqli($host, $username, $password, $db_name);

if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}

$GLOBALS['conn'] = $mysqli;
$conn = $mysqli;

$mysqli->set_charset($charset);
