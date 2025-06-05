<?php
class Post {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function findById($id) {
        return $this->db->fetchOne(
            "SELECT p.*, c.category_name, u.user_name as author_name 
             FROM posts_details p 
             LEFT JOIN categories c ON p.r_category_id = c.category_id 
             LEFT JOIN user_details u ON p.r_author_id = u.user_id 
             WHERE p.post_id = ? AND p.status_del = 1",
            array($id)
        );
    }
    
    public function getAll($options = array()) {
        $sql = "SELECT p.*, c.category_name, u.user_name as author_name 
                FROM posts_details p 
                LEFT JOIN categories c ON p.r_category_id = c.category_id 
                LEFT JOIN user_details u ON p.r_author_id = u.user_id 
                WHERE p.status_del = 1";
        $params = array();
        
        if (isset($options['status'])) {
            $sql .= " AND p.post_status = ?";
            $params[] = $options['status'];
        }
        
        if (isset($options['category_id'])) {
            $sql .= " AND p.r_category_id = ?";
            $params[] = $options['category_id'];
        }
        
        if (isset($options['author_id'])) {
            $sql .= " AND p.r_author_id = ?";
            $params[] = $options['author_id'];
        }
        
        if (isset($options['search'])) {
            $sql .= " AND (p.title LIKE ? OR p.content LIKE ?)";
            $search = '%' . $options['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql .= " ORDER BY p.create_time DESC";
        
        if (isset($options['limit'])) {
            $sql .= " LIMIT " . intval($options['limit']);
            if (isset($options['offset'])) {
                $sql .= " OFFSET " . intval($options['offset']);
            }
        }
        
        $posts = $this->db->fetchAll($sql, $params);
        
        // Fix image paths
        foreach ($posts as &$post) {
            if (!empty($post['image_link'])) {
                if (strpos($post['image_link'], UPLOAD_DIR) !== 0) {
                    $post['image_link'] = UPLOAD_DIR . $post['image_link'];
                }
            }
        }
        
        return $posts;
    }
    
    public function create($data, $files = array()) {
        $title = $data['title'];
        $content = $data['content'];
        $category_id = $data['category'];
        $status = isset($data['post_status']) ? $data['post_status'] : 'draft';
        $author_id = $_SESSION['user_id'];
        
        $image_filename = null;
        if (isset($files['image_upload']) && $files['image_upload']['error'] === UPLOAD_ERR_OK) {
            $upload_result = $this->uploadImage($files['image_upload']);
            if ($upload_result['success']) {
                $image_filename = $upload_result['filename'];
            }
        }
        
        $this->db->query(
            "INSERT INTO posts_details (title, content, r_category_id, r_author_id, post_status, image_link, create_time, update_time, status_del) 
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), 1)",
            array($title, $content, $category_id, $author_id, $status, $image_filename)
        );
        
        return array('success' => true, 'message' => 'Post created successfully');
    }
    
    public function update($data, $files = array()) {
        $post_id = $data['post_id'];
        $title = $data['title'];
        $content = $data['content'];
        $category_id = $data['category'];
        $status = isset($data['post_status']) ? $data['post_status'] : 'draft';
        
        $current_post = $this->findById($post_id);
        if (!$current_post) {
            return array('success' => false, 'message' => 'Post not found');
        }
        
        $image_filename = $current_post['image_link'];
        if (!empty($image_filename) && strpos($image_filename, UPLOAD_DIR) === 0) {
            $image_filename = str_replace(UPLOAD_DIR, '', $image_filename);
        }
        
        if (isset($files['image_upload']) && $files['image_upload']['error'] === UPLOAD_ERR_OK) {
            $upload_result = $this->uploadImage($files['image_upload']);
            if ($upload_result['success']) {
                if (!empty($image_filename) && file_exists(UPLOAD_DIR . $image_filename)) {
                    unlink(UPLOAD_DIR . $image_filename);
                }
                $image_filename = $upload_result['filename'];
            }
        }
        
        $this->db->query(
            "UPDATE posts_details 
             SET title = ?, content = ?, r_category_id = ?, post_status = ?, image_link = ?, update_time = NOW() 
             WHERE post_id = ?",
            array($title, $content, $category_id, $status, $image_filename, $post_id)
        );
        
        return array('success' => true, 'message' => 'Post updated successfully');
    }
    
    public function delete($id) {
        $this->db->query(
            "UPDATE posts_details SET status_del = 0 WHERE post_id = ?",
            array($id)
        );
        
        return array('success' => true, 'message' => 'Post deleted successfully');
    }
    
    public function count($options = array()) {
        $sql = "SELECT COUNT(*) as count FROM posts_details WHERE status_del = 1";
        $params = array();
        
        if (isset($options['status'])) {
            $sql .= " AND post_status = ?";
            $params[] = $options['status'];
        }
        
        if (isset($options['category_id'])) {
            $sql .= " AND r_category_id = ?";
            $params[] = $options['category_id'];
        }
        
        if (isset($options['author_id'])) {
            $sql .= " AND r_author_id = ?";
            $params[] = $options['author_id'];
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result ? $result['count'] : 0;
    }
    
    private function uploadImage($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return array('success' => false, 'message' => 'Upload error');
        }
        
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/avif');
        if (!in_array($file['type'], $allowed_types)) {
            return array('success' => false, 'message' => 'Invalid file type');
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            return array('success' => false, 'message' => 'File too large');
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . uniqid() . '.' . $extension;
        $filepath = UPLOAD_DIR . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return array(
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath
            );
        }
        
        return array('success' => false, 'message' => 'Failed to upload file');
    }
}
?>
