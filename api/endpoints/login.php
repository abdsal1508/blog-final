<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('error' => 'Method not allowed'));
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(array('error' => 'Email and password are required'));
    exit();
}

$authService = new AuthService();
$result = $authService->login($email, $password);

if ($result['success']) {
    echo json_encode(array(
        'success' => true,
        'user' => $result['user'],
        'message' => $result['message']
    ));
} else {
    http_response_code(401);
    echo json_encode(array(
        'success' => false,
        'message' => $result['message']
    ));
}
?>
