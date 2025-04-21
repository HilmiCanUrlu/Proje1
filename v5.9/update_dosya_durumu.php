<?php
// Önce herhangi bir çıktı oluşmaması için tamponu başlat
ob_start();

// Oturum başlat ve gereken dosyaları dahil et
session_start();
require_once "database.php";
require_once "Logger.php";

// Hata raporlamayı devre dışı bırak, ancak loglama devam etsin
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Sesi kontrol et
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Tampon içeriğini temizle
    ob_clean();
    
    // Doğru JSON başlığını ayarla
    header('Content-Type: application/json');
    
    echo json_encode(['success' => false, 'message' => 'Oturum bulunamadı']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // POST parametrelerini kontrol et
        if (!isset($_POST['dosya_id']) || !isset($_POST['dosya_durumu'])) {
            // Tampon içeriğini temizle
            ob_clean();
            
            // Doğru JSON başlığını ayarla
            header('Content-Type: application/json');
            
            echo json_encode(['success' => false, 'message' => 'Eksik parametreler: dosya_id ve dosya_durumu gerekli']);
            exit;
        }

        $dosya_id = intval($_POST['dosya_id']);
        $dosya_durumu = $_POST['dosya_durumu'];
        
        // Geçerli durumlar
        $gecerli_durumlar = ['Hazırlandı', 'Belediyede', 'Tapuda', 'Tamamlandı', 'Beklemede'];
        if (!in_array($dosya_durumu, $gecerli_durumlar)) {
            // Tampon içeriğini temizle
            ob_clean();
            
            // Doğru JSON başlığını ayarla
            header('Content-Type: application/json');
            
            echo json_encode(['success' => false, 'message' => 'Geçersiz dosya durumu']);
            exit;
        }
        
        $db = new Database();
        $conn = $db->getConnection();
        
        // Personel ID'yi al
        $personel_id = $_SESSION['personel_id'];
        
        // Dosya durumunu güncelle
        $sql = "UPDATE dosyalar SET dosya_durumu = :dosya_durumu WHERE dosya_id = :dosya_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':dosya_durumu', $dosya_durumu, PDO::PARAM_STR);
        $stmt->bindParam(':dosya_id', $dosya_id, PDO::PARAM_INT);
        $result = $stmt->execute();
        
        // Tampon içeriğini temizle
        ob_clean();
        
        // Doğru JSON başlığını ayarla
        header('Content-Type: application/json');
        
        if ($result) {
            // Log kaydı ekle
            $logger = new Logger($conn);
            $logger->logKaydet($personel_id, 'DOSYA_DURUMU_GUNCELLEME', "Dosya ID: $dosya_id, Yeni Dosya Durumu: $dosya_durumu");
            
            echo json_encode(['success' => true, 'message' => 'Dosya durumu başarıyla güncellendi']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Dosya durumu güncellenirken bir hata oluştu']);
        }
    } catch (PDOException $e) {
        // Tampon içeriğini temizle
        ob_clean();
        
        // Doğru JSON başlığını ayarla
        header('Content-Type: application/json');
        
        error_log("PDO Hatası: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Veritabanı hatası: ' . $e->getMessage(),
            'error_code' => $e->getCode()
        ]);
    }
} else {
    // Tampon içeriğini temizle
    ob_clean();
    
    // Doğru JSON başlığını ayarla
    header('Content-Type: application/json');
    
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek metodu']);
}

// Tampon sonunu tamamla
ob_end_flush(); 