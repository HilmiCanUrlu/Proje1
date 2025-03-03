<?php
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

    $stmt = $conn->prepare("
        SELECT 
            u.username,
            al.action,
            DATE_FORMAT(al.created_at, '%Y-%m-%d %H:%i') as created_at,
            al.status
        FROM activity_log al
        JOIN users u ON al.user_id = u.id
        ORDER BY al.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Status sınıflarını belirle
    foreach($activities as &$activity) {
        switch($activity['status']) {
            case 'success':
                $activity['status_class'] = 'success';
                break;
            case 'warning':
                $activity['status_class'] = 'warning';
                break;
            case 'error':
                $activity['status_class'] = 'danger';
                break;
            default:
                $activity['status_class'] = 'info';
        }
    }

    echo json_encode($activities);

} catch(PDOException $e) {
    error_log("Activity log error: " . $e->getMessage());
    echo json_encode(['error' => 'Veri çekme hatası']);
} 