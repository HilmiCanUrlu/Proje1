<?php
session_start();
require_once 'database.php';

header('Content-Type: application/json');

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // İlk olarak is_deleted kolonunu kontrol et ve yoksa ekle
    try {
        $checkColumn = $db->query("SHOW COLUMNS FROM files LIKE 'is_deleted'");
        if ($checkColumn->rowCount() == 0) {
            $db->exec("ALTER TABLE files ADD COLUMN is_deleted TINYINT(1) DEFAULT 0");
        }
    } catch (PDOException $e) {
        // Kolon ekleme hatası olursa yoksay ve devam et
        error_log("Column check/add error: " . $e->getMessage());
    }

    if (!isset($_SESSION['personel_id'])) {
        throw new Exception('Oturum açmanız gerekiyor.');
    }

    if (!isset($_POST['file_id']) || !is_numeric($_POST['file_id'])) {
        throw new Exception('Geçersiz dosya ID.');
    }

    $file_id = (int)$_POST['file_id'];

    // Dosya bilgilerini al
    $stmt = $db->prepare("SELECT file_path, file_name FROM files WHERE file_id = ?");
    $stmt->execute([$file_id]);
    $file = $stmt->fetch();

    if (!$file) {
        throw new Exception('Dosya bulunamadı.');
    }

    // Dosyayı deleted klasörüne taşı
    $current_path = $file['file_path'];
    $new_path = str_replace('uploads/', 'deleted/', $current_path);

    // Deleted klasörünü oluştur (yoksa)
    $deleted_dir = dirname($new_path);
    if (!file_exists($deleted_dir)) {
        mkdir($deleted_dir, 0755, true);
    }

    // Dosyayı taşı
    if (file_exists($current_path)) {
        if (!rename($current_path, $new_path)) {
            throw new Exception('Dosya taşıma işlemi başarısız oldu.');
        }
    }

    // Veritabanını güncelle
    $stmt = $db->prepare("UPDATE files SET is_deleted = 1, file_path = ? WHERE file_id = ?");
    if (!$stmt->execute([$new_path, $file_id])) {
        // Eğer veritabanı güncellemesi başarısız olursa dosyayı geri taşı
        if (file_exists($new_path)) {
            rename($new_path, $current_path);
        }
        throw new Exception('Veritabanı güncellemesi başarısız oldu.');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Dosya başarıyla silindi.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 