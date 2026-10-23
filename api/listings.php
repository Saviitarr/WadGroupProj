<?php
// api/listings.php - returns every property as JSON for the listings and details pages.
// js/data.js fetches this (CAMPUS_CONNECT_LISTINGS_ENDPOINT) and the existing JavaScript
// filters, sorts and displays it, so Member 2's front end works unchanged with real data.
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

// One row per property, with room statistics worked out by MySQL
$properties = $pdo->query(
    "SELECT p.id, p.name, p.address, p.area, p.distance_to_campus_km, p.description,
            p.has_wifi, p.has_parking,
            MIN(r.price_per_month)                    AS min_price,
            COUNT(r.id)                               AS room_count,
            COALESCE(SUM(r.is_available), 0)          AS rooms_available,
            COALESCE(SUM(r.capacity), 0)              AS beds,
            GROUP_CONCAT(DISTINCT r.room_type)        AS room_types
       FROM properties p
       LEFT JOIN rooms r ON r.property_id = p.id
      GROUP BY p.id
      ORDER BY p.distance_to_campus_km"
)->fetchAll();

// Photos for all properties in one query, grouped by property id
$images = [];
foreach ($pdo->query('SELECT property_id, image_url FROM property_images ORDER BY id') as $img) {
    $images[$img['property_id']][] = $img['image_url'];
}

// The room type of the cheapest room is shown on the card ("Single room" / "Double room")
$cheapestType = $pdo->prepare(
    'SELECT room_type FROM rooms WHERE property_id = ? ORDER BY price_per_month LIMIT 1'
);

$labels = ['single' => 'Single room', 'double' => 'Double room'];
$listings = [];

foreach ($properties as $p) {
    $types = array_filter(explode(',', (string) $p['room_types']));

    $cheapestType->execute([$p['id']]);
    $mainType = $cheapestType->fetchColumn() ?: 'single';

    $amenities = [];
    if ($p['has_wifi']) {
        $amenities[] = 'Wi-Fi';
    }
    if ($p['has_parking']) {
        $amenities[] = 'Parking';
    }
    foreach ($types as $t) {
        $amenities[] = ucfirst($t) . ' rooms';
    }

    $listings[] = [
        'id'             => (int) $p['id'],
        'name'           => $p['name'],
        'location'       => 'Windhoek',
        'area'           => $p['area'],
        'street'         => $p['address'],
        'type'           => $labels[$mainType] ?? 'Single room',
        'types'          => array_values(array_map(function ($t) use ($labels) { return $labels[$t] ?? $t; }, $types)),
        'price'          => (float) $p['min_price'],
        'deposit'        => 0,
        'distanceKm'     => (float) $p['distance_to_campus_km'],
        'availableFrom'  => '',
        'minimumStay'    => '1 month',
        'roomsAvailable' => (int) $p['rooms_available'],
        'bedrooms'       => (int) $p['room_count'],
        'guests'         => (int) $p['beds'],
        'status'         => 'active',
        'description'    => (string) $p['description'],
        'amenities'      => $amenities,
        'images'         => $images[$p['id']] ?? [],
    ];
}

echo json_encode($listings);
