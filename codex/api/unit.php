<?php
/**
 * GET /codex/api/unit.php?id=X
 * Returns full details for a single listed, available unit.
 * Also returns similar units in the same property.
 */
define('API_REQUEST', true);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

api_headers();

$id = req_int('id', 0, 1);
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Unit ID is required.']);
    exit;
}

// ── Fetch unit ────────────────────────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT
        u.id,
        u.unit_number,
        u.unit_type,
        u.floor_number,
        u.room_count,
        u.size_sqft,
        u.rent_amount,
        u.status,
        COALESCE(ut.type_name, u.unit_type) AS resolved_type,
        p.id            AS property_id,
        p.name          AS property_name,
        p.address,
        p.city,
        p.region,
        p.district,
        p.description   AS property_description,
        pt.type_name    AS property_type,
        u2.name         AS manager_name
    FROM units u
    LEFT JOIN properties p    ON u.property_id   = p.id
    LEFT JOIN property_types pt ON p.type_id     = pt.id
    LEFT JOIN unit_types ut   ON u.unit_type_id  = ut.id
    LEFT JOIN users u2        ON p.manager_id    = u2.id
    WHERE u.id = ? AND u.status = 'vacant' AND u.is_listed = 1
    LIMIT 1
");
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    http_response_code(404);
    echo json_encode(['error' => true, 'message' => 'Unit not found or no longer available.']);
    exit;
}

// ── Images ────────────────────────────────────────────────────────────────────
$img_stmt = $conn->prepare("
    SELECT id, image_path, is_cover
    FROM unit_images
    WHERE unit_id = ?
    ORDER BY is_cover DESC, id ASC
");
$img_stmt->bind_param('i', $id);
$img_stmt->execute();
$img_rows = $img_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$images      = [];
$cover_image = null;
foreach ($img_rows as $ir) {
    $url     = image_url($ir['image_path']);
    $images[] = ['url' => $url, 'is_cover' => (bool)$ir['is_cover']];
    if ($ir['is_cover'] && !$cover_image) $cover_image = $url;
}
if (!$cover_image && !empty($images)) {
    $cover_image = $images[0]['url'];
}

// ── Amenities ─────────────────────────────────────────────────────────────────
$am_stmt = $conn->prepare("
    SELECT a.id, a.name
    FROM unit_amenities ua
    JOIN amenities a ON ua.amenity_id = a.id
    WHERE ua.unit_id = ?
    ORDER BY a.name ASC
");
$am_stmt->bind_param('i', $id);
$am_stmt->execute();
$amenities = $am_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Property images ───────────────────────────────────────────────────────────
$prop_id    = (int)$row['property_id'];
$pi_stmt    = $conn->prepare("
    SELECT image_path, is_cover
    FROM property_images
    WHERE property_id = ?
    ORDER BY is_cover DESC, id ASC
    LIMIT 1
");
$pi_stmt->bind_param('i', $prop_id);
$pi_stmt->execute();
$pi_row          = $pi_stmt->get_result()->fetch_assoc();
$property_cover  = $pi_row ? image_url($pi_row['image_path']) : null;

// ── Similar units (same property, also available) ─────────────────────────────
$sim_stmt = $conn->prepare("
    SELECT
        u.id,
        u.unit_number,
        COALESCE(ut.type_name, u.unit_type) AS resolved_type,
        u.room_count,
        u.size_sqft,
        u.rent_amount
    FROM units u
    LEFT JOIN unit_types ut ON u.unit_type_id = ut.id
    WHERE u.property_id = ? AND u.status = 'vacant' AND u.is_listed = 1 AND u.id != ?
    ORDER BY u.rent_amount ASC
    LIMIT 3
");
$sim_stmt->bind_param('ii', $prop_id, $id);
$sim_stmt->execute();
$sim_rows = $sim_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$similar = [];
foreach ($sim_rows as $sr) {
    // Cover image for similar unit
    $si = $conn->prepare("SELECT image_path FROM unit_images WHERE unit_id = ? ORDER BY is_cover DESC, id ASC LIMIT 1");
    $si->bind_param('i', $sr['id']);
    $si->execute();
    $si_row = $si->get_result()->fetch_assoc();

    $similar[] = [
        'id'          => (int)$sr['id'],
        'unit_number' => $sr['unit_number'],
        'unit_type'   => $sr['resolved_type'] ?: 'Unit',
        'room_count'  => $sr['room_count'] !== null ? (int)$sr['room_count'] : null,
        'size_sqft'   => $sr['size_sqft']  !== null ? (int)$sr['size_sqft']  : null,
        'rent_amount' => (float)$sr['rent_amount'],
        'cover_image' => $si_row ? image_url($si_row['image_path']) : null,
    ];
}

// ── Response ──────────────────────────────────────────────────────────────────
echo json_encode([
    'id'           => (int)$row['id'],
    'unit_number'  => $row['unit_number'],
    'unit_type'    => $row['resolved_type'] ?: 'Unit',
    'floor_number' => $row['floor_number'] !== null ? (int)$row['floor_number'] : null,
    'room_count'   => $row['room_count']   !== null ? (int)$row['room_count']   : null,
    'size_sqft'    => $row['size_sqft']    !== null ? (int)$row['size_sqft']    : null,
    'rent_amount'  => (float)$row['rent_amount'],
    'cover_image'  => $cover_image,
    'images'       => $images,
    'amenities'    => array_map(fn($a) => ['id' => (int)$a['id'], 'name' => $a['name']], $amenities),
    'property'     => [
        'id'          => $prop_id,
        'name'        => $row['property_name'],
        'address'     => $row['address'],
        'city'        => $row['city'],
        'region'      => $row['region'],
        'district'    => $row['district'],
        'type'        => $row['property_type'],
        'description' => $row['property_description'],
        'manager'     => $row['manager_name'],
        'cover_image' => $property_cover,
    ],
    'similar'      => $similar,
]);
