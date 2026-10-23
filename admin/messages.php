<?php
require '../include/auth.php';
require_admin();

$messages = $pdo->query('SELECT * FROM contact ORDER BY created_at DESC')->fetchAll();

$pageTitle = 'Messages – Admin';
$pageCss = ['css/forms.css'];
require '../includes/header.php';
?>

<main class="m3 wrap page">
    <?php require '../include/admin_nav.php'; ?>

    <h1>Messages</h1>
    <p class="lead">Messages sent from the contact page, newest first.</p>

    <?php if (!$messages): ?>
        <div class="card">
            <p class="empty muted">No messages yet.</p>
        </div>
    <?php endif; ?>

    <div class="message-list">
        <?php foreach ($messages as $m): ?>
            <article class="card message">
                <h2><?= e($m['subject']) ?></h2>
                <p class="muted">
                    From <?= e($m['name']) ?>
                    (<a class="link" href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a>)
                    · <?= e($m['created_at']) ?>
                </p>
                <p class="message-body"><?= nl2br(e($m['message'])) ?></p>
                <div class="message-actions">
                    <a class="btn btn-small btn-ghost" href="mailto:<?= e($m['email']) ?>">Reply by email</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</main>

<?php require '../includes/footer.php'; ?>
