<?php
/**
 * Authentication Check & Helper Functions
 * Sistem E-Learning Akademik
 */

/**
 * Start session if not already started
 */
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
  return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require an authenticated user.
 */
function requireLogin($role = null) {
  if (!isLoggedIn()) {
    header('Location: /elearning_unas/src/auth/login.php');
    exit;
  }

  if ($role !== null) {
    $allowedRoles = is_array($role) ? $role : [$role];
    $user = getCurrentUser();

    if (!in_array($user['role'], $allowedRoles, true)) {
      header('Location: /elearning_unas/src/auth/login.php');
      exit;
    }
  }
}

/**
 * Get current logged in user
 */
function getCurrentUser() {
  if (!isLoggedIn()) {
    return null;
  }
  
  return [
    'id' => $_SESSION['user_id'],
    'username' => $_SESSION['username'],
    'nama_lengkap' => $_SESSION['nama_lengkap'],
    'role' => $_SESSION['role'],
    'email' => $_SESSION['email'] ?? null
  ];
}

/**
 * Set user session after login
 */
function setUserSession($user) {
  session_start();
  $_SESSION['user_id'] = $user['id'];
  $_SESSION['username'] = $user['username'];
  $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
  $_SESSION['role'] = $user['role'];
  $_SESSION['email'] = $user['email'] ?? null;
  $_SESSION['login_time'] = time();
}

/**
 * Require specific role
 */
function requireRole($role) {
  if (!isLoggedIn()) {
    header('Location: /elearning_unas/src/auth/login.php');
    exit;
  }
  
  $user = getCurrentUser();
  $allowedRoles = is_array($role) ? $role : [$role];

  if (!in_array($user['role'], $allowedRoles, true)) {
    header('Location: /elearning_unas/src/auth/login.php');
    exit;
  }
}

/**
 * Redirect by role
 */
function redirectByRole() {
  if (!isLoggedIn()) {
    header('Location: /elearning_unas/src/auth/login.php');
    exit;
  }
  
  $user = getCurrentUser();
  
  switch ($user['role']) {
    case 'mahasiswa':
      header('Location: /elearning_unas/src/mahasiswa/dashboard.php');
      break;
    case 'dosen':
      header('Location: /elearning_unas/src/dosen/dashboard.php');
      break;
    case 'admin':
      header('Location: /elearning_unas/src/admin/dashboard.php');
      break;
    default:
      header('Location: /elearning_unas/src/auth/login.php');
  }
  exit;
}

/**
 * Logout user
 */
function logout() {
  session_start();
  session_destroy();
  header('Location: /elearning_unas/src/auth/login.php');
  exit;
}

?>
