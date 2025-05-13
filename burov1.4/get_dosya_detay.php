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
    // Dosya bilgilerini al
    $query = "SELECT d.*, m.* 
              FROM dosyalar d 
              LEFT JOIN musteriler m ON d.musteri_id = m.musteri_id 
              WHERE d.dosya_id = :dosya_id";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':dosya_id' => $_GET['dosya_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        http_response_code(404);
        exit(json_encode(['error' => 'Dosya not found']));
    }

    // Dosya ve müşteri bilgilerini ayır
    $dosya = [
        'dosya_id' => $result['dosya_id'],
        'dosya_turu' => $result['dosya_turu'],
        'islem_turu' => $result['islem_turu'],
        'il' => $result['il'],
        'ilce' => $result['ilce'],
        'mahalle' => $result['mahalle'],
        'ada' => $result['ada'],
        'parsel' => $result['parsel'],
        'dosya_durumu' => $result['dosya_durumu']
    ];

    $musteri = [
        'musteri_id' => $result['musteri_id'],
        'musteri_adi' => $result['musteri_adi'],
        'telefon' => $result['telefon'],
        'email' => $result['email']
    ];

    // İşlemleri al
    $query = "SELECT * FROM islemler WHERE dosya_id = :dosya_id ORDER BY islem_id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([':dosya_id' => $_GET['dosya_id']]);
    $islemler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tüm bilgileri JSON olarak döndür
    echo json_encode([
        'success' => true,
        'dosya' => $dosya,
        'musteri' => $musteri,
        'islemler' => $islemler
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    exit(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
} 