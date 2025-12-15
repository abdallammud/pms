<?php 
date_default_timezone_set('Africa/Mogadishu');
require_once('db.php');
require_once('utilities.php');
require_once('config.php');
require_once('helpers.php');

require_once('auth.php'); 
require_once('autoload.php');



// $GLOBALS['logoPath'] = baseUri() .'/assets/images/'.return_setting('system_logo');
// Dynamic logo path from settings table
$logoResult = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'logo_path'");
$logoRow = $logoResult ? $logoResult->fetch_assoc() : null;
$GLOBALS['logoPath'] = ($logoRow && !empty($logoRow['setting_value'])) 
    ? baseUri() . '/' . $logoRow['setting_value'] 
    : baseUri() . '/public/images/logo.png'; // Default fallback







// Refresh session and permissions on every page load

?>