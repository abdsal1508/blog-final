<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(array('error' => 'Method not allowed'));
    exit();
}

$postService = new PostService();

$options = array();

if (isset($_GET['status'])) {
    $options['status'] = $_GET['status'];
}

if (isset($_GET['category_id'])) {
    $options['category_id'] = $_GET['category_id'];
}

if (isset($_GET['author_id'])) {
    $options['author_id'] = $_GET['author_id'];
}

if (isset($_GET['search'])) {
    $options['search'] = $_GET['search'];
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = isset($_GET['per_page']) ? min(50, max(1, intval($_GET['per_page']))) : 10;
$options['limit'] = $per_page;
$options['offset'] = ($page - 1) * $per_page;

$result = $postService->getAllPosts($options);

echo json_encode(array(
    'success' => true,
    'posts' => $result['posts'],
    'total' => $result['total'],
    'page' => $page,
    'per_page' => $per_page
));
?>
