<?php
require_once 'database.php';

$db = new Database();
$conn = $db->getConnection();

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "Geçersiz ID";
    exit;
}

// Veritabanından mevcut müşteri bilgilerini çek
$sql = "SELECT * FROM musteriler WHERE musteri_id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$musteri = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$musteri) {
    echo "Müşteri bulunamadı.";
    exit;
}

// Form gönderildiyse güncelle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $musteri_adi = $_POST["musteri_adi"];
    $tc_kimlik_no = $_POST["tc_kimlik_no"];
    $telefon = $_POST["telefon"];
    $email = $_POST["email"];

    $updateSql = "UPDATE musteriler SET 
        musteri_adi = :musteri_adi, 
        tc_kimlik_no = :tc_kimlik_no, 
        telefon = :telefon, 
        email = :email 
        WHERE musteri_id = :id";

    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bindParam(':musteri_adi', $musteri_adi);
    $updateStmt->bindParam(':tc_kimlik_no', $tc_kimlik_no);
    $updateStmt->bindParam(':telefon', $telefon);
    $updateStmt->bindParam(':email', $email);
    $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($updateStmt->execute()) {
        echo "<script>
            window.parent.postMessage('refreshPage', '*');
        </script>";
        exit;
    }
    
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Müşteri Düzenle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h4>Müşteri Düzenle</h4>
        <form method="POST">
            <div class="mb-3">
                <label for="musteri_adi" class="form-label">Müşteri Adı</label>
                <input type="text" id="musteri_adi" name="musteri_adi" class="form-control" value="<?= htmlspecialchars($musteri['musteri_adi']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="tc_kimlik_no" class="form-label">TC Kimlik No</label>
                <input type="text" id="tc_kimlik_no" name="tc_kimlik_no" class="form-control" value="<?= htmlspecialchars($musteri['tc_kimlik_no']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="telefon" class="form-label">Telefon</label>
                <input type="text" id="telefon" name="telefon" class="form-control" value="<?= htmlspecialchars($musteri['telefon']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($musteri['email']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Güncelle</button>
            <a href="musteri_listesi.php" class="btn btn-secondary">Geri Dön</a>
        </form>
    </div>
</body>
</html>
