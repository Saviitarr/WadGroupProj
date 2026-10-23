<?php
require 'include/auth.php';

// Already logged in? No need to see the login form again.
if (is_logged_in()) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/index.php' : 'dashboard.php'));
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';   // never trim passwords - spaces can be part of them

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        // Prepared statement: the email is sent separately from the SQL, so it can't change the query (SQL injection)
        $stmt = $pdo->prepare('SELECT id, full_name, password_hash, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();   // the row as an array, or false if no user has that email

        // password_verify hashes what was typed using the salt stored inside the hash, then compares.
        // We check $user first so we never read ['password_hash'] from false.
        if ($user && password_verify($password, $user['password_hash'])) {

            // New session ID on login, so an ID planted before login is useless (session fixation)
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['full_name'];

            if ($user['role'] === 'admin') {
                header('Location: admin/index.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        }

        // Same message for "no such email" and "wrong password", so attackers can't find out which emails exist
        $error = 'Incorrect email or password. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in – Campus Connect</title>
</head>

<body>
    <main class="auth-card">
        <h1>Welcome back to Campus Connect</h1>

        <?php if (isset($_GET['registered'])): ?>
            <p class="alert alert-success">Account created – please log in.</p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="alert alert-error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="post" action="login.php">
            <label>Email <input type="email" name="email" value="<?= e($email) ?>" required></label><br>
            <label>Password <input type="password" name="password" required></label><br>
            <button type="submit">Continue</button>
        </form>

        <p>or</p>
        <a href="register.php">Create a student account</a>
        <p>Admins log in here too and are taken to the admin area.</p>
    </main>
</body>

</html>
