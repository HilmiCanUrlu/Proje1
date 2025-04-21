<?php
require_once "database.php";

$database = new Database();
$db = $database->getConnection();

try {
    $query = "SELECT musteri_id, musteri_adi, musteri_turu FROM musteriler ORDER BY musteri_adi ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $musteriler = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($musteriler);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 