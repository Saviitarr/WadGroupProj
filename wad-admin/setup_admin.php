<?php
require_once __DIR__ . "/auth.php";

$numberOfAdmins = (int) $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();

if ($numberOfAdmins > 0) {
    http_response_code(403);
    exit("Admin setup is disabled because an account already exists.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || strlen($password) < 8) {
        $message = "Enter a username and a password of at least 8 characters.";
    } else {
        $statement = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
        $statement->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
        $message = "Admin created. Delete setup_admin.php before sharing or deploying this project.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create admin | Campus Connect</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
    <main class="login-card">
        <h1>Create an admin</h1>
        <?php if ($message !== ""): ?>
            <p class="ok"><?= e($message) ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <label for="username">Username</label>
            <input id="username" name="username" required>
            <label for="password">Password</label>
            <input id="password" type="password" name="password" minlength="8" required>
            <button type="submit" class="btn">Create admin</button>
        </form>
    </main>
</body>
</html>
