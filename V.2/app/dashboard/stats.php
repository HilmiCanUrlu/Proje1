<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/database.php';

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    $conn = Database::getInstance();

    // Toplam aktif kullanıcı sayısı
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
    $stmt->execute();
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Son 30 dakika içindeki aktif oturumlar
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT user_id) as total 
        FROM sessions 
        WHERE last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
    ");
    $stmt->execute();
    $active_sessions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Toplam aktivite sayısı
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM activity_log");
    $stmt->execute();
    $total_transactions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'success' => true,
        'data' => [
            'total_users' => $total_users,
            'active_sessions' => $active_sessions,
            'total_transactions' => $total_transactions,
            'system_status' => 'Aktif'
        ]
    ]);

} catch(PDOException $e) {
    error_log("Stats error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
} 