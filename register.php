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
    <title>Register</title>
</head>

<body>
    <h1>Register</h1>

    <?php foreach ($errors as $err): ?>
        <p style="color:red"><?= e($err) ?></p>
    <?php endforeach; ?>

    <form method="post" action="register.php">
        <label>Full name <input type="text" name="full_name" value="<?= e($old['full_name']) ?>"></label><br>
        <label>Student number <input type="text" name="student_number"
                value="<?= e($old['student_number']) ?>"></label><br>
        <label>Email <input type="email" name="email" value="<?= e($old['email']) ?>"></label><br>
        <label>Phone <input type="tel" name="phone" value="<?= e($old['phone']) ?>"></label><br>
        <label>Password <input type="password" name="password"></label><br>
        <label>Confirm password <input type="password" name="confirm_password"></label><br>
        <button type="submit">Register</button>
    </form>
</body>

</html>