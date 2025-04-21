<?php
require_once 'database.php';

$db = new Database();
$conn = $db->getConnection();

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "Geçersiz ID";
    exit;
}

$sql = "DELETE FROM musteriler WHERE musteri_id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);

if ($stmt->execute()) {
    header("Location: musteri_takip.php"); // Listeye yönlendir
    exit;
} else {
    echo "Silme işlemi başarısız.";
}
