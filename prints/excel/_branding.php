<?php
/**
 * Shared branding loader for Excel report exports.
 * Sets $logoPath (filesystem path) and $brandColorHex (6-char uppercase hex, no #).
 * Must be included AFTER ob_end_clean() and BEFORE PhpSpreadsheet setup.
 */

// DB connection (safe to call if already included)
if (!isset($conn)) {
    require_once('./app/db.php');
}

// Determine current org (best-effort — Excel files are called directly via URL)
$_excelOrgId = 0;
if (isset($_SESSION) && isset($_SESSION['org_id'])) {
    $_excelOrgId = (int) $_SESSION['org_id'];
}
$_excelOrgClause = $_excelOrgId > 0 ? "AND org_id = $_excelOrgId" : '';

// Document logo (falls back to system logo, then to default)
$_logoQuery = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'doc_logo_path' $_excelOrgClause ORDER BY id DESC LIMIT 1");
$_logoRow   = $_logoQuery ? $_logoQuery->fetch_assoc() : null;
if (!$_logoRow || empty($_logoRow['setting_value'])) {
    $_logoQuery = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'logo_path' $_excelOrgClause ORDER BY id DESC LIMIT 1");
    $_logoRow   = $_logoQuery ? $_logoQuery->fetch_assoc() : null;
}
$logoPath = ($_logoRow && !empty($_logoRow['setting_value'])) ? realpath($_logoRow['setting_value']) : false;

// Brand primary color
$_colorQuery   = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'brand_primary_color' $_excelOrgClause ORDER BY id DESC LIMIT 1");
$_colorRow     = $_colorQuery ? $_colorQuery->fetch_assoc() : null;
$brandColorHex = ($_colorRow && !empty($_colorRow['setting_value']))
    ? strtoupper(ltrim($_colorRow['setting_value'], '#'))
    : '1D3354';

unset($_excelOrgId, $_excelOrgClause, $_logoQuery, $_logoRow, $_colorQuery, $_colorRow);
