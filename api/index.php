<?php
require_once '../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$headers = getallheaders();
$auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';
$api_key = str_replace('Bearer ', '', $auth_header);

if ($api_key !== API_KEY) {
    http_response_code(401);
    echo json_encode(array('error' => 'Unauthorized'));
    exit();
}

$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/api/';
$path = parse_url($request_uri, PHP_URL_PATH);
$path = str_replace($base_path, '', $path);
$path = trim($path, '/');

$method = $_SERVER['REQUEST_METHOD'];
$params = $_GET;
$data = array();

if ($method === 'POST' || $method === 'PUT') {
    $body = file_get_contents('php://input');
    if (!empty($body)) {
        $data = json_decode($body, true);
    }
}

$db = new Database();
$response = array();

if ($path === 'posts') {
    switch ($method) {
        case 'GET':
            $page = isset($params['page']) ? max(1, intval($params['page'])) : 1;
            $per_page = isset($params['per_page']) ? min(50, max(1, intval($params['per_page']))) : 10;
            $offset = ($page - 1) * $per_page;

            $sql = "SELECT p.*, c.category_name, u.user_name as author_name 
                    FROM posts_details p 
                    LEFT JOIN categories c ON p.r_category_id = c.category_id 
                    LEFT JOIN user_details u ON p.r_author_id = u.user_id 
                    WHERE p.status_del = 1";
            $query_params = array();

            if (!isset($params['include_drafts'])) {
                $sql .= " AND p.post_status = 'published'";
            }

            $sql .= " ORDER BY p.create_time DESC LIMIT ? OFFSET ?";
            $query_params[] = $per_page;
            $query_params[] = $offset;

            $posts = $db->fetchAll($sql, $query_params);

            foreach ($posts as &$post) {
                if (!empty($post['image_link'])) {
                    if (strpos($post['image_link'], UPLOAD_DIR) !== 0) {
                        $post['image_link'] = UPLOAD_DIR . $post['image_link'];
                    }
                }
            }

            $response = array(
                'posts' => $posts,
                'page' => $page,
                'per_page' => $per_page
            );
            break;

        case 'POST':
            if (empty($data['title']) || empty($data['content']) || empty($data['category'])) {
                http_response_code(400);
                echo json_encode(array('error' => 'Missing required fields'));
                exit();
            }

            $result = $db->query(
                "INSERT INTO posts_details (title, content, r_category_id, r_author_id, post_status, create_time, update_time, status_del) 
                 VALUES (?, ?, ?, ?, ?, NOW(), NOW(), 1)",
                array($data['title'], $data['content'], $data['category'], $data['author_id'], $data['post_status'] ?? 'draft')
            );

            if ($result) {
                $response = array(
                    'success' => true,
                    'post_id' => $db->lastInsertId()
                );
            } else {
                http_response_code(500);
                $response = array('error' => 'Failed to create post');
            }
            break;

        default:
            http_response_code(405);
            $response = array('error' => 'Method not allowed');
            break;
    }
} else {
    http_response_code(404);
    $response = array('error' => 'Endpoint not found');
}

echo json_encode($response);
?>
