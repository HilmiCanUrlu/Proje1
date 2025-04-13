<?php
session_start();
require_once "database.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

if (!isset($_GET['dosya_id'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'Dosya ID required']));
}

$database = new Database();
$db = $database->getConnection();

try {
    // Dosya ve müşteri bilgilerini al
    $query = "SELECT d.*, m.musteri_adi, m.telefon, m.email 
              FROM dosyalar d 
              LEFT JOIN musteriler m ON d.musteri_id = m.musteri_id 
              WHERE d.dosya_id = :dosya_id";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':dosya_id' => $_GET['dosya_id']]);
    $dosya = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dosya) {
        http_response_code(404);
        exit(json_encode(['error' => 'Dosya not found']));
    }

    // İlk işlemi al (toplam tutarı almak için)
    $query = "SELECT * FROM islemler WHERE dosya_id = :dosya_id ORDER BY islem_id ASC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute([':dosya_id' => $_GET['dosya_id']]);
    $ilk_islem = $stmt->fetch(PDO::FETCH_ASSOC);

    // Son işlemi al (güncel kalan tutarı almak için)
    $query = "SELECT * FROM islemler WHERE dosya_id = :dosya_id ORDER BY islem_id DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute([':dosya_id' => $_GET['dosya_id']]);
    $son_islem = $stmt->fetch(PDO::FETCH_ASSOC);

    // Tüm işlemleri al
    $query = "SELECT *, DATE_FORMAT(olusturma_tarihi, '%d.%m.%Y %H:%i') as formatli_tarih 
              FROM islemler 
              WHERE dosya_id = :dosya_id 
              ORDER BY islem_id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([':dosya_id' => $_GET['dosya_id']]);
    $islemler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Toplam yapılan ödemeyi hesapla
    $toplam_yapilan = 0;
    foreach ($islemler as $islem) {
        $toplam_yapilan += $islem['yapilan_tutar'];
    }

    // Sonuçları döndür
    echo json_encode([
        'success' => true,
        'dosya' => $dosya,
        'islemler' => $islemler,
        'ozet' => [
            'toplam_tutar' => $ilk_islem ? $ilk_islem['toplam_tutar'] : 0,
            'toplam_yapilan' => $toplam_yapilan,
            'toplam_kalan' => $son_islem ? $son_islem['kalan_tutar'] : 0
        ],
        'ilk_islem_var' => !empty($ilk_islem)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    exit(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
} 