<?php
/**
 * Academic E-Learning System
 * Database Configuration File
 */

// Database Connection Settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Change this to your phpMyAdmin password if any
define('DB_NAME', 'elearning_system');
define('DB_PORT', 3306);

// Create Connection using mysqli
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check Connection
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Define base URL
define('BASE_URL', 'http://localhost/elearning/');

// Define upload directory
define('UPLOAD_DIR', __DIR__ . '/uploads/');
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// Helper function to sanitize input
function sanitize($input) {
    global $conn;
    return htmlspecialchars($conn->real_escape_string(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Helper function to log errors
function logError($message, $file = 'error.log') {
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message\n";
    error_log($log_message, 3, __DIR__ . '/' . $file);
}

// Helper function for responses
function sendResponse($success, $message = '', $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Start session
session_start();

// Define user roles
$USER_ROLES = ['admin', 'dosen', 'mahasiswa'];
?>
