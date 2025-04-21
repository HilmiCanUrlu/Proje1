<?php
require_once 'database.php';
$database = new Database();
$conn = $database->getConnection();

$etkinlik_id = isset($_GET['etkinlik_id']) ? $_GET['etkinlik_id'] : null;

if ($etkinlik_id) {
    $stmt = $conn->prepare("SELECT e.*, 
                                   ekleyen.ad as ekleyen_ad, ekleyen.soyad as ekleyen_soyad, 
                                   m.musteri_adi 
                            FROM etkinlikler e
                            LEFT JOIN personel ekleyen ON e.ekleyen = ekleyen.personel_id
                            LEFT JOIN musteriler m ON e.musteri = m.musteri_id
                            WHERE e.id = ?");
    $stmt->execute([$etkinlik_id]);
    $etkinlik = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Etkinlik Detayı</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <?php if ($etkinlik) { ?>
        <h4><?php echo htmlspecialchars($etkinlik['baslik']); ?></h4>
        <p><?php echo htmlspecialchars($etkinlik['aciklama']); ?></p>
        <table class="table">
            <tr><th>Kayıt</th><td><?php echo date('d F Y l', strtotime($etkinlik['tarih'])); ?></td></tr>
            <tr><th>Ekleyen</th><td><?php echo htmlspecialchars($etkinlik['ekleyen_ad'] . ' ' . $etkinlik['ekleyen_soyad']); ?></td></tr>
            <tr><th>Kategori</th><td><?php echo htmlspecialchars($etkinlik['kategori']); ?></td></tr>
            <tr><th>Personel</th><td><?php echo htmlspecialchars($etkinlik['personel']); ?></td></tr>
            <tr><th>Dosya</th><td>
                <?php if (!empty($etkinlik['dosya_linki'])) { ?>
                    <a href="file_detay.php?dosya_id=<?php echo $etkinlik['dosya_linki']; ?>" target="_parent">Dosya Detayları</a>
                <?php } ?>
            </td></tr>
            <tr><th>Müşteri</th><td><?php echo htmlspecialchars($etkinlik['musteri_adi']); ?></td></tr>
            <tr><th>Tekrarlama</th><td>Ay Tekrarı: <?php echo $etkinlik['tekrar_aylik'] ? 'Evet' : 'Hayır'; ?>  Yıl Tekrarı: <?php echo $etkinlik['tekrar_yillik'] ? 'Evet' : 'Hayır'; ?></td></tr>
        </table>
    <?php } else { ?>
        <div class="alert alert-warning">Etkinlik bulunamadı.</div>
    <?php } ?>
</div>
</body>
</html> 