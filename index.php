<?php
require_once 'config/config.php';

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    // Redirect based on user role
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: views/admin/admin.php');
    } elseif ($_SESSION['user_role'] === 'author') {
        header('Location: views/dashboard/user_dashboard.php');
    } else {
        header('Location: views/public/index.php');
    }
} else {
    // Redirect to public homepage
    header('Location: views/public/index.php');
}
exit();
?>
