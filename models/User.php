<?php
class User {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function findByUsername($username) {
        return $this->db->fetchOne(
            "SELECT * FROM user_details WHERE user_name = ? AND access = 1",
            array($username)
        );
    }
    
    public function findByEmail($email) {
        return $this->db->fetchOne(
            "SELECT * FROM user_details WHERE user_email = ? AND access = 1",
            array($email)
        );
    }
    
    public function findById($id) {
        return $this->db->fetchOne(
            "SELECT * FROM user_details WHERE user_id = ? AND access = 1",
            array($id)
        );
    }
    
    public function create($username, $email, $password, $role = 'end_user') {
        // Check if username exists
        if ($this->findByUsername($username)) {
            return array('success' => false, 'message' => 'Username already exists');
        }
        
        // Check if email exists
        if ($this->findByEmail($email)) {
            return array('success' => false, 'message' => 'Email already exists');
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $this->db->query(
            "INSERT INTO user_details (user_name, user_email, user_password, user_role, access) VALUES (?, ?, ?, ?, 1)",
            array($username, $email, $hashed_password, $role)
        );
        
        return array('success' => true, 'message' => 'User created successfully');
    }
    
    public function getAll($options = array()) {
        $sql = "SELECT * FROM user_details WHERE access = 1";
        $params = array();
        
        if (isset($options['role'])) {
            $sql .= " AND user_role = ?";
            $params[] = $options['role'];
        }
        
        $sql .= " ORDER BY user_id DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function update($id, $username, $email, $role) {
        $this->db->query(
            "UPDATE user_details SET user_name = ?, user_email = ?, user_role = ? WHERE user_id = ?",
            array($username, $email, $role, $id)
        );
        
        return array('success' => true, 'message' => 'User updated successfully');
    }
    
    public function delete($id) {
        $this->db->query(
            "UPDATE user_details SET access = 0 WHERE user_id = ?",
            array($id)
        );
        
        return array('success' => true, 'message' => 'User deleted successfully');
    }
    
    public function count($options = array()) {
        $sql = "SELECT COUNT(*) as count FROM user_details WHERE access = 1";
        $params = array();
        
        if (isset($options['role'])) {
            $sql .= " AND user_role = ?";
            $params[] = $options['role'];
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result ? $result['count'] : 0;
    }
}
?>
