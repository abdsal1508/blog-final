<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('error' => 'Method not allowed'));
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (empty($username) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(array('error' => 'All fields are required'));
    exit();
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(array('error' => 'Password must be at least 6 characters long'));
    exit();
}

$authService = new AuthService();
$result = $authService->register($username, $email, $password);

if ($result['success']) {
    echo json_encode(array(
        'success' => true,
        'message' => $result['message']
    ));
} else {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'message' => $result['message']
    ));
}
?>
