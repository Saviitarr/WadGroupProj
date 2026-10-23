
<?php
$pageTitle = $pageTitle ?? "Campus Connect";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($pageTitle); ?> | Campus Connect
    </title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<header class="site-header">
    <div class="nav-container">

        <a href="index.php" class="logo">
            <span class="logo-icon">CC</span>
            <span>Campus Connect</span>
        </a>

        <button class="menu-toggle" id="menu-toggle"
                type="button" aria-expanded="false"
                aria-controls="site-nav">
            Menu
        </button>

        <nav class="site-nav" id="site-nav">
            <a href="listings.php">Browse rooms</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
            <a href="login.php">Log in</a>
            <a href="register.php" class="signup-btn">Sign up</a>
        </nav>

    </div>
</header>
