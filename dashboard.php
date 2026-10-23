<?php
// dashboard.php - the student's own page: profile summary + my bookings (Member 3)
require_once __DIR__ . '/includes/helpers.php';

require_login();

$user   = current_user($pdo);
$userId = (int) $_SESSION['user_id'];

if (!$user) {
    // The session points at a user that no longer exists
    session_destroy();
    header('Location: login.php');
    exit;
}

// All of this student's bookings, newest first, with the room and property details
$stmt = $pdo->prepare(
    'SELECT b.id, b.start_date, b.end_date, b.status, b.notes, b.created_at,
            r.room_number, r.room_type, r.price_per_month,
            p.id AS property_id, p.name AS property_name, p.area
       FROM bookings b
       JOIN rooms r      ON r.id = b.room_id
       JOIN properties p ON p.id = r.property_id
      WHERE b.user_id = ?
      ORDER BY b.created_at DESC, b.id DESC'
);
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

// Count bookings per status for the summary cards
$counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'cancelled' => 0];
foreach ($bookings as $b) {
    $counts[$b['status']]++;
}

// The next approved stay that has not ended yet (for the "Your next stay" card)
$today = date('Y-m-d');
$nextStay = null;
foreach ($bookings as $b) {
    if ($b['status'] === 'approved' && $b['end_date'] >= $today) {
        if (!$nextStay || $b['start_date'] < $nextStay['start_date']) {
            $nextStay = $b;
        }
    }
}

// What a student may do with a booking, depending on its status
function can_edit($b)
{
    return $b['status'] === 'pending';
}
function can_cancel($b)
{
    // Pending requests any time; approved ones only before move-in
    return $b['status'] === 'pending'
        || ($b['status'] === 'approved' && $b['start_date'] > date('Y-m-d'));
}
function can_delete($b)
{
    // Only finished-with bookings can be removed from the history
    return in_array($b['status'], ['rejected', 'cancelled'], true);
}

$statusLabels = [
    'pending'   => 'Pending',
    'approved'  => 'Approved',
    'rejected'  => 'Declined',
    'cancelled' => 'Cancelled',
];

$highlight = (int) ($_GET['highlight'] ?? 0);
$isAdmin = $user['role'] === 'admin';
$firstName = explode(' ', trim($user['full_name']))[0];

$pageTitle = 'My bookings';
$pageCss   = ['css/forms.css'];   // styles for the Member 3 pages
require __DIR__ . '/includes/header.php';   // Member 1's shared header
?>
<main class="m3 wrap page">
<?php show_flash(); ?>

<div class="dash-head">
    <div>
        <h1>Hi, <?= e($firstName) ?></h1>
        <p class="lead">Here are your booking requests and their status.</p>
    </div>
    <?php if (!$isAdmin): ?>
        <a class="btn" href="<?= e($listingsUrl) ?>">+ New booking</a>
    <?php else: ?>
        <a class="btn" href="admin/index.php">Go to admin area</a>
    <?php endif; ?>
</div>

