<?php
require_once __DIR__ . "/../auth.php";
require_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title ?? "Admin") ?> | Campus Connect</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body class="admin-site">
    <header class="site-header">
        <a href="index.php" class="brand">Campus Connect <span class="admin-badge">ADMIN</span></a>
        <nav aria-label="Admin navigation">
            <a href="index.php">Overview</a>
            <a href="listings.php">Listings</a>
            <a href="bookings.php">Bookings</a>
            <a href="../logout.php">Log out</a>
        </nav>
    </header>
    <main class="page-width">
