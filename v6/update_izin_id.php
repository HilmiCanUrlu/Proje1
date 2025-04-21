<?php
session_start();
require_once "database.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

try {
    if (isset($_POST['dosya_id']) && isset($_POST['personel_id'])) {
        $query = "UPDATE dosyalar SET izin_id = :izin_id WHERE dosya_id = :dosya_id";
        $stmt = $db->prepare($query);
        $result = $stmt->execute([
            ':dosya_id' => $_POST['dosya_id'],
            ':izin_id' => $_POST['personel_id']
        ]);
        
        header('Content-Type: application/json');
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Kıdemli personel atanamadı.']);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Gerekli parametreler eksik.']);
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 