<div class="dash-layout">
    <section class="dash-main" aria-label="Bookings">

        <ul class="stat-grid">
            <li class="stat"><span class="stat-num"><?= count($bookings) ?></span><span>Total requests</span></li>
            <li class="stat stat-pending"><span class="stat-num"><?= $counts['pending'] ?></span><span>Pending</span></li>
            <li class="stat stat-approved"><span class="stat-num"><?= $counts['approved'] ?></span><span>Approved</span></li>
            <li class="stat stat-closed"><span class="stat-num"><?= $counts['rejected'] + $counts['cancelled'] ?></span><span>Declined or cancelled</span></li>
        </ul>

        <?php if ($nextStay): ?>
            <article class="card next-stay">
                <p class="eyebrow">Your next stay</p>
                <h2><?= e($nextStay['property_name']) ?> · Room <?= e($nextStay['room_number']) ?></h2>
                <p><?= nice_date($nextStay['start_date']) ?> – <?= nice_date($nextStay['end_date']) ?> · <?= e($nextStay['area']) ?></p>
                <?php
                $daysToGo = (int) floor((strtotime($nextStay['start_date']) - strtotime($today)) / 86400);
                ?>
                <p class="next-stay-count"><?= $daysToGo > 0 ? 'Move-in in ' . $daysToGo . ' day' . ($daysToGo === 1 ? '' : 's') : 'You are currently staying here' ?></p>
            </article>
        <?php endif; ?>

        <div class="card">
            <div class="table-head">
                <h2>My bookings</h2>
                <div class="tabs" role="group" aria-label="Filter bookings by status">
                    <button type="button" class="tab" data-filter="all" aria-pressed="true">All</button>
                    <button type="button" class="tab" data-filter="pending" aria-pressed="false">Pending</button>
                    <button type="button" class="tab" data-filter="approved" aria-pressed="false">Approved</button>
                    <button type="button" class="tab" data-filter="closed" aria-pressed="false">Closed</button>
                </div>
            </div>

            <?php if (!$bookings): ?>
                <div class="empty">
                    <p><strong>No bookings yet.</strong></p>
                    <p class="muted">Find a room you like and send a request. It will show up here.</p>
                    <?php if (!$isAdmin): ?><a class="btn" href="<?= e($listingsUrl) ?>">Browse rooms</a><?php endif; ?>
                </div>
            <?php else: ?>
                <table class="bookings-table">
                    <caption class="sr-only">Your booking requests</caption>
                    <thead>
                    <tr>
                        <th scope="col">Place</th>
                        <th scope="col">Dates</th>
                        <th scope="col">Rent</th>
                        <th scope="col">Status</th>
                        <th scope="col"><span class="sr-only">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr data-status="<?= e($b['status']) ?>"<?= (int) $b['id'] === $highlight ? ' class="highlight"' : '' ?>>
                            <td data-label="Place">
                                <strong><?= e($b['property_name']) ?></strong>
                                <span class="muted block">Room <?= e($b['room_number']) ?> · <?= e(ucfirst($b['room_type'])) ?> · #<?= e($b['id']) ?></span>
                                <?php if ($b['notes']): ?>
                                    <span class="note block">“<?= e($b['notes']) ?>”</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Dates"><?= nice_date($b['start_date']) ?> – <?= nice_date($b['end_date']) ?></td>
                            <td data-label="Rent"><?= money($b['price_per_month']) ?>/mo</td>
                            <td data-label="Status"><span class="badge badge-<?= e($b['status']) ?>"><?= e($statusLabels[$b['status']]) ?></span></td>
                            <td class="actions">
                                <?php if (can_edit($b)): ?>
                                    <a class="link" href="book.php?edit=<?= e($b['id']) ?>">Edit</a>
                                <?php endif; ?>

                                <?php if (can_cancel($b)): ?>
                                    <form method="post" action="booking-action.php" class="inline-form"
                                          data-confirm="Cancel your booking at <?= e($b['property_name']) ?>?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="booking_id" value="<?= e($b['id']) ?>">
                                        <button type="submit" class="link link-danger">Cancel</button>
                                    </form>
                                <?php endif; ?>

                                <?php if (can_delete($b)): ?>
                                    <form method="post" action="booking-action.php" class="inline-form"
                                          data-confirm="Remove this booking from your history? This cannot be undone.">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="booking_id" value="<?= e($b['id']) ?>">
                                        <button type="submit" class="link link-muted">Remove</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="empty-filter muted" id="emptyFilter" hidden>No bookings with this status.</p>
            <?php endif; ?>
        </div>
    </section>

    <aside class="card profile" aria-labelledby="profile-title">
        <div class="profile-top">
            <span class="avatar avatar-lg" aria-hidden="true"><?= e(strtoupper(substr($user['full_name'], 0, 1))) ?></span>
            <div>
                <h2 id="profile-title"><?= e($user['full_name']) ?></h2>
                <span class="badge badge-role"><?= e(ucfirst($user['role'])) ?></span>
            </div>
        </div>
        <dl class="profile-list">
            <?php if ($user['student_number']): ?>
                <div><dt>Student number</dt><dd><?= e($user['student_number']) ?></dd></div>
            <?php endif; ?>
            <div><dt>Email</dt><dd><?= e($user['email']) ?></dd></div>
            <div><dt>Phone</dt><dd><?= e($user['phone'] ?: 'Not added') ?></dd></div>
            <div><dt>Member since</dt><dd><?= nice_date($user['created_at']) ?></dd></div>
        </dl>
        <hr>
        <h3>How booking works</h3>
        <ol class="steps">
            <li>You send a request for a room and dates.</li>
            <li>An admin approves or declines it.</li>
            <li>You can edit or cancel while it is pending, and cancel an approved stay before move-in.</li>
        </ol>
        <p class="muted small">Questions? <a class="link" href="contact.php">Contact us</a>.</p>
    </aside>
</div>

<script src="js/dashboard.js"></script>
</main>

<?php require __DIR__ . '/includes/footer.php';   // Member 1's shared footer ?>
