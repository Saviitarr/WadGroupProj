<?php
require_once __DIR__ . "/auth.php";

if (!empty($_SESSION["admin_id"])) {
    header("Location: admin/index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $statement = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $statement->execute([$username]);
    $admin = $statement->fetch();

    if ($admin && password_verify($password, $admin["password_hash"])) {
        session_regenerate_id(true);
        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["admin_name"] = $admin["username"];
        header("Location: admin/index.php");
        exit;
    }

    $error = "Invalid username or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin login | Campus Connect</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
    <main class="login-card">
        <h1>Admin login</h1>

        <?php if ($error !== ""): ?>
            <p class="alert"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

            <label for="username">Username</label>
            <input id="username" name="username" required autofocus>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <button type="submit" class="btn">Log in</button>
        </form>
    </main>
</body>
</html>
