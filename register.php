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
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register | Campus Connect</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body class="auth-page">

    <header class="auth-header">

        <a href="index.php" class="auth-logo">
            <span class="logo-icon">CC</span>
            <span>Campus Connect</span>
        </a>

        <p class="register-login-link">
            Have an account? <a href="login.php">Log in</a>
        </p>

    </header>

    <main class="auth-main register-main">

        <div class="auth-card register-card">

            <div class="auth-card-header">
                Sign up
            </div>

            <div class="auth-card-content">

                <h1>Create your student account</h1>

                <p class="register-description">
                    Use your NUST student email so we can confirm
                    you're a student.
                </p>

                <!-- Server-side errors -->
                <?php if (!empty($errors)): ?>
                    <div class="auth-error" role="alert">

                        <?php foreach ($errors as $err): ?>
                            <p><?php echo e($err); ?></p>
                        <?php endforeach; ?>

                    </div>
                <?php endif; ?>

                <!-- JavaScript errors -->
                <div id="register-error" class="auth-error"
                     role="alert" hidden></div>

                <form id="register-form" method="POST"
                        action="register.php" novalidate>

                    <div class="auth-field">
                        <label for="full-name">Full name</label>

                        <input
                            type="text"
                            id="full-name"
                            name="full_name"
                            placeholder="Enter your full name"
                            value="<?php echo e($old['full_name']); ?>"
                            autocomplete="name"
                            required
                        >
                    </div>

                    <div class="register-field-row">

                        <div class="auth-field">
                            <label for="student-number">
                                Student number
                            </label>

                            <input
                                type="text"
                                id="student-number"
                                name="student_number"
                                placeholder="9-digit number"
                                value="<?php echo e($old['student_number']); ?>"
                                maxlength="9"
                                inputmode="numeric"
                                required
                            >
                        </div>

                        <div class="auth-field">
                            <label for="register-phone">Phone</label>

                            <input
                                type="tel"
                                id="register-phone"
                                name="phone"
                                placeholder="+264..."
                                value="<?php echo e($old['phone']); ?>"
                                autocomplete="tel"
                            >
                        </div>

                    </div>

                    <div class="auth-field">
                        <label for="register-email">
                            Student email
                        </label>

                        <input
                            type="email"
                            id="register-email"
                            name="email"
                            placeholder="name@students.nust.na"
                            value="<?php echo e($old['email']); ?>"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label for="register-password">Password</label>

                        <div class="password-container">

                            <input
                                type="password"
                                id="register-password"
                                name="password"
                                placeholder="Create a password"
                                autocomplete="new-password"
                                minlength="8"
                                required
                            >

                            <button type="button"
                                    class="show-password"
                                    id="show-register-password">
                                Show
                            </button>

                        </div>

                        <p class="password-hint">
                            Password must contain at least 8 characters.
                        </p>
                    </div>

                    <div class="auth-field">
                        <label for="confirm-password">
                            Confirm password
                        </label>

                        <div class="password-container">

                            <input
                                type="password"
                                id="confirm-password"
                                name="confirm_password"
                                placeholder="Confirm your password"
                                autocomplete="new-password"
                                required
                            >

                            <button type="button"
                                    class="show-password"
                                    id="show-confirm-password">
                                Show
                            </button>

                        </div>
                    </div>

                    <div class="register-terms">

                        <input type="checkbox"
                               id="register-agreement"
                               name="accept_terms"
                               value="1"
                               required>

                        <label for="register-agreement">
                            I agree to the terms of use and
                            confirm that my details are correct.
                        </label>

                    </div>

                    <button type="submit" class="auth-submit">
                        Agree and continue
                    </button>

                </form>

                <p class="auth-note">
                    Admin accounts are created by the system administrator.
                </p>

            </div>

        </div>

    </main>

    <script src="js/script.js"></script>

</body>
</html>
