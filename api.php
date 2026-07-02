<?php
/**
 * API Handler for AJAX Requests
 * Routes all data operations to respective handler methods
 */

require_once 'config.php';
require_once 'auth.php';

header('Content-Type: application/json');

// Check if user is logged in for all operations except login
if (!isset($_POST['action']) || $_POST['action'] !== 'login') {
    if (!$auth->isLoggedIn()) {
        sendResponse(false, 'Unauthorized - Please login first');
    }
}

$action = sanitize($_POST['action'] ?? '');

switch ($action) {
    // Dashboard Data
    case 'getDashboardData':
        getDashboardData();
        break;
    
    // Classes
    case 'getClasses':
        getClasses();
        break;
    
    case 'getClassDetail':
        getClassDetail();
        break;
    
    // Assignments
    case 'getAssignments':
        getAssignments();
        break;
    
    case 'createAssignment':
        createAssignment();
        break;
    
    case 'getSubmissions':
        getSubmissions();
        break;
    
    case 'submitAssignment':
        submitAssignment();
        break;
    
    case 'gradeSubmission':
        gradeSubmission();
        break;
    
    // Attendance
    case 'getAttendance':
        getAttendance();
        break;
    
    case 'markAttendance':
        markAttendance();
        break;
    
    case 'recordAttendance':
        recordAttendance();
        break;
    
    // Announcements
    case 'getAnnouncements':
        getAnnouncements();
        break;
    
    case 'createAnnouncement':
        createAnnouncement();
        break;
    
    // Grades
    case 'getGrades':
        getGrades();
        break;
    
    // User Management (Admin)
    case 'getUsers':
        getUsers();
        break;
    
    case 'createUser':
        createUser();
        break;
    
    case 'updateUser':
        updateUser();
        break;
    
    case 'deleteUser':
        deleteUser();
        break;
    
    default:
        sendResponse(false, 'Invalid action');
}

// =================== Dashboard Functions ===================
function getDashboardData() {
    global $conn, $auth;
    
    $user = $auth->getCurrentUser();
    $data = [];
    
    if ($user['role'] === 'dosen') {
        // Dosen Dashboard
        $class_query = "SELECT COUNT(*) as total FROM classes WHERE lecturer_id = {$user['id']}";
        $assignment_query = "SELECT COUNT(*) as total FROM assignments WHERE created_by = {$user['id']} 
                            AND deadline > NOW()";
        $submission_query = "SELECT COUNT(*) as total FROM submissions WHERE submitted_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
                            AND assignment_id IN (SELECT id FROM assignments WHERE created_by = {$user['id']})";
        
        $data['total_classes'] = $conn->query($class_query)->fetch_assoc()['total'];
        $data['active_assignments'] = $conn->query($assignment_query)->fetch_assoc()['total'];
        $data['new_submissions'] = $conn->query($submission_query)->fetch_assoc()['total'];
        
    } elseif ($user['role'] === 'mahasiswa') {
        // Mahasiswa Dashboard
        $pending_query = "SELECT COUNT(*) as total FROM assignments WHERE id IN 
                         (SELECT assignment_id FROM assignments WHERE id NOT IN 
                         (SELECT assignment_id FROM submissions WHERE student_id = {$user['id']})
                         AND deadline > NOW())";
        
        $deadline_query = "SELECT DATEDIFF(MIN(deadline), NOW()) as days FROM assignments 
                          WHERE deadline > NOW() AND id NOT IN 
                          (SELECT assignment_id FROM submissions WHERE student_id = {$user['id']})";
        
        $grade_query = "SELECT AVG(grade) as avg FROM submissions WHERE student_id = {$user['id']} 
                       AND grade IS NOT NULL";
        
        $attendance_query = "SELECT COUNT(CASE WHEN status = 'hadir' THEN 1 END) as present,
                            COUNT(*) as total FROM attendance WHERE student_id = {$user['id']}";
        
        $pending_result = $conn->query($pending_query)->fetch_assoc();
        $data['pending_assignments'] = $pending_result['total'] ?? 0;
        
        $deadline_result = $conn->query($deadline_query)->fetch_assoc();
        $data['nearest_deadline'] = max(0, $deadline_result['days'] ?? 0);
        
        $grade_result = $conn->query($grade_query)->fetch_assoc();
        $data['average_grade'] = round($grade_result['avg'] ?? 0, 1);
        
        $attendance_result = $conn->query($attendance_query)->fetch_assoc();
        $total = $attendance_result['total'] ?? 1;
        $data['attendance_percentage'] = round(($attendance_result['present'] ?? 0) / $total * 100);
        
    } elseif ($user['role'] === 'admin') {
        // Admin Dashboard
        $data['total_users'] = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
        $data['total_dosen'] = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'dosen'")->fetch_assoc()['total'];
        $data['total_mahasiswa'] = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'mahasiswa'")->fetch_assoc()['total'];
        $data['total_classes'] = $conn->query("SELECT COUNT(*) as total FROM classes")->fetch_assoc()['total'];
    }
    
    sendResponse(true, 'Dashboard data retrieved', $data);
}

