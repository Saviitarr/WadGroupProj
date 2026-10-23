<?php
// forbidden.php - shown (with HTTP 403) when a logged-in student opens an admin page.
// include/auth.php's require_admin() includes this file, so $base and e() already exist.
require_once __DIR__ . '/include/auth.php';
$pageTitle = 'Access denied';
$pageCss   = ['css/forms.css'];
require __DIR__ . '/includes/header.php';
?>
<main class="m3 wrap page">
    <div class="card forbidden">
        <p class="big">403</p>
        <h1>You don't have access to this page</h1>
        <p class="lead">This area is for administrators only.</p>
        <a class="btn" href="<?= $base ?>dashboard.php">Go to my bookings</a>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
