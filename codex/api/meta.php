<?php
/**
 * GET /codex/api/meta.php
 * Returns filter metadata: cities, unit types, amenities, price range.
 * This is called once on page load to populate filter dropdowns.
 */
define('API_REQUEST', true);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

api_headers();

// Cities that have at least one available listed unit
$cities = [];
$r = $conn->query("
    SELECT DISTINCT p.city
    FROM units u
    JOIN properties p ON u.property_id = p.id
    WHERE u.status = 'vacant' AND u.is_listed = 1 AND p.city IS NOT NULL AND p.city != ''
    ORDER BY p.city ASC
");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $cities[] = $row['city'];
    }
}

// Unit types available
$unit_types = [];
$r = $conn->query("
    SELECT DISTINCT
        COALESCE(ut.id, 0)        AS id,
        COALESCE(ut.type_name, u.unit_type, 'Other') AS label
    FROM units u
    LEFT JOIN unit_types ut ON u.unit_type_id = ut.id
    WHERE u.status = 'vacant' AND u.is_listed = 1
    ORDER BY label ASC
");
if ($r) {
    $seen = [];
    while ($row = $r->fetch_assoc()) {
        if (!empty($row['label']) && !in_array($row['label'], $seen)) {
            $unit_types[] = ['id' => (int)$row['id'], 'label' => $row['label']];
            $seen[] = $row['label'];
        }
    }
}

// Amenities that at least one available unit has
$amenities = [];
$r = $conn->query("
    SELECT DISTINCT a.id, a.name
    FROM amenities a
    JOIN unit_amenities ua ON a.id = ua.amenity_id
    JOIN units u ON ua.unit_id = u.id
    WHERE u.status = 'vacant' AND u.is_listed = 1
    ORDER BY a.name ASC
");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $amenities[] = ['id' => (int)$row['id'], 'name' => $row['name']];
    }
}

// Price range across all available units
$price_range = ['min' => 0, 'max' => 0];
$r = $conn->query("
    SELECT MIN(rent_amount) AS min_price, MAX(rent_amount) AS max_price
    FROM units
    WHERE status = 'vacant' AND is_listed = 1
");
if ($r) {
    $row = $r->fetch_assoc();
    $price_range = [
        'min' => (float)($row['min_price'] ?? 0),
        'max' => (float)($row['max_price'] ?? 0),
    ];
}

// Total counts
$counts = ['units' => 0, 'properties' => 0];
$r = $conn->query("
    SELECT
        COUNT(DISTINCT u.id)          AS total_units,
        COUNT(DISTINCT u.property_id) AS total_properties
    FROM units u
    WHERE u.status = 'vacant' AND u.is_listed = 1
");
if ($r) {
    $row = $r->fetch_assoc();
    $counts = [
        'units'      => (int)$row['total_units'],
        'properties' => (int)$row['total_properties'],
        'cities'     => count($cities),
    ];
}

echo json_encode([
    'cities'      => $cities,
    'unit_types'  => $unit_types,
    'amenities'   => $amenities,
    'price_range' => $price_range,
    'counts'      => $counts,
]);
