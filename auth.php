<?php
/**
 * Authentication Handler
 * Handles login, logout, and session management
 */

require_once 'config.php';

class Auth {
    private $conn;
    
    public function __construct($database) {
        $this->conn = $database;
    }
    
    /**
     * Login user with email/username and password
     */
    public function login($username, $password, $role) {
        $username = sanitize($username);
        $role = sanitize($role);
        
        $query = "SELECT id, username, email, password, role, first_name, last_name FROM users 
                 WHERE (username = ? OR email = ?) AND role = ? AND status = 'active'";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error: ' . $this->conn->error];
        }
        
        $stmt->bind_param("sss", $username, $username, $role);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['login_time'] = time();
                
                return ['success' => true, 'message' => 'Login successful', 'redirect' => 'dashboard.php'];
            } else {
                return ['success' => false, 'message' => 'Invalid password'];
            }
        } else {
            return ['success' => false, 'message' => 'Invalid username or email'];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Get current user info
     */
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'role' => $_SESSION['role'],
                'name' => $_SESSION['first_name'] . ' ' . $_SESSION['last_name']
            ];
        }
        return null;
    }
    
    /**
     * Check user role
     */
    public function hasRole($required_roles) {
        if (!$this->isLoggedIn()) return false;
        
        if (is_array($required_roles)) {
            return in_array($_SESSION['role'], $required_roles);
        }
        return $_SESSION['role'] === $required_roles;
    }
}

// Handle AJAX login request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $auth = new Auth($conn);
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'mahasiswa';
    
    if (empty($username) || empty($password)) {
        sendResponse(false, 'Username and password are required');
    }
    
    $result = $auth->login($username, $password, $role);
    sendResponse($result['success'], $result['message'], $result);
}

// Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
    $auth = new Auth($conn);
    $result = $auth->logout();
    sendResponse($result['success'], $result['message']);
}

// Create global auth instance
$auth = new Auth($conn);
?>
