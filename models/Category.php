<?php
class Category {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getAll() {
        return $this->db->fetchAll(
            "SELECT * FROM categories WHERE status_del = 1 ORDER BY category_name"
        );
    }
    
    public function findById($id) {
        return $this->db->fetchOne(
            "SELECT * FROM categories WHERE category_id = ? AND status_del = 1",
            array($id)
        );
    }
    
    public function create($name, $description) {
        // Check if category name exists
        $existing = $this->db->fetchOne(
            "SELECT * FROM categories WHERE category_name = ?",
            array($name)
        );
        
        if ($existing) {
            return array('success' => false, 'message' => 'Category name already exists');
        }
        
        $this->db->query(
            "INSERT INTO categories (category_name, category_description) VALUES (?, ?)",
            array($name, $description)
        );
        
        return array('success' => true, 'message' => 'Category created successfully');
    }
    
    public function update($id, $name, $description) {
        // Check if category name exists (excluding current category)
        $existing = $this->db->fetchOne(
            "SELECT * FROM categories WHERE category_name = ? AND category_id != ?",
            array($name, $id)
        );
        
        if ($existing) {
            return array('success' => false, 'message' => 'Category name already exists');
        }
        
        $this->db->query(
            "UPDATE categories SET category_name = ?, category_description = ? WHERE category_id = ?",
            array($name, $description, $id)
        );
        
        return array('success' => true, 'message' => 'Category updated successfully');
    }
    
    public function delete($id) {
        // Check if category has posts
        $post_count = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM posts_details WHERE r_category_id = ? AND status_del = 1",
            array($id)
        );
        
        if ($post_count && $post_count['count'] > 0) {
            return array('success' => false, 'message' => 'Cannot delete category with existing posts');
        }
        
        $this->db->query(
            "UPDATE categories SET status_del = 0 WHERE category_id = ?",
            array($id)
        );
        
        return array('success' => true, 'message' => 'Category deleted successfully');
    }
    
    public function count() {
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM categories WHERE status_del = 1");
        return $result ? $result['count'] : 0;
    }
}
?>
