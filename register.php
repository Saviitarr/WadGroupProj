<?php
require 'include/auth.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$old = ['full_name' => '', 'student_number' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name'] ?? '');
    $student_number = trim($_POST['student_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $old = compact('full_name', 'student_number', 'email', 'phone');

    if ($full_name === '') {
        $errors[] = 'Full name is required.';
    }
    if (!preg_match('/^\d{9}$/', $student_number)) {
        $errors[] = 'Student number must be 9 digits.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR student_number = ?');
        $stmt->execute([$email, $student_number]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email or student number already exists.';
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO users (full_name, student_number, email, phone, password_hash)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $full_name,
            $student_number,
            $email,
            $phone,
            password_hash($password, PASSWORD_DEFAULT),
        ]);

        header('Location: login.php?registered=1');
        exit;
    }
}// ← closes the "if POST" from Block 2
$pageTitle = 'Create an account';
$pageCss   = ['css/forms.css'];
require 'includes/header.php';
?>
<main class="m3 wrap page auth-page">
    <div class="card auth-card auth-card-wide">
        <h1>Create your account</h1>
        <p class="lead">Sign up with your NUST student details to request rooms.</p>

        <?php if ($errors): ?>
            <div class="alert alert-error" role="alert">
                <?php foreach ($errors as $err): ?>
                    <p><?= e($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="register.php" class="form js-validate" novalidate>
            <div class="field">
                <label for="full_name">Full name</label>
                <input type="text" id="full_name" name="full_name" value="<?= e($old['full_name']) ?>" required
                       maxlength="100" autocomplete="name" aria-describedby="full_name-error">
                <p class="field-error" id="full_name-error" hidden></p>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="student_number">Student number</label>
                    <input type="text" id="student_number" name="student_number" value="<?= e($old['student_number']) ?>"
                           required inputmode="numeric" pattern="\d{9}" maxlength="9" aria-describedby="student_number-error">
                    <p class="field-error" id="student_number-error" hidden></p>
                </div>
                <div class="field">
                    <label for="phone">Phone <span class="optional">(optional)</span></label>
                    <input type="tel" id="phone" name="phone" value="<?= e($old['phone']) ?>" maxlength="20" autocomplete="tel">
                </div>
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" required
                       maxlength="150" autocomplete="email" aria-describedby="email-error">
                <p class="field-error" id="email-error" hidden></p>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="8"
                           autocomplete="new-password" aria-describedby="password-hint password-error">
                    <p class="hint" id="password-hint">At least 8 characters.</p>
                    <p class="field-error" id="password-error" hidden></p>
                </div>
                <div class="field">
                    <label for="confirm_password">Confirm password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           autocomplete="new-password" aria-describedby="confirm_password-error">
                    <p class="field-error" id="confirm_password-error" hidden></p>
                </div>
            </div>
            <button type="submit" class="btn btn-block">Create account</button>
        </form>

        <p class="auth-switch">Already have an account? <a class="link" href="login.php">Log in</a></p>
    </div>
</main>
<script src="js/auth-form.js"></script>

<?php require 'includes/footer.php'; ?>
