<?php
session_start();
require_once "database.php";

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_POST['dosya_id']) || !isset($_POST['toplam_tutar'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Get the file's izin_id to check authorization
    $stmt = $conn->prepare("SELECT izin_id FROM dosyalar WHERE dosya_id = ?");
    $stmt->execute([$_POST['dosya_id']]);
    $dosya = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user is authorized (admin or matches izin_id)
    if ($_SESSION['personel_id'] != 1 && $_SESSION['personel_id'] != $dosya['izin_id']) {
        echo json_encode(['success' => false, 'message' => 'You are not authorized to perform this action']);
        exit;
    }

    // Start transaction
    $conn->beginTransaction();

    $dosya_id = $_POST['dosya_id'];
    $yeni_toplam_tutar = floatval($_POST['toplam_tutar']);

    // Get the latest record from muhasebe table
    $stmt = $conn->prepare("SELECT * FROM muhasebe WHERE dosya_id = ? ORDER BY muhasebe_id DESC LIMIT 1");
    $stmt->execute([$dosya_id]);
    $lastRecord = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($lastRecord) {
        // Calculate new kalan_tutar based on previous payments
        $yapilan_odeme = floatval($lastRecord['yapilan_odeme']);
        $kalan_tutar = $yeni_toplam_tutar - $yapilan_odeme;

        // Update the latest record
        $stmt = $conn->prepare("UPDATE muhasebe SET toplam_tutar = ?, kalan_tutar = ? WHERE muhasebe_id = ?");
        $stmt->execute([
            $yeni_toplam_tutar,
            $kalan_tutar,
            $lastRecord['muhasebe_id']
        ]);
    } else {
        // First record for this file
        $stmt = $conn->prepare("INSERT INTO muhasebe (dosya_id, toplam_tutar, yapilan_odeme, kalan_tutar, aciklama) 
                               VALUES (?, ?, 0, ?, ?)");
        $stmt->execute([
            $dosya_id,
            $yeni_toplam_tutar,
            $yeni_toplam_tutar,
            'İlk toplam tutar kaydı'
        ]);
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Toplam tutar başarıyla güncellendi',
        'new_total' => $yeni_toplam_tutar,
        'new_balance' => $kalan_tutar ?? $yeni_toplam_tutar
    ]);

} catch (PDOException $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 