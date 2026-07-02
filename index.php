<?php
/**
 * Academic E-Learning System
 * Main Entry Point
 */

require_once 'php/config.php';
require_once 'php/auth.php';

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    require_once 'php/api.php';
    exit();
}

// Check if user is logged in, if not, show login page
if (!$auth->isLoggedIn()) {
    // Show HTML with login form
    include 'academic-elearning-system.html';
    exit();
}

// User is logged in, show dashboard
// This is handled by the HTML file with JS that checks session
include 'academic-elearning-system.html';
?>
