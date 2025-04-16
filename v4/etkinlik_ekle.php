<?php
require_once 'database.php';
$database = new Database();
$conn = $database->getConnection();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tarih = $_POST['tarih'];
    $aciklama = $_POST['aciklama'];

    if (!empty($tarih) && !empty($aciklama)) {
        $stmt = $conn->prepare("INSERT INTO etkinlikler (tarih, aciklama) VALUES (?, ?)");
        if ($stmt->execute([$tarih, $aciklama])) {
            $message = "<div class='alert alert-success'>Etkinlik başarıyla eklendi!</div>";
            echo "<script>
    window.parent.postMessage('eventAdded', '*');
</script>";

        } else {
            $message = "<div class='alert alert-danger'>Kayıt sırasında hata oluştu.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Tüm alanları doldurunuz.</div>";
    }
}

// Modal üzerinden tarih gelecek
$tarih = isset($_GET['tarih']) ? $_GET['tarih'] : date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Etkinlik Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-3">
    <h4>Etkinlik Ekle</h4>
    <?php echo $message; ?>
    <form method="post" action="">
        <div class="mb-3">
            <label for="tarih" class="form-label">Tarih</label>
            <input type="date" id="tarih" name="tarih" class="form-control" value="<?php echo htmlspecialchars($tarih); ?>" required>
        </div>
        <div class="mb-3">
            <label for="aciklama" class="form-label">Etkinlik Açıklaması</label>
            <textarea id="aciklama" name="aciklama" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Kaydet</button>
    </form>
</body>
</html>
