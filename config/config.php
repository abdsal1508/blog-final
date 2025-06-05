<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '0000');
define('DB_NAME', 'blog_db');

// Site configuration
define('SITE_NAME', 'Simply Blogs');
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST']);
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_DIR', 'assets/uploads/posts/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB

// API configuration
define('API_BASE_URL', 'http://localhost/final-end/api/');
define('API_KEY', 'sb_blog_2024_api_key');

// Include core files
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/Post.php';
require_once BASE_PATH . '/models/Category.php';
require_once BASE_PATH . '/services/AuthService.php';
require_once BASE_PATH . '/services/PostService.php';
require_once BASE_PATH . '/services/CategoryService.php';

// Helper functions
function redirect($url) {
    header("Location: $url");
    exit();
}

function html_escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function format_date($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = array(
        'type' => $type,
        'message' => $message
    );
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function is_author() {
    return isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'author' || $_SESSION['user_role'] === 'admin');
}

function require_login() {
    if (!is_logged_in()) {
        set_flash_message('error', 'Please log in to access this page');
        redirect('/views/auth/login.php');
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        set_flash_message('error', 'You do not have permission to access this page');
        redirect('/views/public/index.php');
    }
}

function require_author() {
    require_login();
    if (!is_author()) {
        set_flash_message('error', 'You do not have permission to access this page');
        redirect('/views/public/index.php');
    }
}

// Create upload directory if it doesn't exist
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
?>
