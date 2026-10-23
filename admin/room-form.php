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

$pageTitle = ($editing ? 'Edit room' : 'Add room') . ' – Admin';
$pageCss = ['css/forms.css'];
require '../includes/header.php';
?>

<main class="m3 wrap page">
    <?php require '../include/admin_nav.php'; ?>

    <p class="breadcrumb"><a href="rooms.php">Rooms</a> / <?= $editing ? 'Edit room' : 'Add a room' ?></p>
    <h1><?= $editing ? 'Edit room' : 'Add a room' ?></h1>
    <p class="lead"><?= $editing ? 'Change the details and save.' : 'New rooms appear on the listings page as soon as they are saved.' ?></p>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $err): ?>
                <p><?= e($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="card form admin-form" method="post" action="room-form.php<?= $editing ? '?id=' . $id : '' ?>">
        <div class="field-row">
            <div class="field">
                <label for="property_id">Property</label>
                <select id="property_id" name="property_id" required>
                    <option value="">Choose…</option>
                    <?php foreach ($properties as $pid => $pname): ?>
                        <option value="<?= $pid ?>" <?= $pid == $room['property_id'] ? 'selected' : '' ?>><?= e($pname) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="room_number">Room number</label>
                <input type="text" id="room_number" name="room_number" value="<?= e($room['room_number']) ?>" maxlength="20" required>
            </div>
        </div>

        <div class="field-row field-row-3">
            <div class="field">
                <label for="room_type">Type</label>
                <select id="room_type" name="room_type">
                    <option value="single" <?= $room['room_type'] === 'single' ? 'selected' : '' ?>>Single</option>
                    <option value="double" <?= $room['room_type'] === 'double' ? 'selected' : '' ?>>Double (shared)</option>
                </select>
            </div>
            <div class="field">
                <label for="price_per_month">Rent per month (N$)</label>
                <input type="number" id="price_per_month" name="price_per_month" value="<?= e($room['price_per_month']) ?>" min="0" step="0.01" required>
            </div>
            <div class="field">
                <label for="capacity">Capacity</label>
                <input type="number" id="capacity" name="capacity" value="<?= e($room['capacity']) ?>" min="1" max="10" required>
            </div>
        </div>

        <div class="check-row">
            <label class="check">
                <input type="checkbox" name="is_available" value="1" <?= $room['is_available'] ? 'checked' : '' ?>>
                Available for booking
            </label>
        </div>

        <div class="form-actions">
            <a class="link link-muted" href="rooms.php">Cancel</a>
            <button class="btn" type="submit"><?= $editing ? 'Save changes' : 'Add room' ?></button>
        </div>
    </form>
</main>

<?php require '../includes/footer.php'; ?>
