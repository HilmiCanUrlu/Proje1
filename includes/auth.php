<?php
class Auth {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function login($username, $password) {
        try {
            $query = "SELECT id, username, password, role FROM users 
                     WHERE username = :username AND status = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            
            if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if(password_verify($password, $row['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['role'] = $row['role'];
                    $_SESSION['last_activity'] = time();
                    
                    // Update last login
                    $this->updateLastLogin($row['id']);
                    return true;
                }
            }
            return false;
        } catch(PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
    
    private function updateLastLogin($userId) {
        $query = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $userId);
        $stmt->execute();
    }
    
    public function isLoggedIn() {
        if(isset($_SESSION['user_id']) && 
           (time() - $_SESSION['last_activity']) < SESSION_TIMEOUT) {
            $_SESSION['last_activity'] = time();
            return true;
        }
        return false;
    }
    
    public function hasPermission($requiredRole) {
        $roleHierarchy = [
            'admin' => 3,
            'manager' => 2,
            'user' => 1
        ];
        
        return $roleHierarchy[$_SESSION['role']] >= $roleHierarchy[$requiredRole];
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
}
?> 