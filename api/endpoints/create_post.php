<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('error' => 'Method not allowed'));
    exit();
}

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(array('error' => 'Authentication required'));
    exit();
}

$postService = new PostService();
$result = $postService->createPost($_POST, $_FILES);

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
