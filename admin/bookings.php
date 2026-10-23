<?php
require '../include/auth.php';
require_admin();

// Approve / Reject buttons post here
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = (int) ($_POST['booking_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT b.id, b.room_id, b.start_date, b.end_date, b.status, r.capacity
         FROM bookings b
         JOIN rooms r ON r.id = b.room_id
         WHERE b.id = ?'
    );
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();

    // Only pending bookings can be decided, and only with a known action
    if (!$booking || $booking['status'] !== 'pending' || !in_array($action, ['approve', 'reject'], true)) {
        header('Location: bookings.php?msg=invalid');
        exit;
    }

    if ($action === 'approve') {
        // Same overlap check as book.php: other requests may have been approved since this one was made
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM bookings
             WHERE room_id = ? AND status = 'approved'
               AND start_date < ? AND end_date > ?"
        );
        $stmt->execute([$booking['room_id'], $booking['end_date'], $booking['start_date']]);

        if ($stmt->fetchColumn() >= $booking['capacity']) {
            header('Location: bookings.php?msg=full');
            exit;
        }
    }

    $new_status = $action === 'approve' ? 'approved' : 'rejected';
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ? AND status = 'pending'");
    $stmt->execute([$new_status, $booking_id]);

    header('Location: bookings.php?msg=' . $new_status);
    exit;
}

// Messages are picked from this list, so nothing from the URL is ever printed
$notices = [
    'approved' => ['success', 'Booking approved.'],
    'rejected' => ['success', 'Booking rejected.'],
    'full' => ['error', 'Cannot approve: the room is already full for those dates.'],
    'invalid' => ['error', 'That booking can no longer be changed.'],
];
$notice = $notices[$_GET['msg'] ?? ''] ?? null;

// Pending first (status = 'pending' is 1 for pending rows, 0 for the rest), newest first after that
$bookings = $pdo->query(
    "SELECT b.id, b.start_date, b.end_date, b.status, b.notes, b.created_at,
            u.full_name, u.student_number, u.email,
            r.room_number, p.name AS property_name
     FROM bookings b
     JOIN users u ON u.id = b.user_id
     JOIN rooms r ON r.id = b.room_id
     JOIN properties p ON p.id = r.property_id
     ORDER BY b.status = 'pending' DESC, b.created_at DESC"
)->fetchAll();

$pageTitle = 'Bookings – Admin';
$pageCss = ['css/forms.css'];
require '../includes/header.php';
?>

<main class="m3 wrap page">
    <?php require '../include/admin_nav.php'; ?>

    <h1>Bookings</h1>
    <p class="lead">Approve or reject booking requests. Pending requests are listed first.</p>

    <?php if ($notice): ?>
        <p class="alert alert-<?= $notice[0] ?>"><?= e($notice[1]) ?></p>
    <?php endif; ?>

    <div class="card">
        <?php if (!$bookings): ?>
            <p class="empty muted">No bookings yet.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Room</th>
                        <th>Move in</th>
                        <th>Move out</th>
                        <th>Status</th>
                        <th><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>
                                <strong><?= e($b['full_name']) ?></strong>
                                <span class="muted block"><?= e($b['student_number']) ?> · <?= e($b['email']) ?></span>
                                <?php if ($b['notes']): ?>
                                    <span class="note block">“<?= e($b['notes']) ?>”</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Room"><?= e($b['property_name']) ?> – Room <?= e($b['room_number']) ?></td>
                            <td data-label="Move in"><?= e($b['start_date']) ?></td>
                            <td data-label="Move out"><?= e($b['end_date']) ?></td>
                            <td data-label="Status"><span class="badge badge-<?= e($b['status']) ?>"><?= e(ucfirst($b['status'])) ?></span></td>
                            <td class="actions">
                                <?php if ($b['status'] === 'pending'): ?>
                                    <form class="inline-form" method="post" action="bookings.php">
                                        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                        <button class="btn btn-small" type="submit" name="action" value="approve">Approve</button>
                                        <button class="btn btn-small btn-ghost" type="submit" name="action" value="reject">Reject</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<?php require '../includes/footer.php'; ?>
