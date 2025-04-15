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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = new Database();
        $conn = $db->getConnection();

        // POST verilerini kontrol et
        error_log("POST verileri: " . print_r($_POST, true));

        $dosya_id = intval($_POST['dosya_id']);

        // Önce dosyaya ait müşteri ID'sini al
        $stmt = $conn->prepare("SELECT musteri_id FROM dosyalar WHERE dosya_id = ?");
        $stmt->execute([$dosya_id]);
        $musteri_id = $stmt->fetchColumn();

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

        // İşlem tipine göre devam et
        if (isset($_POST['is_toplam_tutar_update']) && $_POST['is_toplam_tutar_update'] == '1') {
            $toplam_tutar = floatval($_POST['toplam_tutar']);
            
            $check_stmt = $conn->prepare("SELECT muhasebe_id FROM muhasebe WHERE musteri_id = ?");
            $check_stmt->execute([$musteri_id]);
            $muhasebe_id = $check_stmt->fetchColumn();

            if ($muhasebe_id) {
                $stmt = $conn->prepare("UPDATE muhasebe SET toplam_tutar = ?, kalan_tutar = ? WHERE musteri_id = ?");
                $stmt->execute([$toplam_tutar, $toplam_tutar, $musteri_id]);
            } else {
                $stmt = $conn->prepare("INSERT INTO muhasebe (musteri_id, toplam_tutar, yapilan_odeme, kalan_tutar) VALUES (?, ?, 0, ?)");
                $stmt->execute([$musteri_id, $toplam_tutar, $toplam_tutar]);
            }
        } else {
            $yapilan_odeme = floatval($_POST['yapilan_tutar']);
            $aciklama = $_POST['aciklama'] ?? '';

            $stmt = $conn->prepare("SELECT * FROM muhasebe WHERE musteri_id = ?");
            $stmt->execute([$musteri_id]);
            $muhasebe = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($muhasebe) {
                $yeni_yapilan_odeme = $muhasebe['yapilan_odeme'] + $yapilan_odeme;
                $yeni_kalan_tutar = $muhasebe['toplam_tutar'] - $yeni_yapilan_odeme;

                $stmt = $conn->prepare("UPDATE muhasebe SET yapilan_odeme = ?, kalan_tutar = ?, aciklama = ? WHERE musteri_id = ?");
                $stmt->execute([$yeni_yapilan_odeme, $yeni_kalan_tutar, $aciklama, $musteri_id]);
            } else {
                $toplam_tutar = floatval($_POST['toplam_tutar']);
                $kalan_tutar = $toplam_tutar - $yapilan_odeme;

                $stmt = $conn->prepare("INSERT INTO muhasebe (musteri_id, toplam_tutar, yapilan_odeme, kalan_tutar, aciklama) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$musteri_id, $toplam_tutar, $yapilan_odeme, $kalan_tutar, $aciklama]);
            }
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        error_log("PDO Hatası: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Veritabanı hatası: ' . $e->getMessage(),
            'error_code' => $e->getCode()
        ]);
    }
} 