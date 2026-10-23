<?php
require_once __DIR__ . "/../auth.php";
require_admin();

$id = (int) ($_GET["id"] ?? $_POST["id"] ?? 0);
$listing = [
    "title" => "",
    "description" => "",
    "location" => "Windhoek",
    "price" => "",
    "image_url" => "",
    "status" => "active"
];

if ($id > 0) {
    $statement = $pdo->prepare("SELECT * FROM listings WHERE id = ?");
    $statement->execute([$id]);
    $existing = $statement->fetch();

    if (!$existing) {
        http_response_code(404);
        exit("Listing not found.");
    }

    $listing = $existing;
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();

    $listing["title"] = trim($_POST["title"] ?? "");
    $listing["description"] = trim($_POST["description"] ?? "");
    $listing["location"] = trim($_POST["location"] ?? "");
    $listing["price"] = trim($_POST["price"] ?? "");
    $listing["image_url"] = trim($_POST["image_url"] ?? "");
    $listing["status"] = ($_POST["status"] ?? "") === "inactive" ? "inactive" : "active";

    if ($listing["title"] === "") {
        $errors[] = "Listing name is required.";
    }

    if ($listing["location"] === "") {
        $errors[] = "Location is required.";
    }

    if (!is_numeric($listing["price"]) || (float) $listing["price"] <= 0) {
        $errors[] = "Enter a monthly rent greater than zero.";
    }

    if (empty($errors)) {
        $values = [
            $listing["title"],
            $listing["description"],
            $listing["location"],
            $listing["price"],
            $listing["image_url"],
            $listing["status"]
        ];

        if ($id > 0) {
            $query = "UPDATE listings SET title = ?, description = ?, location = ?, price = ?, image_url = ?, status = ? WHERE id = ?";
            $values[] = $id;
            $message = "Listing updated";
        } else {
            $query = "INSERT INTO listings (title, description, location, price, image_url, status) VALUES (?, ?, ?, ?, ?, ?)";
            $message = "Listing added";
        }

        $statement = $pdo->prepare($query);
        $statement->execute($values);
        header("Location: listings.php?msg=" . urlencode($message));
        exit;
    }
}

$page_title = $id > 0 ? "Edit listing" : "Add listing";
require __DIR__ . "/_header.php";
?>
<h1><?= e($page_title) ?></h1>

<?php foreach ($errors as $error): ?>
    <p class="alert"><?= e($error) ?></p>
<?php endforeach; ?>

<form method="post" class="card form-card">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="id" value="<?= $id ?>">

    <label for="title">Listing name</label>
    <input id="title" name="title" value="<?= e($listing["title"]) ?>" required>

    <label for="location">Area / location</label>
    <input id="location" name="location" value="<?= e($listing["location"]) ?>" required>

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="5"><?= e($listing["description"]) ?></textarea>

    <label for="price">Monthly rent (N$)</label>
    <input id="price" type="number" name="price" min="0.01" step="0.01" value="<?= e($listing["price"]) ?>" required>

    <label for="image_url">Image URL</label>
    <input id="image_url" name="image_url" value="<?= e($listing["image_url"]) ?>">

    <label for="status">Status</label>
    <select id="status" name="status">
        <option value="active" <?= $listing["status"] === "active" ? "selected" : "" ?>>Active</option>
        <option value="inactive" <?= $listing["status"] === "inactive" ? "selected" : "" ?>>Inactive</option>
    </select>

    <div class="button-row">
        <button type="submit" class="btn">Save listing</button>
        <a href="listings.php" class="btn btn-outline">Cancel</a>
    </div>
</form>
<?php require __DIR__ . "/_footer.php"; ?>
