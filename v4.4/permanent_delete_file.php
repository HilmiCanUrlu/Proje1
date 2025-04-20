<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['personel_id'])) {
        throw new Exception('Oturum açmanız gerekiyor.');
    }

    if ($_SESSION['personel_id'] != 1) {
        throw new Exception('Bu işlem için yetkiniz yok.');
    }

    if (!isset($_POST['file_id']) || !is_numeric($_POST['file_id'])) {
        throw new Exception('Geçersiz dosya ID.');
    }

    $file_id = (int)$_POST['file_id'];

    // Dosya bilgilerini al
    $stmt = $db->prepare("SELECT file_path FROM files WHERE file_id = ? AND is_deleted = 1");
    $stmt->execute([$file_id]);
    $file = $stmt->fetch();

    if (!$file) {
        throw new Exception('Silinmiş dosya bulunamadı.');
    }

    // Fiziksel dosyayı sil
    if (file_exists($file['file_path'])) {
        if (!unlink($file['file_path'])) {
            throw new Exception('Dosya silinirken bir hata oluştu.');
        }
    }

    // Veritabanından kaydı sil
    $stmt = $db->prepare("DELETE FROM files WHERE file_id = ?");
    if (!$stmt->execute([$file_id])) {
        throw new Exception('Veritabanı kaydı silinemedi.');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Dosya kalıcı olarak silindi.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 