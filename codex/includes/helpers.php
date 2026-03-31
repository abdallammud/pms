<?php
require_once __DIR__ . '/config.php';

/**
 * Resolve a stored image path into a full public URL.
 * Paths in the DB look like: public/uploads/units/abc.jpg
 */
function image_url(?string $path): ?string {
    if (empty($path)) return null;
    return rtrim(APP_BASE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Return a placeholder image URL when no photo is available.
 */
function placeholder_url(string $type = 'unit'): string {
    // Unsplash source for consistent placeholders
    if ($type === 'property') {
        return 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800&q=80&auto=format&fit=crop';
    }
    return 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&q=80&auto=format&fit=crop';
}

/**
 * Format currency. Defaults to KES.
 */
function fmt_currency(float $amount, string $symbol = 'KES'): string {
    return $symbol . ' ' . number_format($amount, 0);
}

/**
 * Return CORS headers for API responses.
 */
function api_headers(): void {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: ' . CORS_ORIGIN);
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

/**
 * Return a short "bedroom" label from room_count and unit_type string.
 */
function unit_label(?int $rooms, ?string $type): string {
    if (!empty($type)) return $type;
    if ($rooms === null) return 'Unit';
    return match($rooms) {
        0       => 'Studio',
        1       => '1 Bedroom',
        2       => '2 Bedroom',
        3       => '3 Bedroom',
        default => $rooms . ' Bedroom'
    };
}

/**
 * Build a safe integer from a request parameter.
 */
function req_int(string $key, int $default = 0, int $min = 0, int $max = PHP_INT_MAX): int {
    $val = isset($_GET[$key]) ? (int)$_GET[$key] : $default;
    return max($min, min($max, $val));
}

/**
 * Build a safe float from a request parameter.
 */
function req_float(string $key, float $default = 0): float {
    return isset($_GET[$key]) ? max(0, (float)$_GET[$key]) : $default;
}

/**
 * Safely trim a string GET param.
 */
function req_str(string $key, string $default = ''): string {
    return isset($_GET[$key]) ? trim((string)$_GET[$key]) : $default;
}
