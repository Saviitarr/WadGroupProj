<?php
require '../include/auth.php';
require_admin();

// No user input in these queries, so plain query() is fine (nothing to inject)
$counts = [
    'Students' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn(),
    'Rooms' => $pdo->query('SELECT COUNT(*) FROM rooms')->fetchColumn(),
    'Pending bookings' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
    'Messages' => $pdo->query('SELECT COUNT(*) FROM contact')->fetchColumn(),
];

$pageTitle = 'Admin';
$pageCss = ['css/forms.css'];
require '../includes/header.php';
?>

<main class="m3 wrap page">
    <?php require '../include/admin_nav.php'; ?>

    <h1>Welcome, <?= e($_SESSION['name']) ?></h1>
    <p class="lead">Here's what's happening on Campus Connect.</p>

    <ul class="stat-grid">
        <?php foreach ($counts as $label => $number): ?>
            <li class="stat<?= $label === 'Pending bookings' ? ' stat-pending' : '' ?>">
                <span class="stat-num"><?= $number ?></span>
                <?= e($label) ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="card" style="margin-top: 20px">
        <?php if ($counts['Pending bookings'] > 0): ?>
            <h2><?= $counts['Pending bookings'] ?> booking request(s) waiting</h2>
            <p class="muted">Approve or reject them so students know where they stand.</p>
            <a class="btn" href="bookings.php">Review bookings</a>
        <?php else: ?>
            <h2>All caught up</h2>
            <p class="muted">There are no pending booking requests.</p>
            <a class="btn btn-ghost" href="rooms.php">Manage rooms</a>
        <?php endif; ?>
    </div>
</main>

<?php require '../includes/footer.php'; ?>
