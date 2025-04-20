<?php
// Hata raporlamayı ayarla
error_reporting(E_ALL);
ini_set('display_errors', 0);

// PDO bağlantı bilgileri
$host = 'localhost';
$dbname = 'personel_sistemi';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

try {
    // PDO bağlantı seçenekleri
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_general_ci"
    ];

    // PDO bağlantısını oluştur
    $db = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=$charset",
        $username,
        $password,
        $options
    );

} catch (PDOException $e) {
    // Hata durumunda JSON formatında yanıt döndür
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı bağlantı hatası'
    ]);
    exit;
}
?> 