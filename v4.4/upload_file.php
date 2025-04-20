<?php
// Çıktı tamponlamasını başlat
ob_start();

// Hata raporlamayı ayarla
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Tüm çıktıyı JSON olarak ayarla
header('Content-Type: application/json');

try {
    // Session başlat
    session_start();

    // Veritabanı bağlantısını kontrol et
    require_once 'db_connection.php';
    if (!isset($db) || !$db) {
        throw new Exception('Veritabanı bağlantısı kurulamadı.');
    }

    // Oturum kontrolü
    if (!isset($_SESSION['personel_id'])) {
        throw new Exception('Oturum açmanız gerekiyor.');
    }

    // Dosya ID kontrolü
    if (!isset($_POST['dosya_id']) || !is_numeric($_POST['dosya_id'])) {
        throw new Exception('Geçersiz dosya ID.');
    }

    // Dosya kontrolü
    if (!isset($_FILES['file']) || empty($_FILES['file']['name'])) {
        throw new Exception('Dosya seçilmedi.');
    }

    // Dosya yükleme hatası kontrolü
    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $message = match($_FILES['file']['error']) {
            UPLOAD_ERR_INI_SIZE => 'Dosya boyutu PHP yapılandırma limitini aşıyor.',
            UPLOAD_ERR_FORM_SIZE => 'Dosya boyutu form limitini aşıyor.',
            UPLOAD_ERR_PARTIAL => 'Dosya sadece kısmen yüklendi.',
            UPLOAD_ERR_NO_FILE => 'Dosya yüklenmedi.',
            UPLOAD_ERR_NO_TMP_DIR => 'Geçici klasör bulunamadı.',
            UPLOAD_ERR_CANT_WRITE => 'Dosya diske yazılamadı.',
            UPLOAD_ERR_EXTENSION => 'Bir PHP uzantısı dosya yüklemesini durdurdu.',
            default => 'Bilinmeyen bir hata oluştu.'
        };
        throw new Exception($message);
    }

    $dosya_id = (int)$_POST['dosya_id'];
    $file = $_FILES['file'];
    $personel_id = $_SESSION['personel_id'];

    // Upload klasörünü kontrol et ve oluştur
    $upload_dir = 'uploads';
    if (!file_exists($upload_dir)) {
        if (!@mkdir($upload_dir, 0755, true)) {
            throw new Exception('Upload klasörü oluşturulamadı.');
        }
    }

    // Klasör yazma izinlerini kontrol et
    if (!is_writable($upload_dir)) {
        throw new Exception('Upload klasörüne yazma izni yok.');
    }

    // Dosya bilgilerini hazırla
    $file_name = basename($file['name']); // Güvenli dosya adı
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $unique_filename = uniqid() . '.' . $file_extension;
    $file_path = $upload_dir . '/' . $unique_filename;

    // Dosyayı yükle
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        throw new Exception('Dosya yüklenirken bir hata oluştu.');
    }

    // Dosyanın başarıyla yüklendiğini kontrol et
    if (!file_exists($file_path)) {
        throw new Exception('Dosya sunucuya yüklenemedi.');
    }

    try {
        // Veritabanına kaydet
        $stmt = $db->prepare("INSERT INTO files (dosya_id, file_name, original_name, file_path, file_type, file_size, uploaded_by) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            // Dosyayı sil ve hata fırlat
            @unlink($file_path);
            throw new Exception('SQL hazırlama hatası.');
        }

        $execute_result = $stmt->execute([
            $dosya_id,
            $unique_filename,
            $file_name,
            $file_path,
            $file['type'],
            $file['size'],
            $personel_id
        ]);

        if (!$execute_result) {
            // Dosyayı sil ve hata fırlat
            @unlink($file_path);
            throw new Exception('Dosya bilgileri veritabanına kaydedilemedi.');
        }

        $file_id = $db->lastInsertId();

        // Başarılı yanıt döndür
        ob_clean(); // Önceki çıktıları temizle
        echo json_encode([
            'success' => true,
            'message' => 'Dosya başarıyla yüklendi.',
            'file' => [
                'file_id' => $file_id,
                'file_name' => $file_name,
                'file_size' => $file['size'],
                'file_type' => $file['type'],
                'file_path' => $file_path,
                'upload_date' => date('Y-m-d H:i:s')
            ]
        ]);

    } catch (PDOException $e) {
        // Dosyayı sil ve hata fırlat
        @unlink($file_path);
        throw new Exception('Veritabanı hatası: ' . $e->getMessage());
    }

} catch (Exception $e) {
    // Hata durumunda JSON yanıtı döndür
    $error_message = $e->getMessage();
    error_log("Upload Error: " . $error_message);
    
    // Tamponlanmış çıktıyı temizle
    ob_clean();
    
    echo json_encode([
        'success' => false,
        'message' => $error_message
    ]);
} finally {
    // Tamponlamayı sonlandır ve çıktıyı gönder
    ob_end_flush();
}
?>