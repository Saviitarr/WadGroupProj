<?php
require_once __DIR__ . "/../auth.php";
require_admin();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();

    if (($_POST["action"] ?? "") === "delete") {
        $id = (int) ($_POST["id"] ?? 0);

        if ($id > 0) {
            $statement = $pdo->prepare("DELETE FROM listings WHERE id = ?");
            $statement->execute([$id]);
        }

        header("Location: listings.php?msg=" . urlencode("Listing deleted"));
        exit;
    }
}

$statement = $pdo->query("SELECT * FROM listings ORDER BY created_at DESC");
$listings = $statement->fetchAll();
$page_title = "Manage listings";
require __DIR__ . "/_header.php";
?>
<div class="page-heading">
    <div>
        <h1>Manage listings</h1>
        <p class="muted">Add, edit or remove accommodation listings.</p>
    </div>
    <a class="btn" href="listing_form.php">Add listing</a>
</div>

<?php if (!empty($_GET["msg"])): ?>
    <p class="ok"><?= e($_GET["msg"]) ?></p>
<?php endif; ?>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Listing</th>
                <th>Location</th>
                <th>Monthly rent</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($listings as $listing): ?>
                <tr>
                    <td><?= e($listing["title"]) ?></td>
                    <td><?= e($listing["location"]) ?></td>
                    <td>N$<?= number_format((float) $listing["price"], 2) ?></td>
                    <td><span class="tag <?= e($listing["status"]) ?>"><?= e(ucfirst($listing["status"])) ?></span></td>
                    <td>
                        <div class="actions">
                            <a href="listing_form.php?id=<?= (int) $listing["id"] ?>">Edit</a>
                            <form method="post" onsubmit="return confirm('Delete this listing? Related bookings may also be deleted.')">
                                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $listing["id"] ?>">
                                <button type="submit" class="link-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($listings)): ?>
                <tr><td colspan="5">No listings found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . "/_footer.php"; ?>
