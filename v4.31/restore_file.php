<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['personel_id'])) {
        throw new Exception('Oturum açmanız gerekiyor.');
    }

    if (!isset($_POST['file_id']) || !is_numeric($_POST['file_id'])) {
        throw new Exception('Geçersiz dosya ID.');
    }

    $file_id = (int)$_POST['file_id'];

    // Dosya bilgilerini al
    $stmt = $db->prepare("SELECT file_path, file_name FROM files WHERE file_id = ? AND is_deleted = 1");
    $stmt->execute([$file_id]);
    $file = $stmt->fetch();

    if (!$file) {
        throw new Exception('Silinmiş dosya bulunamadı.');
    }

    // Dosyayı uploads klasörüne geri taşı
    $current_path = $file['file_path'];
    $new_path = str_replace('deleted/', 'uploads/', $current_path);

    // Dosyayı taşı
    if (file_exists($current_path)) {
        if (!rename($current_path, $new_path)) {
            throw new Exception('Dosya taşıma işlemi başarısız oldu.');
        }
    }

    // Veritabanını güncelle
    $stmt = $db->prepare("UPDATE files SET is_deleted = 0, file_path = ? WHERE file_id = ?");
    if (!$stmt->execute([$new_path, $file_id])) {
        // Eğer veritabanı güncellemesi başarısız olursa dosyayı geri taşı
        if (file_exists($new_path)) {
            rename($new_path, $current_path);
        }
        throw new Exception('Veritabanı güncellemesi başarısız oldu.');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Dosya başarıyla geri alındı.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 