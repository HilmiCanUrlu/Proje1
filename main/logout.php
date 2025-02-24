<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Kullanıcının session kaydını sil
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("DELETE FROM sessions WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);

        // Aktivite loguna kaydet
        $stmt = $conn->prepare("INSERT INTO activity_log (user_id, action, status) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], 'Çıkış yapıldı', 'info']);
    }

    // Session'ı temizle
    session_unset();
    session_destroy();
    
    // Çerezleri temizle
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }

    // Login sayfasına yönlendir
    header('Location: index.php');
    exit;
    
} catch(PDOException $e) {
    error_log("Logout error: " . $e->getMessage());
    header('Location: index.php');
    exit;
} 