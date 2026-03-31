<?php
/**
 * Public Listings Site - Configuration
 * Adjust these values for your deployment environment.
 */

// ── Database ──────────────────────────────────────────────────────────────────
// Reuses the main PMS app config if available, otherwise falls back to defaults.
$_pms_config = __DIR__ . '/../../app/config.php';
if (file_exists($_pms_config)) {
    require_once $_pms_config;
} else {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'pms_db');
}

// ── Site Identity ─────────────────────────────────────────────────────────────
define('SITE_NAME',    'PropSpace');
define('SITE_TAGLINE', 'Find your perfect rental home.');
define('SITE_URL',     'https://www.propspace.com');      // public site root
define('APP_URL',      'https://app.propspace.com');      // dashboard app URL

// ── Asset Base URL for Images ─────────────────────────────────────────────────
// Images are stored relative to the PMS app root. Adjust this URL so the
// landing page can serve unit/property images correctly.
// In production this would be https://app.propspace.com/
define('APP_BASE_URL', 'http://localhost/FileZillaFTP/source/diff/bu/pms/');

// ── Pagination ────────────────────────────────────────────────────────────────
define('DEFAULT_PAGE_SIZE', 12);
define('MAX_PAGE_SIZE',     24);

// ── CORS (for API endpoints) ──────────────────────────────────────────────────
define('CORS_ORIGIN', '*');   // tighten to your domain in production
