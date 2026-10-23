<?php
require 'include/auth.php';
require_login();

// Admins have their own area
if ($_SESSION['role'] === 'admin') {
    header('Location: admin/index.php');
    exit;
}

// Cancel a booking (the Cancel buttons post here)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = (int) ($_POST['booking_id'] ?? 0);

    // "AND user_id = ?" means a student can only cancel THEIR OWN bookings,
    // even if they change the hidden booking_id in the browser.
    $stmt = $pdo->prepare(
        "UPDATE bookings SET status = 'cancelled'
         WHERE id = ? AND user_id = ? AND status IN ('pending', 'approved')"
    );
    $stmt->execute([$booking_id, $_SESSION['user_id']]);

    // rowCount() = how many rows the UPDATE changed: 1 if it worked, 0 if not theirs / already cancelled
    header('Location: dashboard.php?' . ($stmt->rowCount() ? 'cancelled=1' : 'error=1'));
    exit;
}

// This student's bookings, newest first, with the room and property details
$stmt = $pdo->prepare(
    'SELECT b.id, b.start_date, b.end_date, b.status, b.notes, b.created_at,
            r.id AS room_id, r.room_number, r.price_per_month, p.name AS property_name
     FROM bookings b
     JOIN rooms r ON r.id = b.room_id
     JOIN properties p ON p.id = r.property_id
     WHERE b.user_id = ?
     ORDER BY b.created_at DESC'
);
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My bookings – Campus Connect</title>
</head>

<body>
    <header>
        <nav>
            <a href="listingandshi/listings.php">Browse rooms</a>
            <a href="logout.php">Log out</a>
        </nav>
    </header>

    <main>
        <h1>Welcome, <?= e($_SESSION['name']) ?></h1>

        <?php if (isset($_GET['booked'])): ?>
            <p class="alert alert-success">Booking request sent – it's pending until an admin approves it.</p>
        <?php endif; ?>
        <?php if (isset($_GET['cancelled'])): ?>
            <p class="alert alert-success">Booking cancelled.</p>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <p class="alert alert-error">That booking couldn't be cancelled.</p>
        <?php endif; ?>

        <h2>My bookings</h2>

        <?php if (!$bookings): ?>
            <p>You have no bookings yet. <a href="listingandshi/listings.php">Browse rooms</a></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Move in</th>
                        <th>Move out</th>
                        <th>Rent / month</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>
                                <a href="listingandshi/listing-details.php?id=<?= $b['room_id'] ?>">
                                    <?= e($b['property_name']) ?> – Room <?= e($b['room_number']) ?>
                                </a>
                            </td>
                            <td><?= e($b['start_date']) ?></td>
                            <td><?= e($b['end_date']) ?></td>
                            <td>N$<?= number_format($b['price_per_month'], 2) ?></td>
                            <td><span class="status status-<?= e($b['status']) ?>"><?= e(ucfirst($b['status'])) ?></span></td>
                            <td>
                                <?php if ($b['status'] === 'pending' || $b['status'] === 'approved'): ?>
                                    <form method="post" action="dashboard.php"
                                        onsubmit="return confirm('Cancel this booking?');">
                                        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                        <button type="submit">Cancel</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>

</html>