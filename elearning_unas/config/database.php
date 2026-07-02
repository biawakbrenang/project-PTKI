<?php
/**
 * Database Configuration
 * Sistem E-Learning Akademik
 */

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Kosongkan jika tidak ada password
define('DB_NAME', 'elearning_unas');
define('DB_CHARSET', 'utf8mb4');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset(DB_CHARSET);

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Return connection
return $conn;
?>
