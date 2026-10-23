<?php
require '../include/auth.php';
require_admin();

$messages = $pdo->query('SELECT * FROM contact ORDER BY created_at DESC')->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Messages – Admin – Campus Connect</title>
</head>

<body>
    <?php require '../include/admin_nav.php'; ?>

    <main>
        <h1>Messages</h1>

        <?php if (!$messages): ?>
            <p>No messages yet.</p>
        <?php endif; ?>

        <?php foreach ($messages as $m): ?>
            <article class="message">
                <h2><?= e($m['subject']) ?></h2>
                <p>
                    From <?= e($m['name']) ?>
                    (<a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a>)
                    · <?= e($m['created_at']) ?>
                </p>
                <p><?= nl2br(e($m['message'])) ?></p>
            </article>
        <?php endforeach; ?>
    </main>
</body>

</html>