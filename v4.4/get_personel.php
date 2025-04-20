<?php
session_start();
require_once 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['personel_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    // Fetch all personnel ordered by name and surname
    $stmt = $db->prepare("SELECT personel_id, ad, soyad FROM personel ORDER BY ad, soyad");
    $stmt->execute();
    $personnel = $stmt->fetchAll();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $personnel]);
} catch (PDOException $e) {
    error_log("Database error in get_personel.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Failed to fetch personnel list']);
}
?> 