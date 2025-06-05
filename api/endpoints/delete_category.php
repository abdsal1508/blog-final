<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('error' => 'Method not allowed'));
    exit();
}

if (!is_admin()) {
    http_response_code(403);
    echo json_encode(array('error' => 'Admin access required'));
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$category_id = $input['category_id'] ?? 0;

if (empty($category_id)) {
    http_response_code(400);
    echo json_encode(array('error' => 'Category ID is required'));
    exit();
}

$categoryService = new CategoryService();
$result = $categoryService->deleteCategory($category_id);

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
