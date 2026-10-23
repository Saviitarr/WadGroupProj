<?php
require_once __DIR__ . "/../auth.php";
require_admin();

$page_title = "Overview";

$stats = [
    "Total listings" => $pdo->query("SELECT COUNT(*) FROM listings")->fetchColumn(),
    "Active listings" => $pdo->query("SELECT COUNT(*) FROM listings WHERE status = 'active'")->fetchColumn(),
    "Pending bookings" => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
    "Approved bookings" => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'approved'")->fetchColumn()
];

require __DIR__ . "/_header.php";
?>
<h1>Admin overview</h1>
<p class="muted">Manage accommodation listings and booking requests.</p>

<section class="stats" aria-label="Overview statistics">
    <?php foreach ($stats as $label => $number): ?>
        <div class="stat">
            <strong><?= (int) $number ?></strong>
            <span><?= e($label) ?></span>
        </div>
    <?php endforeach; ?>
</section>

<div class="button-row">
    <a class="btn" href="listing_form.php">Add a listing</a>
    <a class="btn btn-outline" href="bookings.php?status=pending">Review pending bookings</a>
</div>
<?php require __DIR__ . "/_footer.php"; ?>
