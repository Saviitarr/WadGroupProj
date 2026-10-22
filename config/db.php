<?php
// Connection settings (XAMPP defaults)
$host = 'localhost';
$db   = 'accommodation';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // show errors instead of failing silently
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // rows come back as ['column' => value]
        ]
    );
} catch (PDOException $e) {
    echo "Stop". $e->getMessage();
    exit();
    
}