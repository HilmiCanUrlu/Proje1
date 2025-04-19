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
    $stmt = $conn->prepare("SELECT izin_id, musteri_id FROM dosyalar WHERE dosya_id = ?");
    $stmt->execute([$_POST['dosya_id']]);
    $dosya = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user is authorized (admin or matches izin_id)
    if ($_SESSION['personel_id'] != 1 && $_SESSION['personel_id'] != $dosya['izin_id']) {
        echo json_encode(['success' => false, 'message' => 'You are not authorized to perform this action']);
        exit;
    }

    // Start transaction
    $conn->beginTransaction();

    // Get the latest record from muhasebe table
    $stmt = $conn->prepare("SELECT * FROM muhasebe WHERE musteri_id = ? ORDER BY muhasebe_id DESC LIMIT 1");
    $stmt->execute([$dosya['musteri_id']]);
    $lastRecord = $stmt->fetch(PDO::FETCH_ASSOC);

    $newToplamTutar = floatval($_POST['toplam_tutar']);
    
    if ($lastRecord) {
        // Calculate new kalan_tutar based on previous payments
        $yapilan_odeme = floatval($lastRecord['yapilan_odeme']);
        $kalan_tutar = $newToplamTutar - $yapilan_odeme;

        // Update the latest record
        $stmt = $conn->prepare("UPDATE muhasebe SET toplam_tutar = ?, kalan_tutar = ? WHERE muhasebe_id = ?");
        $stmt->execute([
            $newToplamTutar,
            $kalan_tutar,
            $lastRecord['muhasebe_id']
        ]);

        // Insert new record with updated values
        $stmt = $conn->prepare("INSERT INTO muhasebe (musteri_id, toplam_tutar, yapilan_odeme, kalan_tutar, aciklama) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $dosya['musteri_id'],
            $newToplamTutar,
            $yapilan_odeme,
            $kalan_tutar,
            'Toplam tutar güncellendi'
        ]);
    } else {
        // First record for this customer
        $stmt = $conn->prepare("INSERT INTO muhasebe (musteri_id, toplam_tutar, yapilan_odeme, kalan_tutar, aciklama) 
                               VALUES (?, ?, 0, ?, ?)");
        $stmt->execute([
            $dosya['musteri_id'],
            $newToplamTutar,
            $newToplamTutar,
            'İlk toplam tutar kaydı'
        ]);
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Toplam tutar başarıyla güncellendi',
        'new_total' => $newToplamTutar,
        'new_balance' => $kalan_tutar ?? $newToplamTutar
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