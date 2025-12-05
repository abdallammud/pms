<?php 
$host = 'localhost'; // Extract from DSN or define directly
$db_name = 'pms_db'; // Extract from DSN or define directly
$charset = 'utf8mb4'; // Extract from DSN or define directly
$username = 'root';
$password = '';

// $username = 'u562838275_pms';
// $db_name = 'u562838275_pms';
// $password = 'kBH/6|c7N/';

$mysqli = new mysqli($host, $username, $password, $db_name);

if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}

$GLOBALS['conn'] = $mysqli;
$conn = $mysqli; 

$mysqli->set_charset($charset);
