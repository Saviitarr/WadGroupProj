<?php
require_once __DIR__ . "/../auth.php";
require_admin();

$allowedStatuses = ["pending", "approved", "rejected", "cancelled"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();

    $status = $_POST["status"] ?? "";
    $id = (int) ($_POST["id"] ?? 0);

    if ($id > 0 && in_array($status, $allowedStatuses, true)) {
        $statement = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $statement->execute([$status, $id]);
    }

    header("Location: bookings.php?msg=" . urlencode("Booking updated"));
    exit;
}

$filter = $_GET["status"] ?? "";
$query = "SELECT b.*, l.title FROM bookings b JOIN listings l ON l.id = b.listing_id";
$values = [];

if (in_array($filter, $allowedStatuses, true)) {
    $query .= " WHERE b.status = ?";
    $values[] = $filter;
}

$query .= " ORDER BY b.created_at DESC";
$statement = $pdo->prepare($query);
$statement->execute($values);
$bookings = $statement->fetchAll();

$page_title = "Manage bookings";
require __DIR__ . "/_header.php";
?>
<h1>Manage bookings</h1>
<p class="muted">Review and update student booking requests.</p>

<?php if (!empty($_GET["msg"])): ?>
    <p class="ok"><?= e($_GET["msg"]) ?></p>
<?php endif; ?>

<nav class="filters" aria-label="Booking status filters">
    <a href="bookings.php">All</a>
    <?php foreach ($allowedStatuses as $item): ?>
        <a href="bookings.php?status=<?= e($item) ?>"><?= e(ucfirst($item)) ?></a>
    <?php endforeach; ?>
</nav>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Listing</th>
                <th>Student / customer</th>
                <th>Dates</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?= e($booking["title"]) ?></td>
                    <td>
                        <?= e($booking["customer_name"]) ?><br>
                        <small><?= e($booking["customer_email"]) ?></small>
                    </td>
                    <td><?= e($booking["start_date"]) ?> to <?= e($booking["end_date"]) ?></td>
                    <td>
                        <span class="tag <?= e($booking["status"]) ?>">
                            <?= e(ucfirst($booking["status"])) ?>
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <?php foreach (["approved" => "Approve", "rejected" => "Decline", "cancelled" => "Cancel"] as $newStatus => $label): ?>
                                <?php if ($booking["status"] !== $newStatus): ?>
                                    <form method="post">
                                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $booking["id"] ?>">
                                        <input type="hidden" name="status" value="<?= e($newStatus) ?>">
                                        <button class="btn btn-small <?= $newStatus === "approved" ? "" : "btn-outline" ?>" type="submit">
                                            <?= e($label) ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($bookings)): ?>
                <tr><td colspan="5">No booking requests found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . "/_footer.php"; ?>
