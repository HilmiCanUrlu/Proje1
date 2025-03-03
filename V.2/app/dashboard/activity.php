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
    
    // Son 10 aktiviteyi getir
    $stmt = $conn->prepare("
        SELECT 
            u.username,
            al.action,
            DATE_FORMAT(al.created_at, '%Y-%m-%d %H:%i') as created_at,
            al.status
        FROM activity_log al
        JOIN users u ON al.user_id = u.id
        WHERE u.status = 'active'
        ORDER BY al.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Status sınıflarını belirle
    foreach($activities as &$activity) {
        $activity['status_class'] = match($activity['status']) {
            'success' => 'success',
            'warning' => 'warning',
            'error' => 'danger',
            default => 'info'
        };
    }

    echo json_encode([
        'success' => true,
        'data' => $activities
    ]);

} catch(PDOException $e) {
    error_log("Activity error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
} 