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
    'added' => 'Room added.',
    'updated' => 'Room updated.',
    'deleted' => 'Room deleted.',
    'has_bookings' => 'This room has bookings, so it can\'t be deleted. Edit it and untick "Available" instead.',
];
$notice = $notices[$_GET['msg'] ?? ''] ?? null;

$rooms = $pdo->query(
    'SELECT r.id, r.room_number, r.room_type, r.capacity, r.price_per_month, r.is_available,
            p.name AS property_name
     FROM rooms r
     JOIN properties p ON p.id = r.property_id
     ORDER BY p.name, r.room_number'
)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rooms – Admin – Campus Connect</title>
</head>

<body>
    <?php require '../include/admin_nav.php'; ?>

    <main>
        <h1>Rooms</h1>
        <p><a href="room-form.php">+ Add a room</a></p>

        <?php if ($notice): ?>
            <p class="alert"><?= e($notice) ?></p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Property</th>
                    <th>Room</th>
                    <th>Type</th>
                    <th>Capacity</th>
                    <th>Rent / month</th>
                    <th>Available</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rooms as $r): ?>
                    <tr>
                        <td><?= e($r['property_name']) ?></td>
                        <td><?= e($r['room_number']) ?></td>
                        <td><?= e(ucfirst($r['room_type'])) ?></td>
                        <td><?= $r['capacity'] ?></td>
                        <td>N$<?= number_format($r['price_per_month'], 2) ?></td>
                        <td><?= $r['is_available'] ? 'Yes' : 'No' ?></td>
                        <td>
                            <a href="room-form.php?id=<?= $r['id'] ?>">Edit</a>
                            <form method="post" action="rooms.php"
                                onsubmit="return confirm('Delete this room?');">
                                <input type="hidden" name="room_id" value="<?= $r['id'] ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>

</html>