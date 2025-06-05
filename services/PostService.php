<?php
class PostService {
    private $postModel;
    
    public function __construct() {
        $this->postModel = new Post();
    }
    
    public function getAllPosts($options = array()) {
        $posts = $this->postModel->getAll($options);
        $total = $this->postModel->count($options);
        
        return array(
            'posts' => $posts,
            'total' => $total
        );
    }
    
    public function getPost($id) {
        return $this->postModel->findById($id);
    }
    
    public function createPost($data, $files = array()) {
        return $this->postModel->create($data, $files);
    }
    
    public function updatePost($data, $files = array()) {
        return $this->postModel->update($data, $files);
    }
    
    public function deletePost($id) {
        return $this->postModel->delete($id);
    }
    
    public function getFeaturedPost() {
        $posts = $this->postModel->getAll(array('status' => 'published', 'limit' => 1));
        return !empty($posts) ? $posts[0] : null;
    }
    
    public function canEdit($post_id, $user_id) {
        if (empty($user_id)) {
            return false;
        }
        
        $post = $this->postModel->findById($post_id);
        return $post && ($post['r_author_id'] == $user_id || is_admin());
    }
    
    public function countPosts($options = array()) {
        return $this->postModel->count($options);
    }
}
?>
