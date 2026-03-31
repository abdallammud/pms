<?php
require_once __DIR__ . '/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    if (defined('API_REQUEST')) {
        http_response_code(503);
        echo json_encode(['error' => true, 'message' => 'Service temporarily unavailable.']);
        exit;
    }
    die('Database connection failed.');
}

$conn->set_charset('utf8mb4');
