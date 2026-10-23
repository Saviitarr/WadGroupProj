<?php
require 'include/auth.php';

// True only for a real date in YYYY-MM-DD format.
// createFromFormat would turn '2027-02-31' into 3 March, so we format it back
// and check we got the same text - that catches impossible dates.
function valid_date($value) {
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return $d && $d->format('Y-m-d') === $value;
}

require_login();

// The room id comes from the URL: book.php?listing_id=4
$room_id = (int) ($_GET['listing_id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT r.id, r.room_number, r.room_type, r.capacity, r.price_per_month, r.is_available, p.name
     FROM rooms r
     JOIN properties p ON p.id = r.property_id
     WHERE r.id = ?'
);
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    http_response_code(404);
    exit('Room not found.');
}

$errors = [];
$old = ['start_date' => '', 'end_date' => '', 'notes' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    $old = compact('start_date', 'end_date', 'notes');

    // A. Real dates?
    if (!valid_date($start_date) || !valid_date($end_date)) {
        $errors[] = 'Please choose valid dates.';
    }

    // B. Right order? ('YYYY-MM-DD' strings compare correctly with < and >)
    if (!$errors) {
        if ($start_date < date('Y-m-d')) {
            $errors[] = "Move-in date can't be in the past.";
        }
        if ($end_date <= $start_date) {
            $errors[] = 'Move-out date must be after move-in date.';
        }
    }

    // C. Has the admin switched this room off?
    if (!$room['is_available']) {
        $errors[] = 'This room is not taking bookings right now.';
    }

    // D. Is the room already full for those dates?
    // Two ranges overlap when each starts before the other ends.
    if (!$errors) {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM bookings
             WHERE room_id = ? AND status = 'approved'
               AND start_date < ? AND end_date > ?"
        );
        $stmt->execute([$room_id, $end_date, $start_date]);
        $taken = $stmt->fetchColumn();

        if ($taken >= $room['capacity']) {
            $errors[] = 'Sorry, this room is fully booked for those dates.';
        }
    }

    // E. Does this student already have a booking (pending or approved) for those dates?
    if (!$errors) {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM bookings
             WHERE user_id = ? AND status IN ('pending', 'approved')
               AND start_date < ? AND end_date > ?"
        );
        $stmt->execute([$_SESSION['user_id'], $end_date, $start_date]);

        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'You already have a booking for those dates.';
        }
    }

    if (!$errors) {
        // status is left out on purpose: the DB default makes it 'pending'
        $stmt = $pdo->prepare(
            'INSERT INTO bookings (user_id, room_id, start_date, end_date, notes)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $_SESSION['user_id'],
            $room_id,
            $start_date,
            $end_date,
            $notes !== '' ? $notes : null,
        ]);

        header('Location: dashboard.php?booked=1');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Request to book – Campus Connect</title>
</head>

<body>
    <main>
        <h1><?= e($room['name']) ?> – Room <?= e($room['room_number']) ?></h1>
        <p>
            <?= $room['room_type'] === 'single' ? 'Single room' : 'Shared room' ?>
            · N$<?= number_format($room['price_per_month'], 2) ?> per month
        </p>

        <?php foreach ($errors as $err): ?>
            <p class="alert alert-error"><?= e($err) ?></p>
        <?php endforeach; ?>

        <form method="post" action="book.php?listing_id=<?= $room_id ?>">
            <label>Move-in date
                <input type="date" name="start_date" value="<?= e($old['start_date']) ?>" min="<?= date('Y-m-d') ?>" required>
            </label><br>
            <label>Move-out date
                <input type="date" name="end_date" value="<?= e($old['end_date']) ?>" min="<?= date('Y-m-d') ?>" required>
            </label><br>
            <label>Notes (optional)
                <textarea name="notes"><?= e($old['notes']) ?></textarea>
            </label><br>
            <button type="submit">Send booking request</button>
        </form>
        <p>Your request stays <strong>pending</strong> until an admin approves it.</p>
        <p><a href="listingandshi/listing-details.php?id=<?= $room_id ?>">Back to the room</a></p>
    </main>
</body>

</html>