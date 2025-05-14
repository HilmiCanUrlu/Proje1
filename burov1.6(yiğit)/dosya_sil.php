<?php
session_start();
require_once "database.php";

// Oturum kontrolü
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Oturum açmanız gerekiyor']);
    exit;
}

// Dosya ID kontrolü
if (!isset($_POST['dosya_id']) || empty($_POST['dosya_id'])) {
    echo json_encode(['success' => false, 'message' => 'Dosya ID gerekli']);
    exit;
}

$dosya_id = $_POST['dosya_id'];

try {
    $database = new Database();
    $db = $database->getConnection();

    // Önce dosyanın var olup olmadığını kontrol et
    $check_query = "SELECT dosya_id FROM dosyalar WHERE dosya_id = :dosya_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':dosya_id', $dosya_id);
    $check_stmt->execute();

    if ($check_stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Dosya bulunamadı']);
        exit;
    }

    // İlişkili muhasebe kayıtlarını sil
    $muhasebe_query = "DELETE FROM muhasebe WHERE dosya_id = :dosya_id";
    $muhasebe_stmt = $db->prepare($muhasebe_query);
    $muhasebe_stmt->bindParam(':dosya_id', $dosya_id);
    $muhasebe_stmt->execute();

    // Dosyayı sil
    $delete_query = "DELETE FROM dosyalar WHERE dosya_id = :dosya_id";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->bindParam(':dosya_id', $dosya_id);
    
    if ($delete_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Dosya başarıyla silindi']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Dosya silinirken bir hata oluştu']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?> 