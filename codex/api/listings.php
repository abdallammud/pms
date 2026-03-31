<?php
/**
 * GET /codex/api/listings.php
 *
 * Query params:
 *   page        int    default 1
 *   limit       int    default 12, max 24
 *   city        string filter by city
 *   type        string unit type label (partial match)
 *   type_id     int    unit_type_id exact match
 *   min_price   float
 *   max_price   float
 *   rooms       int    room_count
 *   amenities   string comma-separated amenity IDs
 *   sort        string featured|price_asc|price_desc|newest
 *   q           string search term (unit number, property name, city)
 *
 * Response:
 *   { data: [...], total, page, limit, pages }
 */
define('API_REQUEST', true);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

api_headers();

$page      = req_int('page', 1, 1);
$limit     = req_int('limit', DEFAULT_PAGE_SIZE, 1, MAX_PAGE_SIZE);
$offset    = ($page - 1) * $limit;
$city      = req_str('city');
$type      = req_str('type');
$type_id   = req_int('type_id');
$min_price = req_float('min_price');
$max_price = req_float('max_price');
$rooms     = req_int('rooms');
$q         = req_str('q');
$sort      = req_str('sort', 'featured');

$amenity_ids = [];
if (!empty($_GET['amenities'])) {
    $amenity_ids = array_filter(array_map('intval', explode(',', $_GET['amenities'])));
}

// ── Build WHERE ───────────────────────────────────────────────────────────────
$where  = ["u.status = 'vacant'", "u.is_listed = 1"];
$params = [];
$types  = '';

if (!empty($city)) {
    $where[]  = 'p.city = ?';
    $params[] = $city;
    $types   .= 's';
}

if ($type_id > 0) {
    $where[]  = 'u.unit_type_id = ?';
    $params[] = $type_id;
    $types   .= 'i';
} elseif (!empty($type)) {
    $like     = '%' . $type . '%';
    $where[]  = '(u.unit_type LIKE ? OR ut.type_name LIKE ?)';
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}

if ($min_price > 0) {
    $where[]  = 'u.rent_amount >= ?';
    $params[] = $min_price;
    $types   .= 'd';
}

if ($max_price > 0) {
    $where[]  = 'u.rent_amount <= ?';
    $params[] = $max_price;
    $types   .= 'd';
}

if ($rooms > 0) {
    $where[]  = 'u.room_count = ?';
    $params[] = $rooms;
    $types   .= 'i';
}

if (!empty($q)) {
    $like     = '%' . $q . '%';
    $where[]  = '(u.unit_number LIKE ? OR p.name LIKE ? OR p.city LIKE ? OR p.address LIKE ?)';
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    $types   .= 'ssss';
}

$where_sql = implode(' AND ', $where);

// ── Sort ──────────────────────────────────────────────────────────────────────
$order_by = match($sort) {
    'price_asc'  => 'u.rent_amount ASC',
    'price_desc' => 'u.rent_amount DESC',
    'newest'     => 'u.id DESC',
    default      => 'u.id DESC',
};

// ── Base FROM/JOIN ────────────────────────────────────────────────────────────
$base = "
    FROM units u
    LEFT JOIN properties p   ON u.property_id   = p.id
    LEFT JOIN property_types pt ON p.type_id     = pt.id
    LEFT JOIN unit_types ut  ON u.unit_type_id   = ut.id
    WHERE $where_sql
";

// ── Amenity filter (require ALL selected amenities) ───────────────────────────
if (!empty($amenity_ids)) {
    $placeholders = implode(',', array_fill(0, count($amenity_ids), '?'));
    $base .= "
        AND u.id IN (
            SELECT unit_id FROM unit_amenities
            WHERE amenity_id IN ($placeholders)
            GROUP BY unit_id
            HAVING COUNT(DISTINCT amenity_id) = " . count($amenity_ids) . "
        )
    ";
    foreach ($amenity_ids as $aid) {
        $params[] = $aid;
        $types   .= 'i';
    }
}

// ── Count ─────────────────────────────────────────────────────────────────────
$stmt = $conn->prepare("SELECT COUNT(DISTINCT u.id) $base");
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_row()[0];

// ── Fetch rows ────────────────────────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT
        u.id,
        u.unit_number,
        u.unit_type,
        u.floor_number,
        u.room_count,
        u.size_sqft,
        u.rent_amount,
        COALESCE(ut.type_name, u.unit_type) AS resolved_type,
        p.id   AS property_id,
        p.name AS property_name,
        p.address,
        p.city,
        p.region,
        p.district,
        pt.type_name AS property_type
    $base
    ORDER BY $order_by
    LIMIT ? OFFSET ?
");

$fetch_params = array_merge($params, [$limit, $offset]);
$fetch_types  = $types . 'ii';
$stmt->bind_param($fetch_types, ...$fetch_params);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Enrich each row ───────────────────────────────────────────────────────────
$units = [];
foreach ($rows as $row) {
    // Cover image
    $img = $conn->prepare("
        SELECT image_path FROM unit_images
        WHERE unit_id = ?
        ORDER BY is_cover DESC, id ASC
        LIMIT 1
    ");
    $img->bind_param('i', $row['id']);
    $img->execute();
    $img_row     = $img->get_result()->fetch_assoc();
    $cover_image = $img_row ? image_url($img_row['image_path']) : null;

    // Top 4 amenities
    $am = $conn->prepare("
        SELECT a.id, a.name FROM unit_amenities ua
        JOIN amenities a ON ua.amenity_id = a.id
        WHERE ua.unit_id = ?
        ORDER BY a.name ASC
        LIMIT 4
    ");
    $am->bind_param('i', $row['id']);
    $am->execute();
    $amenities = $am->get_result()->fetch_all(MYSQLI_ASSOC);

    $units[] = [
        'id'           => (int)$row['id'],
        'unit_number'  => $row['unit_number'],
        'unit_type'    => $row['resolved_type'] ?: 'Unit',
        'floor_number' => $row['floor_number'] !== null ? (int)$row['floor_number'] : null,
        'room_count'   => $row['room_count']   !== null ? (int)$row['room_count']   : null,
        'size_sqft'    => $row['size_sqft']    !== null ? (int)$row['size_sqft']    : null,
        'rent_amount'  => (float)$row['rent_amount'],
        'cover_image'  => $cover_image,
        'amenities'    => array_map(fn($a) => ['id' => (int)$a['id'], 'name' => $a['name']], $amenities),
        'property'     => [
            'id'      => (int)$row['property_id'],
            'name'    => $row['property_name'],
            'address' => $row['address'],
            'city'    => $row['city'],
            'region'  => $row['region'],
            'type'    => $row['property_type'],
        ],
    ];
}

echo json_encode([
    'data'  => $units,
    'total' => $total,
    'page'  => $page,
    'limit' => $limit,
    'pages' => (int)ceil($total / $limit),
]);
