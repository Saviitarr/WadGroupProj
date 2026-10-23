<?php
require '../include/auth.php';
require_admin();

// No user input in these queries, so plain query() is fine (nothing to inject)
$counts = [
    'Students' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn(),
    'Properties' => $pdo->query('SELECT COUNT(*) FROM properties')->fetchColumn(),
    'Rooms' => $pdo->query('SELECT COUNT(*) FROM rooms')->fetchColumn(),
    'Pending bookings' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
    'Messages' => $pdo->query('SELECT COUNT(*) FROM contact')->fetchColumn(),
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin – Campus Connect</title>
</head>

<body>
    <?php require '../include/admin_nav.php'; ?>

    <main>
        <h1>Welcome, <?= e($_SESSION['name']) ?></h1>

        <ul class="admin-stats">
            <?php foreach ($counts as $label => $number): ?>
                <li><strong><?= $number ?></strong> <?= e($label) ?></li>
            <?php endforeach; ?>
        </ul>

        <?php if ($counts['Pending bookings'] > 0): ?>
            <p><a href="bookings.php">Review <?= $counts['Pending bookings'] ?> pending booking(s)</a></p>
        <?php endif; ?>
    </main>
</body>

</html>