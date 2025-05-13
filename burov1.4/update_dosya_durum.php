<?php
session_start();
require_once "database.php";
require_once "Logger.php";

header('Content-Type: application/json');

// Hata raporlamayı aktifleştir
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Oturum kontrolü
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Oturum bulunamadı']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // POST parametrelerini kontrol et
        if (!isset($_POST['dosya_id']) || !isset($_POST['durum']) && !isset($_POST['dosya_durumu'])) {
            echo json_encode(['success' => false, 'message' => 'Eksik parametreler: dosya_id ve durum veya dosya_durumu gerekli']);
            exit;
        }

        $dosya_id = intval($_POST['dosya_id']);
        
        // Hangi parametre gönderildiğine göre işlem yap
        if (isset($_POST['durum'])) {
            $durum = $_POST['durum'];
            
            // Durum değerini kontrol et
            if ($durum !== 'aktif' && $durum !== 'pasif') {
                echo json_encode(['success' => false, 'message' => 'Geçersiz durum değeri. Durum "aktif" veya "pasif" olmalıdır']);
                exit;
            }
            
            $db = new Database();
            $conn = $db->getConnection();

            // Personel ID'yi al
            $personel_id = $_SESSION['personel_id'];

            $stmt = $conn->prepare("UPDATE dosyalar SET durum = ? WHERE dosya_id = ?");
            
            if ($stmt->execute([$durum, $dosya_id])) {
                // Log oluştur
                $logger = new Logger($conn);
                $logger->logKaydet(
                    $personel_id,
                    'AKTIVITE_GUNCELLE',
                    "Dosya durumu güncellendi: Dosya ID: $dosya_id, Yeni Durum: $durum"
                );
                
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Veritabanı güncelleme hatası']);
            }
        } 
        // Dosya durumu güncellemesi
        elseif (isset($_POST['dosya_durumu'])) {
            $dosya_durumu = $_POST['dosya_durumu'];
            
            // Geçerli durumlar (isteğe bağlı olarak kontrol eklenebilir)
            $gecerli_durumlar = ['Hazırlandı', 'Belediyede', 'Tapuda', 'Tamamlandı', 'Beklemede'];
            if (!in_array($dosya_durumu, $gecerli_durumlar)) {
                echo json_encode(['success' => false, 'message' => 'Geçersiz dosya durumu']);
                exit;
            }
            
            try {
                $db = new Database();
                $conn = $db->getConnection();
                
                // Dosya durumunu güncelle
                $sql = "UPDATE dosyalar SET dosya_durumu = :dosya_durumu WHERE dosya_id = :dosya_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':dosya_durumu', $dosya_durumu, PDO::PARAM_STR);
                $stmt->bindParam(':dosya_id', $dosya_id, PDO::PARAM_INT);
                $result = $stmt->execute();
                
                if ($result) {
                    // Log kaydı ekle
                    $logger = new Logger($conn);
                    $logger->logKaydet($_SESSION['personel_id'], 'DOSYA_DURUMU_GUNCELLE', "Dosya ID: $dosya_id, Yeni Dosya Durumu: $dosya_durumu");
                    
                    echo json_encode(['success' => true, 'message' => 'Dosya durumu başarıyla güncellendi']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Dosya durumu güncellenirken bir hata oluştu']);
                }
                
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
            }
        }
    } catch (PDOException $e) {
        error_log("PDO Hatası: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Veritabanı hatası: ' . $e->getMessage(),
            'error_code' => $e->getCode()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek metodu']);
} 