<?php
require '../include/auth.php';
require_admin();

// room-form.php        → add a new room
// room-form.php?id=3   → edit room 3
$id = (int) ($_GET['id'] ?? 0);
$editing = $id > 0;

// For the property dropdown (and to check the chosen property really exists)
$properties = $pdo->query('SELECT id, name FROM properties ORDER BY name')->fetchAll(PDO::FETCH_KEY_PAIR);

$errors = [];
$room = [
    'property_id' => '',
    'room_number' => '',
    'room_type' => 'single',
    'capacity' => 1,
    'price_per_month' => '',
    'is_available' => 1,
];

if ($editing) {
    $stmt = $pdo->prepare('SELECT property_id, room_number, room_type, capacity, price_per_month, is_available FROM rooms WHERE id = ?');
    $stmt->execute([$id]);
    $room = $stmt->fetch();

    if (!$room) {
        http_response_code(404);
        exit('Room not found.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room = [
        'property_id' => (int) ($_POST['property_id'] ?? 0),
        'room_number' => trim($_POST['room_number'] ?? ''),
        'room_type' => $_POST['room_type'] ?? '',
        'capacity' => (int) ($_POST['capacity'] ?? 0),
        'price_per_month' => trim($_POST['price_per_month'] ?? ''),
        'is_available' => isset($_POST['is_available']) ? 1 : 0,   // unticked checkboxes send nothing
    ];

    if (!isset($properties[$room['property_id']])) {
        $errors[] = 'Please choose a property.';
    }
    if ($room['room_number'] === '' || strlen($room['room_number']) > 20) {
        $errors[] = 'Room number is required (max 20 characters).';
    }
    if (!in_array($room['room_type'], ['single', 'double'], true)) {
        $errors[] = 'Please choose a room type.';
    }
    if ($room['capacity'] < 1 || $room['capacity'] > 10) {
        $errors[] = 'Capacity must be between 1 and 10.';
    }
    if (!is_numeric($room['price_per_month']) || $room['price_per_month'] < 0) {
        $errors[] = 'Rent must be a number of 0 or more.';
    }

    // Room numbers must be unique inside a property (matches UNIQUE(property_id, room_number))
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM rooms WHERE property_id = ? AND room_number = ? AND id != ?');
        $stmt->execute([$room['property_id'], $room['room_number'], $id]);
        if ($stmt->fetch()) {
            $errors[] = 'That property already has a room with this number.';
        }
    }

    if (!$errors) {
        $values = [
            $room['property_id'],
            $room['room_number'],
            $room['room_type'],
            $room['capacity'],
            $room['price_per_month'],
            $room['is_available'],
        ];

        if ($editing) {
            $values[] = $id;
            $stmt = $pdo->prepare(
                'UPDATE rooms
                 SET property_id = ?, room_number = ?, room_type = ?, capacity = ?, price_per_month = ?, is_available = ?
                 WHERE id = ?'
            );
            $stmt->execute($values);
            $msg = 'updated';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO rooms (property_id, room_number, room_type, capacity, price_per_month, is_available)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute($values);
            $msg = 'added';
        }

        header('Location: rooms.php?msg=' . $msg);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $editing ? 'Edit room' : 'Add room' ?> – Admin – Campus Connect</title>
</head>

<body>
    <?php require '../include/admin_nav.php'; ?>

    <main>
        <h1><?= $editing ? 'Edit room' : 'Add a room' ?></h1>

        <?php foreach ($errors as $err): ?>
            <p class="alert alert-error"><?= e($err) ?></p>
        <?php endforeach; ?>

        <form method="post" action="room-form.php<?= $editing ? '?id=' . $id : '' ?>">
            <label>Property
                <select name="property_id" required>
                    <option value="">Choose…</option>
                    <?php foreach ($properties as $pid => $pname): ?>
                        <option value="<?= $pid ?>" <?= $pid == $room['property_id'] ? 'selected' : '' ?>><?= e($pname) ?></option>
                    <?php endforeach; ?>
                </select>
            </label><br>

            <label>Room number
                <input type="text" name="room_number" value="<?= e($room['room_number']) ?>" maxlength="20" required>
            </label><br>

            <label>Type
                <select name="room_type">
                    <option value="single" <?= $room['room_type'] === 'single' ? 'selected' : '' ?>>Single</option>
                    <option value="double" <?= $room['room_type'] === 'double' ? 'selected' : '' ?>>Double (shared)</option>
                </select>
            </label><br>

            <label>Capacity
                <input type="number" name="capacity" value="<?= e($room['capacity']) ?>" min="1" max="10" required>
            </label><br>

            <label>Rent per month (N$)
                <input type="number" name="price_per_month" value="<?= e($room['price_per_month']) ?>" min="0" step="0.01" required>
            </label><br>

            <label>
                <input type="checkbox" name="is_available" value="1" <?= $room['is_available'] ? 'checked' : '' ?>>
                Available for booking
            </label><br>

            <button type="submit"><?= $editing ? 'Save changes' : 'Add room' ?></button>
            <a href="rooms.php">Cancel</a>
        </form>
    </main>
</body>

</html>
