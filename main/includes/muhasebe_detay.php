<?php
session_start();
require_once 'config.php';
require_once 'database.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Oturum bulunamadı']);
    exit;
}

// Dosya ID kontrolü
if (!isset($_GET['dosya_id'])) {
    echo json_encode(['success' => false, 'message' => 'Dosya ID bulunamadı']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    $dosya_id = $_GET['dosya_id'];

    // İşlemleri getir
    $stmt = $conn->prepare("SELECT * FROM muhasebe_islemleri WHERE dosya_id = ? ORDER BY olusturma_tarihi DESC");
    $stmt->execute([$dosya_id]);
    $islemler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Özet bilgileri hesapla
    $toplam_tutar = 0;
    $toplam_yapilan = 0;
    $toplam_kalan = 0;
    $ilk_islem_var = false;

    if (count($islemler) > 0) {
        $ilk_islem_var = true;
        $toplam_tutar = floatval($islemler[0]['toplam_tutar']);
        
        foreach ($islemler as $islem) {
            $toplam_yapilan += floatval($islem['yapilan_tutar']);
        }
        
        $toplam_kalan = $toplam_tutar - $toplam_yapilan;
    }

    // Sonuçları döndür
    echo json_encode([
        'success' => true,
        'islemler' => $islemler,
        'ozet' => [
            'toplam_tutar' => $toplam_tutar,
            'toplam_yapilan' => $toplam_yapilan,
            'toplam_kalan' => $toplam_kalan
        ],
        'ilk_islem_var' => $ilk_islem_var
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
} 