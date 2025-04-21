<?php
session_start();
require_once "database.php";

header('Content-Type: application/json');

// Hata raporlamayı aktifleştir
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Oturum kontrolü
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Oturum açmanız gerekiyor']);
    exit;
}

// Dosya ID kontrolü
if (!isset($_GET['dosya_id'])) {
    echo json_encode(['success' => false, 'message' => 'Dosya ID gerekli']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    $dosya_id = intval($_GET['dosya_id']);

    // Debug için dosya ID'sini kontrol et
    error_log("Dosya ID: " . $dosya_id);

    // Retrieve izin_id for the file
    $stmt = $conn->prepare("SELECT izin_id FROM dosyalar WHERE dosya_id = ?");
    $stmt->execute([$dosya_id]);
    $izin_id = $stmt->fetchColumn();

    // Check if the current user is admin or matches izin_id
    $isAuthorizedToEdit = ($_SESSION['personel_id'] == 1 || $_SESSION['personel_id'] == $izin_id);

    // Muhasebe kayıtlarını getir
    $stmt = $conn->prepare("SELECT * FROM muhasebe WHERE dosya_id = ? ORDER BY muhasebe_id DESC");
    $stmt->execute([$dosya_id]);
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
        
        // Tüm yapılan ödemeleri topla
        foreach ($islemler as $islem) {
            $toplam_yapilan += floatval($islem['yapilan_odeme']);
        }
        
        $toplam_kalan = $toplam_tutar - $toplam_yapilan;
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
        'authorized' => $isAuthorizedToEdit,
        'debug' => [
            'dosya_id' => $dosya_id,
            'izin_id' => $izin_id
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