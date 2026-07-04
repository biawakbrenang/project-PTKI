<?php
/**
 * ECO-LEARNING - Route Guards & Authentication Middleware
 */
require_once __DIR__ . '/../config.php';

function restrictTo($roles = []) {
    if (!isLoggedIn()) {
        header("Location: ../auth/login.php");
        exit();
    }
    
    if (!empty($roles) && !in_array($_SESSION['role'], $roles)) {
        // Redirect if unauthorized
        header("Location: ../auth/login.php?error=unauthorized");
        exit();
    }
}
?>
