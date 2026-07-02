<?php
/**
 * Logout Handler
 * Sistem E-Learning Akademik
 */

require_once __DIR__ . '/check_auth.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../../config/database.php';

// Log logout activity
if (isLoggedIn()) {
  $user = getCurrentUser();
  logActivity($conn, $user['id'], 'LOGOUT', 'User logout');
}

// Destroy session and redirect
logout();

?>