// =================== Classes Functions ===================
function getClasses() {
    global $conn, $auth;
    
    $user = $auth->getCurrentUser();
    $classes = [];
    
    if ($user['role'] === 'dosen') {
        $query = "SELECT c.*, co.name as course_name, COUNT(ce.id) as student_count
                 FROM classes c
                 JOIN courses co ON c.course_id = co.id
                 LEFT JOIN class_enrollments ce ON c.id = ce.class_id
                 WHERE c.lecturer_id = {$user['id']}
                 GROUP BY c.id
                 ORDER BY c.code";
    } elseif ($user['role'] === 'mahasiswa') {
        $query = "SELECT c.*, co.name as course_name, u.first_name, u.last_name
                 FROM classes c
                 JOIN courses co ON c.course_id = co.id
                 JOIN users u ON c.lecturer_id = u.id
                 JOIN class_enrollments ce ON c.id = ce.class_id
                 WHERE ce.student_id = {$user['id']} AND ce.status = 'active'
                 ORDER BY c.code";
    } else {
        $query = "SELECT c.*, co.name as course_name, u.first_name, u.last_name,
                 COUNT(ce.id) as student_count
                 FROM classes c
                 JOIN courses co ON c.course_id = co.id
                 JOIN users u ON c.lecturer_id = u.id
                 LEFT JOIN class_enrollments ce ON c.id = ce.class_id
                 GROUP BY c.id
                 ORDER BY c.code";
    }
    
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
    
    sendResponse(true, 'Classes retrieved', $classes);
}

// =================== Assignments Functions ===================
function getAssignments() {
    global $conn, $auth;
    
    $user = $auth->getCurrentUser();
    $class_id = intval($_POST['class_id'] ?? 0);
    
    if ($user['role'] === 'dosen') {
        $query = "SELECT a.*, c.code as class_code FROM assignments a 
                 JOIN classes c ON a.class_id = c.id 
                 WHERE a.created_by = {$user['id']}";
    } elseif ($user['role'] === 'mahasiswa') {
        $query = "SELECT a.*, c.code as class_code FROM assignments a 
                 JOIN classes c ON a.class_id = c.id 
                 JOIN class_enrollments ce ON c.id = ce.class_id 
                 WHERE ce.student_id = {$user['id']}";
    } else {
        $query = "SELECT a.*, c.code as class_code FROM assignments a 
                 JOIN classes c ON a.class_id = c.id";
    }
    
    if ($class_id > 0) {
        $query .= " AND a.class_id = $class_id";
    }
    
    $query .= " ORDER BY a.deadline DESC";
    
    $result = $conn->query($query);
    $assignments = [];
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
    
    sendResponse(true, 'Assignments retrieved', $assignments);
}

