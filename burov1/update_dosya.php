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
        $db = new Database();
        $conn = $db->getConnection();

        // Personel ID'yi al
        $personel_id = $_SESSION['personel_id'];
        
        // POST verilerini kontrol et
        $dosya_id = isset($_POST['dosya_id']) ? intval($_POST['dosya_id']) : 0;
        $action = $_POST['action'] ?? '';

        if (!$dosya_id) {
            echo json_encode(['success' => false, 'message' => 'Dosya ID bulunamadı']);
            exit;
        }

        // Güncelleme türüne göre işlem yap
        switch ($action) {
            case 'updateParselBilgileri':
                // Parsel bilgilerini güncelle
                $tapu_maliki = $_POST['tapu_maliki'] ?? '';
                $il = $_POST['il'] ?? '';
                $ilce = $_POST['ilce'] ?? '';
                $mahalle = $_POST['mahalle'] ?? '';
                $ada = $_POST['ada'] ?? '';
                $parsel = $_POST['parsel'] ?? '';

                // Dosyalar tablosunda tapu_maliki alanı yoksa ekleyelim
                try {
                    $stmt = $conn->prepare("SHOW COLUMNS FROM dosyalar LIKE 'tapu_maliki'");
                    $stmt->execute();
                    if ($stmt->rowCount() === 0) {
                        $conn->exec("ALTER TABLE dosyalar ADD COLUMN tapu_maliki VARCHAR(255) NULL AFTER musteri_id");
                    }
                } catch (PDOException $ex) {
                    error_log("Tablo yapısı hatası: " . $ex->getMessage());
                }

                $stmt = $conn->prepare("UPDATE dosyalar SET 
                    tapu_maliki = ?,
                    il = ?, 
                    ilce = ?, 
                    mahalle = ?, 
                    ada = ?, 
                    parsel = ? 
                    WHERE dosya_id = ?");
                
                if ($stmt->execute([$tapu_maliki, $il, $ilce, $mahalle, $ada, $parsel, $dosya_id])) {
                    // Log oluştur
                    $logger = new Logger($conn);
                    $logger->logKaydet(
                        $personel_id,
                        'DOSYA_GUNCELLE',
                        "Dosya parsel bilgileri güncellendi: Dosya ID: $dosya_id"
                    );
                    
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Güncelleme sırasında bir hata oluştu']);
                }
                break;

            case 'updateMusteriBilgileri':
                // Müşteri bilgilerini güncelle
                $musteri_id = isset($_POST['musteri_id']) ? intval($_POST['musteri_id']) : 0;

                if (!$musteri_id) {
                    echo json_encode(['success' => false, 'message' => 'Geçerli bir müşteri seçilmedi']);
                    exit;
                }

                // Log değişiklik yapılan verileri
                error_log("Müşteri güncelleme: Dosya ID: $dosya_id, Müşteri ID: $musteri_id");
                
                // Tapu maliki güncellemesi de yapılacaksa
                if (isset($_POST['update_tapu_maliki']) && $_POST['update_tapu_maliki'] == '1') {
                    $tapu_maliki = $_POST['tapu_maliki'] ?? '';
                    error_log("Tapu maliki güncelleme: $tapu_maliki");
                    
                    // Tablo yapısını kontrol et
                    try {
                        $stmt = $conn->prepare("SHOW COLUMNS FROM dosyalar LIKE 'tapu_maliki'");
                        $stmt->execute();
                        if ($stmt->rowCount() === 0) {
                            error_log("tapu_maliki kolonunu ekliyorum");
                            $conn->exec("ALTER TABLE dosyalar ADD COLUMN tapu_maliki VARCHAR(255) NULL AFTER musteri_id");
                        }
                        
                        $stmt = $conn->prepare("UPDATE dosyalar SET musteri_id = ?, tapu_maliki = ? WHERE dosya_id = ?");
                        error_log("SQL sorgusu hazırlandı: UPDATE dosyalar SET musteri_id = $musteri_id, tapu_maliki = $tapu_maliki WHERE dosya_id = $dosya_id");
                        
                        if ($stmt->execute([$musteri_id, $tapu_maliki, $dosya_id])) {
                            // Log oluştur
                            $logger = new Logger($conn);
                            $logger->logKaydet(
                                $personel_id,
                                'DOSYA_GUNCELLE',
                                "Dosya müşteri ve tapu maliki bilgileri güncellendi: Dosya ID: $dosya_id, Müşteri ID: $musteri_id, Tapu Maliki: $tapu_maliki"
                            );
                            
                            echo json_encode(['success' => true]);
                        } else {
                            $errorInfo = $stmt->errorInfo();
                            error_log("SQL hatası: " . print_r($errorInfo, true));
                            echo json_encode(['success' => false, 'message' => 'Güncelleme sırasında bir hata oluştu: ' . $errorInfo[2]]);
                        }
                    } catch (PDOException $ex) {
                        error_log("Tablo yapısı hatası: " . $ex->getMessage());
                        echo json_encode(['success' => false, 'message' => 'Veritabanı yapısı hatası: ' . $ex->getMessage()]);
                    }
                } else {
                    // Sadece müşteri ID'sini güncelle
                    $stmt = $conn->prepare("UPDATE dosyalar SET musteri_id = ? WHERE dosya_id = ?");
                    error_log("SQL sorgusu hazırlandı: UPDATE dosyalar SET musteri_id = $musteri_id WHERE dosya_id = $dosya_id");
                    
                    if ($stmt->execute([$musteri_id, $dosya_id])) {
                        // Log oluştur
                        $logger = new Logger($conn);
                        $logger->logKaydet(
                            $personel_id,
                            'DOSYA_GUNCELLE',
                            "Dosya müşteri bilgileri güncellendi: Dosya ID: $dosya_id, Müşteri ID: $musteri_id"
                        );
                        
                        echo json_encode(['success' => true]);
                    } else {
                        $errorInfo = $stmt->errorInfo();
                        error_log("SQL hatası: " . print_r($errorInfo, true));
                        echo json_encode(['success' => false, 'message' => 'Güncelleme sırasında bir hata oluştu: ' . $errorInfo[2]]);
                    }
                }
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Geçersiz işlem türü']);
                break;
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