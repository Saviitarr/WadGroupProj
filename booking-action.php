<?php
// booking-action.php - handles the Cancel and Remove buttons on the dashboard (Member 3)
// Only accepts POST, only acts on the logged-in student's own bookings, then redirects back.
require_once __DIR__ . '/includes/helpers.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_ok()) {
    flash('error', 'That action could not be completed. Please try again.');
    header('Location: dashboard.php');
    exit;
}

$userId    = (int) $_SESSION['user_id'];
$bookingId = (int) ($_POST['booking_id'] ?? 0);
$action    = $_POST['action'] ?? '';

if ($action === 'cancel') {
    // UPDATE: pending any time, approved only before move-in. The WHERE clause enforces both rules
    // and ownership, so a forged request for someone else's booking simply changes 0 rows.
    $stmt = $pdo->prepare(
        "UPDATE bookings SET status = 'cancelled'
          WHERE id = ? AND user_id = ?
            AND (status = 'pending' OR (status = 'approved' AND start_date > CURDATE()))"
    );
    $stmt->execute([$bookingId, $userId]);

    if ($stmt->rowCount() === 1) {
        flash('success', 'Booking #' . $bookingId . ' was cancelled.');
    } else {
        flash('error', 'That booking can no longer be cancelled.');
    }
} elseif ($action === 'delete') {
    // DELETE: only declined or cancelled bookings can be removed from the history
    $stmt = $pdo->prepare(
        "DELETE FROM bookings
          WHERE id = ? AND user_id = ? AND status IN ('rejected', 'cancelled')"
    );
    $stmt->execute([$bookingId, $userId]);

    if ($stmt->rowCount() === 1) {
        flash('success', 'Booking #' . $bookingId . ' was removed from your history.');
    } else {
        flash('error', 'Only declined or cancelled bookings can be removed.');
    }
} else {
    flash('error', 'Unknown action.');
}

header('Location: dashboard.php');
exit;