function createAssignment() {
    global $conn, $auth;
    
    if (!$auth->hasRole('dosen')) {
        sendResponse(false, 'Unauthorized');
    }
    
    $class_id = intval($_POST['class_id'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $deadline = sanitize($_POST['deadline'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    if (empty($title) || empty($deadline)) {
        sendResponse(false, 'Title and deadline are required');
    }
    
    $query = "INSERT INTO assignments (class_id, title, description, deadline, created_by) 
             VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssi", $class_id, $title, $description, $deadline, $user_id);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Assignment created successfully', ['id' => $conn->insert_id]);
    } else {
        sendResponse(false, 'Error creating assignment: ' . $conn->error);
    }
}

// =================== Submissions Functions ===================
function getSubmissions() {
    global $conn, $auth;
    
    $user = $auth->getCurrentUser();
    $assignment_id = intval($_POST['assignment_id'] ?? 0);
    
    if ($user['role'] === 'dosen') {
        $query = "SELECT s.*, u.first_name, u.last_name, a.title FROM submissions s 
                 JOIN users u ON s.student_id = u.id 
                 JOIN assignments a ON s.assignment_id = a.id 
                 WHERE a.created_by = {$user['id']}";
    } elseif ($user['role'] === 'mahasiswa') {
        $query = "SELECT s.*, a.title, a.deadline FROM submissions s 
                 JOIN assignments a ON s.assignment_id = a.id 
                 WHERE s.student_id = {$user['id']}";
    }
    
    if ($assignment_id > 0) {
        $query .= " AND s.assignment_id = $assignment_id";
    }
    
    $query .= " ORDER BY s.submitted_at DESC";
    
    $result = $conn->query($query);
    $submissions = [];
    while ($row = $result->fetch_assoc()) {
        $submissions[] = $row;
    }
    
    sendResponse(true, 'Submissions retrieved', $submissions);
}

function submitAssignment() {
    global $conn, $auth;
    
    if (!$auth->hasRole('mahasiswa')) {
        sendResponse(false, 'Unauthorized');
    }
    
    $assignment_id = intval($_POST['assignment_id'] ?? 0);
    $notes = sanitize($_POST['notes'] ?? '');
    $student_id = $_SESSION['user_id'];
    $submitted_at = date('Y-m-d H:i:s');
    
    // Handle file upload
    $file_path = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file_path = 'uploads/' . time() . '_' . basename($_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'], UPLOAD_DIR . basename($file_path));
    }
    
    $query = "INSERT INTO submissions (assignment_id, student_id, file_path, submission_notes, submitted_at) 
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE file_path = ?, submission_notes = ?, submitted_at = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iissssss", $assignment_id, $student_id, $file_path, $notes, $submitted_at, 
                     $file_path, $notes, $submitted_at);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Assignment submitted successfully', ['id' => $conn->insert_id]);
    } else {
        sendResponse(false, 'Error submitting assignment: ' . $conn->error);
    }
}

function gradeSubmission() {
    global $conn, $auth;
    
    if (!$auth->hasRole('dosen')) {
        sendResponse(false, 'Unauthorized');
    }
    
    $submission_id = intval($_POST['submission_id'] ?? 0);
    $grade = intval($_POST['grade'] ?? 0);
    $feedback = sanitize($_POST['feedback'] ?? '');
    $grader_id = $_SESSION['user_id'];
    $graded_at = date('Y-m-d H:i:s');
    
    if ($grade < 0 || $grade > 100) {
        sendResponse(false, 'Grade must be between 0 and 100');
    }
    
    $query = "UPDATE submissions SET grade = ?, feedback = ?, graded_by = ?, graded_at = ? 
             WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isisi", $grade, $feedback, $grader_id, $graded_at, $submission_id);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Grade recorded successfully');
    } else {
        sendResponse(false, 'Error recording grade: ' . $conn->error);
    }
}

