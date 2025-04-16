<?php
session_start();
require_once "database.php";

header('Content-Type: application/json');

// Hata raporlamayı aktifleştir
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Oturum kontrolü - loggedin kontrolü ekleyelim
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
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

    $dosya_id = intval($_GET['dosya_id']);

    // Debug için dosya ID'sini kontrol et
    error_log("Dosya ID: " . $dosya_id);

    // Önce dosyaya ait müşteri ID'sini al
    $stmt = $conn->prepare("SELECT musteri_id FROM dosyalar WHERE dosya_id = ?");
    $stmt->execute([$dosya_id]);
    $musteri_id = $stmt->fetchColumn();

    // Debug için müşteri ID'sini kontrol et
    error_log("Müşteri ID: " . ($musteri_id ?: 'bulunamadı'));

    if (!$musteri_id) {
        echo json_encode(['success' => false, 'message' => 'Dosyaya ait müşteri bulunamadı']);
        exit;
    }

    // Muhasebe tablosunun varlığını kontrol et
    $tableCheck = $conn->query("SHOW TABLES LIKE 'muhasebe'");
    if ($tableCheck->rowCount() == 0) {
        // Muhasebe tablosu yoksa oluştur
        $conn->exec("CREATE TABLE IF NOT EXISTS muhasebe (
            muhasebe_id INT AUTO_INCREMENT PRIMARY KEY,
            musteri_id INT NOT NULL,
            toplam_tutar DECIMAL(10,2) NOT NULL DEFAULT 0,
            yapilan_odeme DECIMAL(10,2) NOT NULL DEFAULT 0,
            kalan_tutar DECIMAL(10,2) NOT NULL DEFAULT 0,
            aciklama TEXT,
            tarih TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (musteri_id) REFERENCES musteriler(musteri_id)
        )");
    }

    // Muhasebe kayıtlarını getir
    $stmt = $conn->prepare("SELECT * FROM muhasebe WHERE musteri_id = ? ORDER BY muhasebe_id DESC");
    $stmt->execute([$musteri_id]);
    $islemler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug için işlem sayısını kontrol et
    error_log("Bulunan işlem sayısı: " . count($islemler));

    // Özet bilgileri hesapla
    $toplam_tutar = 0;
    $toplam_yapilan = 0;
    $toplam_kalan = 0;
    $ilk_islem_var = false;

    if (count($islemler) > 0) {
        $ilk_islem_var = true;
        $toplam_tutar = floatval($islemler[0]['toplam_tutar']);
        $toplam_yapilan = floatval($islemler[0]['yapilan_odeme']);
        $toplam_kalan = floatval($islemler[0]['kalan_tutar']);
    }

    $response = [
        'success' => true,
        'islemler' => $islemler,
        'ozet' => [
            'toplam_tutar' => $toplam_tutar,
            'toplam_yapilan' => $toplam_yapilan,
            'toplam_kalan' => $toplam_kalan
        ],
        'ilk_islem_var' => $ilk_islem_var,
        'debug' => [
            'dosya_id' => $dosya_id,
            'musteri_id' => $musteri_id
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    error_log("PDO Hatası: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
} 