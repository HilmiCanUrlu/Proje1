<?php
// Namespace kullanmadığımızdan emin olalım
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth {
    private $conn;
    
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
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                
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

    public function hashPassword($password) {
        // Argon2id ile şifreleme
        $options = [
            'memory_cost' => 1024 * 64,  // 64MB
            'time_cost' => 4,            // 4 iterasyon
            'threads' => 3               // 3 paralel thread
        ];
        
        return password_hash($password, PASSWORD_ARGON2ID, $options);
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