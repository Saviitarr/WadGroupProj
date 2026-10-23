<?php
session_start();
require_once __DIR__ . '/../config/db.php';   // __DIR__ = this file's folder, so the path works from any page

// Safe output: converts < > " ' into harmless text so user input can't inject HTML/JS (XSS)
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES,'UTF-8');
}

// Is anyone logged in?
function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

// Put at the top of pages that need login (dashboard, booking)
function require_login() {
    if(!is_logged_in()) {
        header('Location: /accommodation/login.php');
        exit;
    }

}

// Put at the top of every admin page
function require_admin() {
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit('Access denied');
    }
}