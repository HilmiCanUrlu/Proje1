<?php
// Namespace kullanmadığımızdan emin olalım
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth {
    private $conn;
    private $user = null;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function login($username, $password) {
        try {
            // Önce kullanıcıyı bulalım
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :username AND status = 'active'");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Kullanıcı bulundu ve şifre doğru ise
            if ($user && password_verify($password, $user['password'])) {
                $this->user = $user;
                
                // Session'ı veritabanına kaydet
                $stmt = $this->conn->prepare("
                    INSERT INTO sessions (user_id, session_id) 
                    VALUES (:user_id, :session_id)
                    ON DUPLICATE KEY UPDATE last_activity = CURRENT_TIMESTAMP
                ");
                $stmt->execute([
                    'user_id' => $user['id'],
                    'session_id' => session_id()
                ]);
                
                // Son giriş zamanını güncelle
                $updateStmt = $this->conn->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                $updateStmt->bindParam(':id', $user['id']);
                $updateStmt->execute();
                
                return true;
            }
            
            return false;
            
        } catch(PDOException $e) {
            error_log("Login hatası: " . $e->getMessage());
            return false;
        }
    }

    public function getUser() {
        return $this->user;
    }

    public function hashPassword($password) {
        // Argon2id ile şifreleme
        $options = [
            'memory_cost' => 1024 * 64,  // 64MB
            'time_cost' => 4,            // 4 iterasyon
            'threads' => 3               // 3 paralel thread
        ];
        
        return password_hash($password, PASSWORD_ARGON2ID, $options);
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function checkPermission($action) {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $role = $_SESSION['role'] ?? '';
        $permissions = [
            'admin' => ['all'],
            'manager' => ['reports', 'users'],
            'user' => ['reports']
        ];

        return isset($permissions[$role]) && 
               (in_array($action, $permissions[$role]) || 
                in_array('all', $permissions[$role]));
    }

    public function logout() {
        try {
            if (isset($_SESSION['user_id'])) {
                // Session'ı veritabanından sil
                $stmt = $this->conn->prepare("DELETE FROM sessions WHERE user_id = :user_id AND session_id = :session_id");
                $stmt->execute([
                    'user_id' => $_SESSION['user_id'],
                    'session_id' => session_id()
                ]);

                // Aktivite loguna kaydet
                $stmt = $this->conn->prepare("INSERT INTO activity_log (user_id, action, status) VALUES (?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], 'Çıkış yapıldı', 'info']);
            }
            
            // PHP session'ı temizle
            session_destroy();
            return true;
        } catch(PDOException $e) {
            error_log("Logout error: " . $e->getMessage());
            return false;
        }
    }
} 