// =================== Attendance Functions ===================
function getAttendance() {
    global $conn, $auth;
    
    $user = $auth->getCurrentUser();
    $class_id = intval($_POST['class_id'] ?? 0);
    
    if ($user['role'] === 'dosen') {
        $query = "SELECT a.*, u.first_name, u.last_name FROM attendance a 
                 JOIN users u ON a.student_id = u.id 
                 WHERE a.class_id = $class_id 
                 ORDER BY a.attendance_date DESC";
    } elseif ($user['role'] === 'mahasiswa') {
        $query = "SELECT * FROM attendance WHERE student_id = {$user['id']} 
                 ORDER BY attendance_date DESC";
    }
    
    $result = $conn->query($query);
    $attendance = [];
    while ($row = $result->fetch_assoc()) {
        $attendance[] = $row;
    }
    
    sendResponse(true, 'Attendance retrieved', $attendance);
}

function markAttendance() {
    global $conn, $auth;
    
    if (!$auth->hasRole('mahasiswa')) {
        sendResponse(false, 'Unauthorized');
    }
    
    $student_id = $_SESSION['user_id'];
    $attendance_date = date('Y-m-d');
    
    // Find class for student
    $class_query = "SELECT DISTINCT class_id FROM class_enrollments WHERE student_id = $student_id LIMIT 1";
    $class_result = $conn->query($class_query)->fetch_assoc();
    
    if (!$class_result) {
        sendResponse(false, 'No class found for this student');
    }
    
    $class_id = $class_result['class_id'];
    
    $query = "INSERT INTO attendance (class_id, student_id, status, attendance_date) 
             VALUES (?, ?, 'hadir', ?)
             ON DUPLICATE KEY UPDATE status = 'hadir'";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $class_id, $attendance_date);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Attendance marked successfully');
    } else {
        sendResponse(false, 'Error marking attendance: ' . $conn->error);
    }
}

function recordAttendance() {
    global $conn, $auth;
    
    if (!$auth->hasRole('dosen')) {
        sendResponse(false, 'Unauthorized');
    }
    
    $class_id = intval($_POST['class_id'] ?? 0);
    $student_id = intval($_POST['student_id'] ?? 0);
    $status = sanitize($_POST['status'] ?? 'alpha');
    $attendance_date = sanitize($_POST['attendance_date'] ?? date('Y-m-d'));
    
    $query = "INSERT INTO attendance (class_id, student_id, status, attendance_date) 
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE status = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisss", $class_id, $student_id, $status, $attendance_date, $status);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Attendance recorded successfully');
    } else {
        sendResponse(false, 'Error recording attendance: ' . $conn->error);
    }
}

// =================== Announcements Functions ===================
function getAnnouncements() {
    global $conn, $auth;
    
    $user = $auth->getCurrentUser();
    $class_id = intval($_POST['class_id'] ?? 0);
    
    if ($user['role'] === 'dosen') {
        $query = "SELECT a.* FROM announcements a 
                 WHERE a.created_by = {$user['id']}";
    } elseif ($user['role'] === 'mahasiswa') {
        $query = "SELECT a.* FROM announcements a 
                 WHERE a.class_id IN (SELECT class_id FROM class_enrollments WHERE student_id = {$user['id']})";
    } else {
        $query = "SELECT a.* FROM announcements a";
    }
    
    if ($class_id > 0) {
        $query .= " AND a.class_id = $class_id";
    }
    
    $query .= " ORDER BY a.created_at DESC";
    
    $result = $conn->query($query);
    $announcements = [];
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    
    sendResponse(true, 'Announcements retrieved', $announcements);
}

