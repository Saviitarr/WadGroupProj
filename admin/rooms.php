<?php
require '../include/auth.php';
require_admin();

// Delete buttons post here
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = (int) ($_POST['room_id'] ?? 0);

    try {
        $stmt = $pdo->prepare('DELETE FROM rooms WHERE id = ?');
        $stmt->execute([$room_id]);
        $msg = 'deleted';
    } catch (PDOException $ex) {
        // 23000 = a constraint said no. Here: bookings.room_id is ON DELETE RESTRICT,
        // so a room with booking history can't be deleted.
        if ($ex->getCode() !== '23000') {
            throw $ex;
        }
        $msg = 'has_bookings';
    }

    header('Location: rooms.php?msg=' . $msg);
    exit;
}

$notices = [
    'added' => ['success', 'Room added.'],
    'updated' => ['success', 'Room updated.'],
    'deleted' => ['success', 'Room deleted.'],
    'has_bookings' => ['error', 'This room has bookings, so it can\'t be deleted. Edit it and untick "Available" instead.'],
];
$notice = $notices[$_GET['msg'] ?? ''] ?? null;

$rooms = $pdo->query(
    'SELECT r.id, r.room_number, r.room_type, r.capacity, r.price_per_month, r.is_available,
            p.name AS property_name
     FROM rooms r
     JOIN properties p ON p.id = r.property_id
     ORDER BY p.name, r.room_number'
)->fetchAll();

$pageTitle = 'Rooms – Admin';
$pageCss = ['css/forms.css'];
require '../includes/header.php';
?>

<main class="m3 wrap page">
    <?php require '../include/admin_nav.php'; ?>

    <div class="dash-head">
        <div>
            <h1>Rooms</h1>
            <p class="lead">Add, edit or remove rooms. Changes show on the listings straight away.</p>
        </div>
        <a class="btn" href="room-form.php">+ Add a room</a>
    </div>

    <?php if ($notice): ?>
        <p class="alert alert-<?= $notice[0] ?>"><?= e($notice[1]) ?></p>
    <?php endif; ?>

    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Room</th>
                    <th>Type</th>
                    <th>Capacity</th>
                    <th>Rent / month</th>
                    <th>Available</th>
                    <th><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rooms as $r): ?>
                    <tr>
                        <td>
                            <strong><?= e($r['property_name']) ?></strong>
                            <span class="muted block">Room <?= e($r['room_number']) ?></span>
                        </td>
                        <td data-label="Type"><?= e(ucfirst($r['room_type'])) ?></td>
                        <td data-label="Capacity"><?= $r['capacity'] ?></td>
                        <td data-label="Rent / month">N$<?= number_format($r['price_per_month'], 2) ?></td>
                        <td data-label="Available">
                            <span class="badge badge-<?= $r['is_available'] ? 'approved' : 'cancelled' ?>"><?= $r['is_available'] ? 'Yes' : 'No' ?></span>
                        </td>
                        <td class="actions">
                            <a class="btn btn-small btn-ghost" href="room-form.php?id=<?= $r['id'] ?>">Edit</a>
                            <form class="inline-form" method="post" action="rooms.php"
                                onsubmit="return confirm('Delete this room?');">
                                <input type="hidden" name="room_id" value="<?= $r['id'] ?>">
                                <button class="btn btn-small" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require '../includes/footer.php'; ?>
