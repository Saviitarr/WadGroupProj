<?php
// Returns rooms as JSON for the listings and details pages.
// Optional filters in the URL: id, search, area, minPrice, maxPrice, types, available
require __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$where = [];    // pieces of the WHERE clause, e.g. "p.area = ?"
$params = [];   // the values for each ?, in the same order

// Details page asks for one room
if (isset($_GET['id'])) {
    $where[] = 'r.id = ?';
    $params[] = (int) $_GET['id'];
}

$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $where[] = '(p.name LIKE ? OR p.address LIKE ? OR p.area LIKE ?)';
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like);
}

$area = trim($_GET['area'] ?? '');
if ($area !== '') {
    $where[] = 'p.area = ?';
    $params[] = $area;
}

if (is_numeric($_GET['minPrice'] ?? '')) {
    $where[] = 'r.price_per_month >= ?';
    $params[] = $_GET['minPrice'];
}

if (is_numeric($_GET['maxPrice'] ?? '')) {
    $where[] = 'r.price_per_month <= ?';
    $params[] = $_GET['maxPrice'];
}

// The page's checkbox labels -> our ENUM values
$typeMap = ['Single room' => 'single', 'Shared room' => 'double'];
if (isset($_GET['types'])) {
    $types = [];
    foreach (explode(',', $_GET['types']) as $label) {
        if (isset($typeMap[$label])) {
            $types[] = $typeMap[$label];
        }
    }
    if (!$types) {              // every box unticked -> nothing can match
        echo json_encode([]);
        exit;
    }
    // One ? per type, e.g. "IN (?, ?)" - only question marks go into the SQL, never the values
    $where[] = 'r.room_type IN (' . implode(', ', array_fill(0, count($types), '?')) . ')';
    $params = array_merge($params, $types);
}

if (($_GET['available'] ?? '') === '1') {
    $where[] = 'r.is_available = 1';
}

$sql = 'SELECT r.id, r.room_number, r.room_type, r.capacity, r.price_per_month, r.is_available,
               p.name, p.address, p.area, p.distance_to_campus_km, p.description, p.has_wifi, p.has_parking,
               (SELECT GROUP_CONCAT(image_url) FROM property_images WHERE property_id = p.id) AS images
        FROM rooms r
        JOIN properties p ON p.id = r.property_id';

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY p.distance_to_campus_km, p.name, r.room_number';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

// Reshape each DB row into the fields the teammate's JavaScript expects
$listings = [];
foreach ($stmt->fetchAll() as $row) {
    $amenities = [];
    if ($row['has_wifi']) {
        $amenities[] = 'Wi-Fi';
    }
    if ($row['has_parking']) {
        $amenities[] = 'Parking';
    }

    $listings[] = [
        'id' => (int) $row['id'],
        'name' => $row['name'] . ' – Room ' . $row['room_number'],
        'location' => 'Windhoek',
        'area' => $row['area'],
        'street' => $row['address'],
        'type' => $row['room_type'] === 'single' ? 'Single room' : 'Shared room',
        'price' => (float) $row['price_per_month'],
        'deposit' => (float) $row['price_per_month'],   // no deposit column: one month's rent
        'distanceKm' => (float) $row['distance_to_campus_km'],
        'roomsAvailable' => (int) $row['is_available'],
        'bedrooms' => 1,
        'guests' => (int) $row['capacity'],
        'status' => 'active',
        'description' => $row['description'] ?? '',
        'amenities' => $amenities,
        'images' => $row['images'] ? explode(',', $row['images']) : [],
    ];
}

if (isset($_GET['id']) && !$listings) {
    http_response_code(404);
    echo json_encode(['error' => 'Listing not found']);
    exit;
}

echo json_encode($listings);