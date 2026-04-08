<?php
/**
 * Redirect /app/pdf.php to /pdf.php for backward compatibility 
 * or when called from subdirectories.
 */
$query = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header("Location: ../pdf.php" . $query);
exit;
