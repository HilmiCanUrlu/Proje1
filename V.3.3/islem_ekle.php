<?php
session_start();
require_once "database.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

// Dosya ID kontrolü
if (!isset($_POST['dosya_id'])) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Dosya ID gerekli']));
}

$database = new Database();
$db = $database->getConnection();

try {
    // Verileri hazırla
    $dosya_id = $_POST['dosya_id'];
    
    // Toplam tutar güncelleme işlemi mi kontrol et
    if (isset($_POST['is_toplam_tutar_update']) && $_POST['is_toplam_tutar_update'] == '1') {
        if (!isset($_POST['toplam_tutar'])) {
            http_response_code(400);
            exit(json_encode(['success' => false, 'message' => 'Toplam tutar gerekli']));
        }

        $yeni_toplam_tutar = floatval($_POST['toplam_tutar']);
        
        // Önce tüm yapılan ödemeleri topla
        $query = "SELECT SUM(yapilan_tutar) as toplam_odeme 
                 FROM islemler 
                 WHERE dosya_id = :dosya_id";
        
        $stmt = $db->prepare($query);
        $stmt->execute([':dosya_id' => $dosya_id]);
        $odeme_sonuc = $stmt->fetch(PDO::FETCH_ASSOC);
        $toplam_odeme = floatval($odeme_sonuc['toplam_odeme']);
        
        // Yeni kalan tutarı hesapla
        $yeni_kalan_tutar = $yeni_toplam_tutar - $toplam_odeme;
        
        // Tüm işlemleri güncelle
        $query = "UPDATE islemler i1
                 SET toplam_tutar = :toplam_tutar,
                     kalan_tutar = :toplam_tutar - (
                         SELECT COALESCE(SUM(i2.yapilan_tutar), 0)
                         FROM islemler i2
                         WHERE i2.dosya_id = :dosya_id
                         AND i2.islem_id <= i1.islem_id
                     )
                 WHERE i1.dosya_id = :dosya_id";
        
        $stmt = $db->prepare($query);
        $result = $stmt->execute([
            ':dosya_id' => $dosya_id,
            ':toplam_tutar' => $yeni_toplam_tutar
        ]);

        if ($result) {
            // İşlem log kaydı
            $log_query = "INSERT INTO sistem_loglar (personel_id, islem_tipi, islem_detay, ip_adresi) 
                         VALUES (:personel_id, 'TOPLAM_TUTAR_GUNCELLE', :islem_detay, :ip_adresi)";
            
            $stmt = $db->prepare($log_query);
            $stmt->execute([
                ':personel_id' => $_SESSION['personel_id'],
                ':islem_detay' => "Dosya ID: $dosya_id için toplam tutar güncellendi. Yeni Toplam: $yeni_toplam_tutar, Toplam Ödeme: $toplam_odeme, Kalan: $yeni_kalan_tutar",
                ':ip_adresi' => $_SERVER['REMOTE_ADDR']
            ]);

            echo json_encode(['success' => true, 'message' => 'Toplam tutar başarıyla güncellendi']);
            exit;
        } else {
            throw new Exception('Toplam tutar güncellenirken bir hata oluştu');
        }
    }

    // Normal işlem ekleme işlemi için yapılan tutar kontrolü
    if (!isset($_POST['yapilan_tutar'])) {
        http_response_code(400);
        exit(json_encode(['success' => false, 'message' => 'Yapılan tutar gerekli']));
    }

    $yapilan_tutar = floatval($_POST['yapilan_tutar']);
    $aciklama = $_POST['aciklama'] ?? null;

    // İlk işlem olup olmadığını kontrol et
    $query = "SELECT COUNT(*) as islem_sayisi FROM islemler WHERE dosya_id = :dosya_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':dosya_id' => $dosya_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $ilk_islem = $result['islem_sayisi'] == 0;

    if ($ilk_islem) {
        // İlk işlem için toplam tutar gerekli
        if (!isset($_POST['toplam_tutar'])) {
            http_response_code(400);
            exit(json_encode(['success' => false, 'message' => 'İlk işlem için toplam tutar gerekli']));
        }
        $toplam_tutar = floatval($_POST['toplam_tutar']);
        $kalan_tutar = $toplam_tutar - $yapilan_tutar;
    } else {
        // Son işlemdeki kalan tutarı al
        $query = "SELECT toplam_tutar, kalan_tutar FROM islemler 
                 WHERE dosya_id = :dosya_id 
                 ORDER BY islem_id DESC LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute([':dosya_id' => $dosya_id]);
        $son_islem = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Toplam tutar ilk işlemdeki değer olacak
        $toplam_tutar = $son_islem['toplam_tutar'];
        // Yeni kalan tutar, son işlemdeki kalan tutardan yapılan ödemeyi çıkar
        $kalan_tutar = $son_islem['kalan_tutar'] - $yapilan_tutar;
    }

    // İşlemi kaydet
    $query = "INSERT INTO islemler (dosya_id, toplam_tutar, yapilan_tutar, kalan_tutar, aciklama, olusturma_tarihi) 
              VALUES (:dosya_id, :toplam_tutar, :yapilan_tutar, :kalan_tutar, :aciklama, NOW())";
    
    $stmt = $db->prepare($query);
    $result = $stmt->execute([
        ':dosya_id' => $dosya_id,
        ':toplam_tutar' => $toplam_tutar,
        ':yapilan_tutar' => $yapilan_tutar,
        ':kalan_tutar' => $kalan_tutar,
        ':aciklama' => $aciklama
    ]);

    if ($result) {
        // İşlem log kaydı
        $log_query = "INSERT INTO sistem_loglar (personel_id, islem_tipi, islem_detay, ip_adresi) 
                     VALUES (:personel_id, 'ISLEM_EKLE', :islem_detay, :ip_adresi)";
        
        $stmt = $db->prepare($log_query);
        $stmt->execute([
            ':personel_id' => $_SESSION['personel_id'],
            ':islem_detay' => "Dosya ID: $dosya_id için " . ($ilk_islem ? "ilk işlem" : "ödeme") . " eklendi. Ödeme: $yapilan_tutar, Kalan: $kalan_tutar",
            ':ip_adresi' => $_SERVER['REMOTE_ADDR']
        ]);

        echo json_encode(['success' => true, 'message' => 'İşlem başarıyla eklendi']);
    } else {
        throw new Exception('İşlem eklenirken bir hata oluştu');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 