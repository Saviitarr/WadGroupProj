<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/db.php";

function require_admin() {
    if (empty($_SESSION["admin_id"])) {
        header("Location: ../login.php");
        exit;
    }
}

function csrf_token() {
    if (empty($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf"];
}

function csrf_check() {
    $expected = $_SESSION["csrf"] ?? "";
    $received = $_POST["csrf"] ?? "";

    if (!is_string($received) || !hash_equals($expected, $received)) {
        http_response_code(400);
        exit("Invalid request.");
    }
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
