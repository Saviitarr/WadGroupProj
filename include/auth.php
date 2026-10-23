<?php
if (session_status() === PHP_SESSION_NONE) {   // only start once, even if two files include this
    session_start();
}
require_once __DIR__ . '/../config/db.php';   // __DIR__ = this file's folder, so the path works from any page

// Pages inside admin/ are one folder deeper, so links back to the root need "../".
// $base is "" for root pages and "../" for admin pages; header.php uses it for every link.
$base = (basename(dirname($_SERVER['SCRIPT_NAME'] ?? '')) === 'admin') ? '../' : '';

// Safe output: converts < > " ' into harmless text so user input can't inject HTML/JS (XSS)
function e($value) {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// Is anyone logged in?
function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

// Is the logged-in user an admin?
function is_admin() {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

// Put at the top of pages that need login (dashboard, booking)
function require_login() {
    global $base;
    if(!is_logged_in()) {
        // remember where they were going, so login.php can send them back afterwards
        $_SESSION['after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        header('Location: ' . $base . 'login.php');
        exit;
    }

}

// Put at the top of every admin page
function require_admin() {
    global $base;
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../forbidden.php';   // friendly "no access" page instead of a blank screen
        exit;
    }
}
