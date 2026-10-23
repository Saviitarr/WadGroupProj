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
            $_SESSION['full_name'] = $user['full_name'];   // same value; the booking pages read this key

            // Send them back to the page they were trying to open (e.g. book.php), if there was one.
            // Only accept paths on this site ("/..."), never "//other-site.com" (open redirect).
            $next = $_SESSION['after_login'] ?? '';
            unset($_SESSION['after_login']);

            if ($user['role'] === 'admin') {
                header('Location: admin/index.php');
            } elseif ($next !== '' && $next[0] === '/' && substr($next, 0, 2) !== '//') {
                header('Location: ' . $next);
            } else {
                header('Location: dashboard.php');
            }
            exit;
        }

        // Same message for "no such email" and "wrong password", so attackers can't find out which emails exist
        $error = 'Incorrect email or password. Please try again.';
    }
}

$pageTitle = 'Log in';
$pageCss   = ['css/forms.css'];
require 'includes/header.php';
?>
<main class="m3 wrap page auth-page">
    <div class="card auth-card">
        <h1>Welcome back</h1>
        <p class="lead">Log in to book rooms and see your requests.</p>

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success" role="status">Account created – please log in.</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php" class="form js-validate" novalidate>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($email) ?>" required autocomplete="email"
                       aria-describedby="email-error">
                <p class="field-error" id="email-error" hidden></p>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password"
                       aria-describedby="password-error">
                <p class="field-error" id="password-error" hidden></p>
            </div>
            <button type="submit" class="btn btn-block">Continue</button>
        </form>

        <p class="auth-switch">New here? <a class="link" href="register.php">Create a student account</a></p>
        <p class="muted small">Admins log in here too and are taken to the admin area.</p>
    </div>
</main>
<script src="js/auth-form.js"></script>

<?php require 'includes/footer.php'; ?>
