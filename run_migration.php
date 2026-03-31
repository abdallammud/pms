<?php
require_once 'app/init.php';

echo "Checking database connection...\n";
if (!isset($GLOBALS['conn'])) {
    die("Error: \$GLOBALS['conn'] is not set.\n");
}
$conn = $GLOBALS['conn'];
echo "Connected to: " . $conn->query("SELECT DATABASE()")->fetch_row()[0] . "\n";

$sqlFile = 'migrations/20260326_phase6_invoice_items.sql';
if (!file_exists($sqlFile)) {
    die("Migration file not found: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);
$queries = explode(';', $sql);

$conn->query("SET FOREIGN_KEY_CHECKS = 0");

foreach ($queries as $query) {
    $query = trim($query);
    if (empty($query))
        continue;

    // Remove comments
    $lines = explode("\n", $query);
    $cleanLines = [];
    foreach ($lines as $line) {
        $trimmedLine = trim($line);
        if (strpos($trimmedLine, '--') === 0 || strpos($trimmedLine, '#') === 0)
            continue;
        $cleanLines[] = $line;
    }
    $cleanQuery = implode("\n", $cleanLines);
    $cleanQuery = trim($cleanQuery);
    if (empty($cleanQuery))
        continue;

    if (!$conn->query($cleanQuery)) {
        echo "Error executing query: " . $conn->error . "\n";
        echo "Query starts with: " . substr($cleanQuery, 0, 100) . "...\n";
    } else {
        echo "Successfully executed query segment.\n";
    }
}

$conn->query("SET FOREIGN_KEY_CHECKS = 1");
echo "Migration completed.\n";
?>