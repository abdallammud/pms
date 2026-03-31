<?php
ob_start();
date_default_timezone_set('Africa/Mogadishu');
require_once('db.php');
require_once('Model.php');

require_once('utilities.php');
require_once('config.php');
require_once('helpers.php');

require_once('auth.php');
require_once('autoload.php');



// $GLOBALS['logoPath'] = baseUri() .'/assets/images/'.return_setting('system_logo');
// Dynamic logo path from settings table
$orgFilter = "1=1";
if (function_exists('current_org_id')) {
    $activeOrgId = (int) current_org_id();
    if ($activeOrgId > 0) {
        $orgFilter = "org_id = " . $activeOrgId;
    }
}
// System/sidebar logo (light version)
$logoResult = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'logo_path' AND $orgFilter ORDER BY id DESC LIMIT 1");
$logoRow = $logoResult ? $logoResult->fetch_assoc() : null;
$GLOBALS['logoPath'] = ($logoRow && !empty($logoRow['setting_value']))
    ? baseUri() . '/' . $logoRow['setting_value']
    : baseUri() . '/public/images/logo.png';

// Document logo (full-colour — used on invoices, reports, lease PDFs)
$docLogoResult = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'doc_logo_path' AND $orgFilter ORDER BY id DESC LIMIT 1");
$docLogoRow = $docLogoResult ? $docLogoResult->fetch_assoc() : null;
$GLOBALS['docLogoPath'] = ($docLogoRow && !empty($docLogoRow['setting_value']))
    ? baseUri() . '/' . $docLogoRow['setting_value']
    : $GLOBALS['logoPath']; // Fall back to system logo if no doc logo set yet
$GLOBALS['docLogoLocalPath'] = ($docLogoRow && !empty($docLogoRow['setting_value']))
    ? $docLogoRow['setting_value']
    : (($logoRow && !empty($logoRow['setting_value'])) ? $logoRow['setting_value'] : 'public/images/logo.png');

// Brand primary color (hex without #, e.g. "1d3354")
$colorResult = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'brand_primary_color' AND $orgFilter ORDER BY id DESC LIMIT 1");
$colorRow = $colorResult ? $colorResult->fetch_assoc() : null;
$GLOBALS['brandPrimaryColor'] = ($colorRow && !empty($colorRow['setting_value']))
    ? ltrim($colorRow['setting_value'], '#')
    : '1d3354';







// Refresh session and permissions on every page load

?>