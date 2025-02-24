<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once 'auth.php';

try {
    $conn = Database::getInstance();
    $auth = new Auth($conn);

    if ($auth->logout()) {
        // Aktivite loguna kaydet
        $stmt = $conn->prepare("INSERT INTO activity_log (user_id, action, status) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], 'Çıkış yapıldı', 'info']);
        
        // Çerezleri temizle
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-3600, '/');
        }
    }

    // Session'ı temizle
    session_unset();
    session_destroy();

    // Login sayfasına yönlendir
    header('Location: login.php');
    exit;
    
} catch(PDOException $e) {
    error_log("Logout error: " . $e->getMessage());
    header('Location: login.php');
    exit;
} 