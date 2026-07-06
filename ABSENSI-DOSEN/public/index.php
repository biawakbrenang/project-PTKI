<?php
session_start();

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/models/Auth.php';
require_once APP_ROOT . '/models/Attendance.php';
require_once APP_ROOT . '/models/Dashboard.php';
require_once APP_ROOT . '/models/Student.php';

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: {$url}");
    exit();
}

function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

$page = $_GET['page'] ?? 'dashboard';
$allowedPages = ['login', 'logout', 'dashboard', 'absensi', 'rekap', 'mahasiswa'];
$page = in_array($page, $allowedPages, true) ? $page : '404';

if (!$auth->isLoggedIn() && $page != 'login') {
    redirect("index.php?page=login");
}

if ($auth->isLoggedIn() && $page === 'login') {
    redirect("index.php?page=dashboard");
}

switch ($page) {
    case 'login':
        include APP_ROOT . '/views/login.php';
        break;
    case 'logout':
        $auth->logout();
        redirect("index.php?page=login");
        break;
    case 'dashboard':
        $dashboard = new Dashboard($db);
        $stats = $dashboard->getStats($_SESSION['user_id']);
        $todaySchedules = $dashboard->getTodaySchedules($_SESSION['user_id']);
        include APP_ROOT . '/views/dashboard.php';
        break;
    case 'absensi':
        $attendance = new Attendance($db);
        $courses = $attendance->getCoursesByDosen($_SESSION['user_id']);
        include APP_ROOT . '/views/absensi.php';
        break;
    case 'rekap':
        $attendance = new Attendance($db);
        $courses = $attendance->getCoursesByDosen($_SESSION['user_id']);
        include APP_ROOT . '/views/rekap.php';
        break;
    case 'mahasiswa':
        $studentModel = new Student($db);
        $attendance = new Attendance($db);
        $courses = $attendance->getCoursesByDosen($_SESSION['user_id']);
        include APP_ROOT . '/views/mahasiswa.php';
        break;
    default:
        http_response_code(404);
        include APP_ROOT . '/views/404.php';
        break;
}
?>
