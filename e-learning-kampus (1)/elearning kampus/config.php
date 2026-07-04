<?php
/**
 * ECO-LEARNING - Database Connection & Global Configurations
 * Technology: PHP Native (PDO) & MySQL
 */

// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => false, // Set to true if running over HTTPS
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'elearning_kampus');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // Handle error gracefully
    die("Koneksi Database Gagal: " . htmlspecialchars($e->getMessage()));
}

// Global Help Functions
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}
?>
