<?php
require_once 'init.php';

if (isset($_GET['folder']) && isset($_GET['file'])) {
    $folder = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['folder']);
    $file = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['file']);

    $path = "../views/{$folder}/modals/{$file}.php";

    if (file_exists($path)) {
        require_once $path;
    } else {
        echo "<div class='alert alert-danger'>Modal not found: {$folder}/{$file}</div>";
    }
}
?>
