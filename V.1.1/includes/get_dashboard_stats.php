<?php
session_start();
require_once 'config.php';
require_once 'database.php';

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Oturum bulunamadı']);
    exit;
}

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Toplam kullanıcı sayısı
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
    $stmt->execute();
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Aktif oturumlar
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM sessions 
        WHERE last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
    ");
    $stmt->execute();
    $active_sessions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Toplam işlem sayısı
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM activity_log");
    $stmt->execute();
    $total_transactions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'total_users' => $total_users,
        'active_sessions' => $active_sessions,
        'total_transactions' => $total_transactions,
        'system_status' => 'active'
    ]);

} catch(PDOException $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
    echo json_encode(['error' => 'Veri çekme hatası']);
} 