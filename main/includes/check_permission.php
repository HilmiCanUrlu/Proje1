<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Kullanıcı rolüne göre izinleri kontrol et
$allowed = false;
$action = $_POST['action'] ?? '';

if (isset($_SESSION['role'])) {
    switch($_SESSION['role']) {
        case 'admin':
            $allowed = true; // Admin her şeyi yapabilir
            break;
        case 'manager':
            $allowed = in_array($action, ['reports', 'users']); // Manager sadece raporları ve kullanıcıları görebilir
            break;
        case 'user':
            $allowed = $action === 'reports'; // Normal kullanıcı sadece raporları görebilir
            break;
    }
}

echo json_encode(['allowed' => $allowed]); 