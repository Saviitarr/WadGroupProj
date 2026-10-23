<?php
// Shared header (Member 1). Pages set these before including it:
//   $pageTitle - text for the <title>
//   $pageCss   - optional array of extra stylesheets, e.g. ['css/forms.css']
// auth.php starts the session so the menu can show the right links when someone is logged in.
require_once __DIR__ . '/../include/auth.php';

$pageTitle = $pageTitle ?? "Campus Connect";
$pageCss   = $pageCss ?? [];
$current   = basename($_SERVER['SCRIPT_NAME'] ?? '');   // e.g. "about.php", used to highlight the menu
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($pageTitle); ?> | Campus Connect
    </title>

    <link rel="stylesheet" href="<?= $base ?>css/style.css">
    <?php foreach ($pageCss as $css): ?>
        <link rel="stylesheet" href="<?= $base . e($css) ?>">
    <?php endforeach; ?>
</head>

<body>

<header class="site-header">
    <div class="nav-container">

        <a href="<?= $base ?>index.php" class="logo">
            <span class="logo-icon">CC</span>
            <span>Campus Connect</span>
        </a>

        <button class="menu-toggle" id="menu-toggle"
                type="button" aria-expanded="false"
                aria-controls="site-nav">
            Menu
        </button>

        <nav class="site-nav" id="site-nav" aria-label="Main navigation">
            <a href="<?= $base ?>listings.php"<?= $current === 'listings.php' ? ' aria-current="page"' : '' ?>>Browse rooms</a>
            <a href="<?= $base ?>about.php"<?= $current === 'about.php' ? ' aria-current="page"' : '' ?>>About</a>
            <a href="<?= $base ?>contact.php"<?= $current === 'contact.php' ? ' aria-current="page"' : '' ?>>Contact</a>

            <?php if (is_admin()): ?>
                <a href="<?= $base ?>admin/index.php">Admin</a>
                <a href="<?= $base ?>logout.php" class="signup-btn">Log out</a>
            <?php elseif (is_logged_in()): ?>
                <a href="<?= $base ?>dashboard.php"<?= $current === 'dashboard.php' ? ' aria-current="page"' : '' ?>>My bookings</a>
                <a href="<?= $base ?>logout.php" class="signup-btn">Log out</a>
            <?php else: ?>
                <a href="<?= $base ?>login.php"<?= $current === 'login.php' ? ' aria-current="page"' : '' ?>>Log in</a>
                <a href="<?= $base ?>register.php" class="signup-btn">Sign up</a>
            <?php endif; ?>
        </nav>

    </div>
</header>
