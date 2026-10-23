<?php
// Shared helpers for the booking, dashboard and contact pages (Member 3).
// Builds on Member 4's include/auth.php: that file starts the session, connects $pdo
// and defines e(), is_logged_in(), require_login() and require_admin().
// Every function below is wrapped in function_exists() so whichever file defines it first wins.
//
// SESSION KEYS this code expects after login (agree these with Member 4):
//   $_SESSION['user_id']   - users.id
//   $_SESSION['role']      - 'student' or 'admin'
//   $_SESSION['full_name'] - users.full_name

require_once __DIR__ . '/../include/auth.php';   // session_start() + $pdo + e() etc.
require_once __DIR__ . '/../config/db.php';      // already loaded by auth.php; harmless safety net

// Where the listings page lives.
$listingsUrl = 'listings.php';   // Member 2's listings page (project root)

// Print the one-time flash message (if any). Call it at the top of <main>.
if (!function_exists('show_flash')) {
    function show_flash()
    {
        $message = take_flash();
        if ($message) {
            echo '<div class="alert alert-' . e($message['type']) . '" role="status">' . e($message['text']) . '</div>';
        }
    }
}

// Escape text before printing it into HTML (stops XSS)
if (!function_exists('e')) {
    function e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

// Format a number as Namibian dollars, e.g. N$3,200
if (!function_exists('money')) {
    function money($amount)
    {
        return 'N$' . number_format((float) $amount, 0, '.', ',');
    }
}

// Format a date from the database (Y-m-d) as e.g. 1 Feb 2027
if (!function_exists('nice_date')) {
    function nice_date($date)
    {
        return date('j M Y', strtotime($date));
    }
}

// Is anyone logged in?
if (!function_exists('is_logged_in')) {
    function is_logged_in()
    {
        return isset($_SESSION['user_id']);
    }
}

// Send visitors who are not logged in to the login page, then come back here
if (!function_exists('require_login')) {
    function require_login()
    {
        if (!is_logged_in()) {
            $_SESSION['flash'] = ['type' => 'error', 'text' => 'Please log in first.'];
            $back = urlencode($_SERVER['REQUEST_URI'] ?? '');
            header('Location: login.php?redirect=' . $back);
            exit;
        }
    }
}

// Load the logged-in user's row from the database (or null)
if (!function_exists('current_user')) {
    function current_user(PDO $pdo)
    {
        if (!is_logged_in()) {
            return null;
        }
        $stmt = $pdo->prepare('SELECT id, full_name, student_number, email, phone, role, created_at
                               FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch() ?: null;
    }
}

// ---- CSRF protection: a secret token in the session that every POST form must send back ----
if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field()
    {
        return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_ok')) {
    function csrf_ok()
    {
        return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
    }
}

// ---- Flash messages: a one-time message shown after a redirect ----
if (!function_exists('flash')) {
    function flash($type, $text)
    {
        $_SESSION['flash'] = ['type' => $type, 'text' => $text];
    }
}

if (!function_exists('take_flash')) {
    function take_flash()
    {
        $message = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $message;
    }
}
