<?php
require_once '../../config/config.php';

$authService = new AuthService();
$authService->logout();

if (isset($_SERVER['HTTP_REFERER'])) {
    redirect($_SERVER['HTTP_REFERER']);
} else {
    redirect('../views/auth/login.php');
}
?>
