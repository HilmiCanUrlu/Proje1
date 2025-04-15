<?php
session_start();
require_once 'config.php';
require_once 'database.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Oturum bulunamadı']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = new Database();
        $conn = $db->getConnection();

        $dosya_id = $_POST['dosya_id'];
        
        // Toplam tutar güncelleme kontrolü
        if (isset($_POST['is_toplam_tutar_update']) && $_POST['is_toplam_tutar_update'] == '1') {
            $toplam_tutar = floatval($_POST['toplam_tutar']);
            
            // En son işlemi güncelle
            $stmt = $conn->prepare("UPDATE muhasebe_islemleri SET toplam_tutar = ? WHERE dosya_id = ? ORDER BY islem_id DESC LIMIT 1");
            $stmt->execute([$toplam_tutar, $dosya_id]);
            
            echo json_encode(['success' => true]);
            exit;
        }

        // Yeni işlem ekleme
        $toplam_tutar = floatval($_POST['toplam_tutar']);
        $yapilan_tutar = floatval($_POST['yapilan_tutar']);
        $kalan_tutar = $toplam_tutar - $yapilan_tutar;
        $aciklama = $_POST['aciklama'] ?? '';

        $stmt = $conn->prepare("INSERT INTO muhasebe_islemleri (dosya_id, toplam_tutar, yapilan_tutar, kalan_tutar, aciklama) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$dosya_id, $toplam_tutar, $yapilan_tutar, $kalan_tutar, $aciklama]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Veritabanı hatası: ' . $e->getMessage()
        ]);
    }
} 