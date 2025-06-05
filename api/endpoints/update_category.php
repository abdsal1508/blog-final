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
$name = $input['name'] ?? '';
$description = $input['description'] ?? '';

if (empty($category_id) || empty($name)) {
    http_response_code(400);
    echo json_encode(array('error' => 'Category ID and name are required'));
    exit();
}

$categoryService = new CategoryService();
$result = $categoryService->updateCategory($category_id, $name, $description);

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
