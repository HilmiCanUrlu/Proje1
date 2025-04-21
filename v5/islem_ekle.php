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

$database = new Database();
$db = $database->getConnection();

try {
    // Gelen verileri kontrol et
    if (!isset($_POST['dosya_id']) || !isset($_POST['yapilan_tutar'])) {
        throw new Exception('Gerekli alanlar eksik');
    }
    $kalan= $_POST['kalan_tutar'];
    $dosya_id = $_POST['dosya_id'];
    $yapilan_tutar = floatval($_POST['yapilan_tutar']);
    $aciklama = $_POST['aciklama'] ?? '';
    
    // İlk işlem mi kontrol et
    $ilk_islem_kontrolu = "SELECT COUNT(*) as sayi FROM muhasebe WHERE dosya_id = :dosya_id";
    $stmt = $db->prepare($ilk_islem_kontrolu);
    $stmt->execute(['dosya_id' => $dosya_id]);
    $sonuc = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sonuc['sayi'] == 0) {
        // İlk işlem - toplam tutar gerekli
        if (!isset($_POST['toplam_tutar'])) {
            throw new Exception('İlk işlem için toplam tutar gerekli');
        }
        $toplam_tutar = floatval($_POST['toplam_tutar']);
        $kalan_tutar = $kalan - $yapilan_tutar;
    } else {
        // Mevcut işlem - son toplam tutarı ve kalan tutarı al
        $son_islem = "SELECT toplam_tutar, kalan_tutar FROM muhasebe 
                      WHERE dosya_id = :dosya_id 
                      ORDER BY muhasebe_id DESC LIMIT 1";
        $stmt = $db->prepare($son_islem);
        $stmt->execute(['dosya_id' => $dosya_id]);
        $son_kayit = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Son kalan tutardan yeni ödemeyi düş
        $toplam_tutar = $son_kayit['toplam_tutar'];
        $kalan_tutar = floatval($son_kayit['kalan_tutar']) - $yapilan_tutar;
        

    }
    
    // Yeni işlemi ekle
    $query = "INSERT INTO muhasebe (dosya_id, toplam_tutar, yapilan_odeme, kalan_tutar, aciklama) 
              VALUES (:dosya_id, :toplam_tutar, :yapilan_odeme, :kalan_tutar, :aciklama)";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        'dosya_id' => $dosya_id,
        'toplam_tutar' => $toplam_tutar,
        'yapilan_odeme' => $yapilan_tutar,
        'kalan_tutar' => $kalan_tutar,
        'aciklama' => $aciklama
    ]);
    
    // Tüm işlemleri getir
    $islemler_query = "SELECT * FROM muhasebe WHERE dosya_id = :dosya_id ORDER BY muhasebe_id ASC";
    $stmt = $db->prepare($islemler_query);
    $stmt->execute(['dosya_id' => $dosya_id]);
    $islemler = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Toplam yapılan ödemeyi hesapla
    $toplam_yapilan = 0;
    foreach ($islemler as $islem) {
        $toplam_yapilan += floatval($islem['yapilan_odeme']);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'İşlem başarıyla eklendi',
        'ozet' => [
            'toplam_tutar' => $toplam_tutar,
            'toplam_yapilan' => $toplam_yapilan,
            'toplam_kalan' => $kalan_tutar
        ],
        'islemler' => $islemler
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 