function createAnnouncement() {
    global $conn, $auth;
    
    if (!$auth->hasRole('dosen')) {
        sendResponse(false, 'Unauthorized');
    }
    
    $class_id = intval($_POST['class_id'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    if (empty($title) || empty($content)) {
        sendResponse(false, 'Title and content are required');
    }
    
    $query = "INSERT INTO announcements (class_id, title, content, created_by) 
             VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issi", $class_id, $title, $content, $user_id);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Announcement created successfully', ['id' => $conn->insert_id]);
    } else {
        sendResponse(false, 'Error creating announcement: ' . $conn->error);
    }
}

// =================== Grades Functions ===================
function getGrades() {
    global $conn, $auth;
    
    $user = $auth->getCurrentUser();
    
    if ($user['role'] === 'mahasiswa') {
        $query = "SELECT s.grade, s.feedback, a.title FROM submissions s 
                 JOIN assignments a ON s.assignment_id = a.id 
                 WHERE s.student_id = {$user['id']} AND s.grade IS NOT NULL 
                 ORDER BY s.graded_at DESC";
    } elseif ($user['role'] === 'dosen') {
        $query = "SELECT s.*, a.title, u.first_name, u.last_name FROM submissions s 
                 JOIN assignments a ON s.assignment_id = a.id 
                 JOIN users u ON s.student_id = u.id 
                 WHERE a.created_by = {$user['id']} 
                 ORDER BY s.graded_at DESC";
    }
    
    $result = $conn->query($query);
    $grades = [];
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }
    
    sendResponse(true, 'Grades retrieved', $grades);
}

// =================== User Management (Admin) ===================
function getUsers() {
    global $conn, $auth;
    
    if (!$auth->hasRole('admin')) {
        sendResponse(false, 'Unauthorized');
    }
    
    $query = "SELECT id, username, email, role, first_name, last_name, status FROM users 
             ORDER BY created_at DESC";
    
    $result = $conn->query($query);
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    sendResponse(true, 'Users retrieved', $users);
}

function createUser() {
    global $conn, $auth;
    
    if (!$auth->hasRole('admin')) {
        sendResponse(false, 'Unauthorized');
    }
    
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = sanitize($_POST['role'] ?? 'mahasiswa');
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    
    if (empty($username) || empty($email) || empty($password)) {
        sendResponse(false, 'Username, email, and password are required');
    }
    
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    $query = "INSERT INTO users (username, email, password, role, first_name, last_name) 
             VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss", $username, $email, $hashed_password, $role, $first_name, $last_name);
    
    if ($stmt->execute()) {
        sendResponse(true, 'User created successfully', ['id' => $conn->insert_id]);
    } else {
        if (strpos($conn->error, 'Duplicate entry') !== false) {
            sendResponse(false, 'Username or email already exists');
        }
        sendResponse(false, 'Error creating user: ' . $conn->error);
    }
}

function updateUser() {
    global $conn, $auth;
    
    if (!$auth->hasRole('admin')) {
        sendResponse(false, 'Unauthorized');
    }
    
    $user_id = intval($_POST['user_id'] ?? 0);
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $status = sanitize($_POST['status'] ?? 'active');
    
    $query = "UPDATE users SET first_name = ?, last_name = ?, status = ? WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $first_name, $last_name, $status, $user_id);
    
    if ($stmt->execute()) {
        sendResponse(true, 'User updated successfully');
    } else {
        sendResponse(false, 'Error updating user: ' . $conn->error);
    }
}

function deleteUser() {
    global $conn, $auth;
    
    if (!$auth->hasRole('admin')) {
        sendResponse(false, 'Unauthorized');
    }
    
    $user_id = intval($_POST['user_id'] ?? 0);
    
    // Prevent deleting yourself
    if ($user_id === $_SESSION['user_id']) {
        sendResponse(false, 'Cannot delete your own account');
    }
    
    $query = "DELETE FROM users WHERE id = ? AND role != 'admin'";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        sendResponse(true, 'User deleted successfully');
    } else {
        sendResponse(false, 'Error deleting user: ' . $conn->error);
    }
}
?>
