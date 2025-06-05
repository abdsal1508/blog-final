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

$input = json_decode(file_get_contents('php://input'), true);
$post_id = $input['post_id'] ?? 0;

if (empty($post_id)) {
    http_response_code(400);
    echo json_encode(array('error' => 'Post ID is required'));
    exit();
}

$postService = new PostService();

if (!$postService->canEdit($post_id, $_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(array('error' => 'Permission denied'));
    exit();
}

$result = $postService->deletePost($post_id);

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
