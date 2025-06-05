<?php
class AuthService {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function login($username_or_email, $password) {
        // Try to find user by username or email
        $user = $this->userModel->findByUsername($username_or_email);
        if (!$user) {
            $user = $this->userModel->findByEmail($username_or_email);
        }
        
        if (!$user) {
            return array('success' => false, 'message' => 'Invalid username/email or password');
        }
        
        // Check password
        if (password_verify($password, $user['user_password']) || $password === $user['user_password']) {
            // Login successful
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['user_name'];
            $_SESSION['user_role'] = $user['user_role'];
            $_SESSION['user_email'] = $user['user_email'];
            
            return array('success' => true, 'user' => $user, 'message' => 'Login successful');
        }
        
        return array('success' => false, 'message' => 'Invalid username/email or password');
    }
    
    public function register($username, $email, $password) {
        return $this->userModel->create($username, $email, $password, 'end_user');
    }
    
    public function logout() {
        session_destroy();
        $_SESSION = array();
    }
}
?>
