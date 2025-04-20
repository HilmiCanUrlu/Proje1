<?php
require_once 'database.php';
$database = new Database();
$conn = $database->getConnection();

$dosya_id = isset($_GET['dosya_id']) ? $_GET['dosya_id'] : null;
$musteri_id = null;
$source = isset($_GET['source']) ? $_GET['source'] : null;

if ($dosya_id) {
    // Fetch the customer ID for the given file ID
    $stmt = $conn->prepare("SELECT musteri_id FROM dosyalar WHERE dosya_id = ?");
    $stmt->execute([$dosya_id]);
    $musteri_id = $stmt->fetchColumn();
}

// Fetch personnel
$personnelQuery = $conn->query("SELECT CONCAT(ad, ' ', soyad) AS full_name FROM personel");
$personnel = $personnelQuery->fetchAll(PDO::FETCH_ASSOC);

$message = "";

session_start();

// Check if the user is logged in and get the personnel ID
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$ekleyen_id = $_SESSION['personel_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tarih = $_POST['tarih'];
    $baslik = $_POST['baslik'];
    $aciklama = $_POST['aciklama'];
    $kategori = $_POST['kategori'];
    $personel = ($source === 'file_detay' && !empty($_POST['personel'])) ? $_POST['personel'] : null;
    $dosya_id = !empty($_POST['dosya_linki']) ? $_POST['dosya_linki'] : null;
    $musteri = !empty($_POST['musteri']) ? $_POST['musteri'] : null;
    $tekrar_aylik = $_POST['tekrar_aylik'];
    $tekrar_yillik = $_POST['tekrar_yillik'];

    if (!empty($tarih) && !empty($baslik) && !empty($aciklama)) {
        // Generate a unique recurrence ID
        $recurrenceId = uniqid();

        // Insert the first event
        $stmt = $conn->prepare("INSERT INTO etkinlikler (tarih, baslik, aciklama, kategori, personel, dosya_linki, musteri, tekrar_aylik, tekrar_yillik, ekleyen, recurrence_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$tarih, $baslik, $aciklama, $kategori, $personel, $dosya_id, $musteri, $tekrar_aylik, $tekrar_yillik, $ekleyen_id, $recurrenceId]);

        // Handle monthly recurrence
        if ($tekrar_aylik) {
            for ($i = 1; $i <= 11; $i++) {
                $newDate = date('Y-m-d', strtotime("+$i month", strtotime($tarih)));
                $stmt->execute([$newDate, $baslik, $aciklama, $kategori, $personel, $dosya_id, $musteri, $tekrar_aylik, $tekrar_yillik, $ekleyen_id, $recurrenceId]);
            }
        }

        // Handle yearly recurrence
        if ($tekrar_yillik) {
            for ($i = 1; $i <= 4; $i++) {
                $newDate = date('Y-m-d', strtotime("+$i year", strtotime($tarih)));
                $stmt->execute([$newDate, $baslik, $aciklama, $kategori, $personel, $dosya_id, $musteri, $tekrar_aylik, $tekrar_yillik, $ekleyen_id, $recurrenceId]);
            }
        }

        $message = "<div class='alert alert-success'>Etkinlik başarıyla eklendi!</div>";
        echo "<script>window.parent.postMessage('eventAdded', '*');</script>";
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
            <label for="baslik" class="form-label">Etkinlik Adı</label>
            <input type="text" id="baslik" name="baslik" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="aciklama" class="form-label">Etkinlik Açıklaması</label>
            <textarea id="aciklama" name="aciklama" class="form-control" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label for="kategori" class="form-label">Kategori</label>
            <select id="kategori" name="kategori" class="form-control" required>
                <option value="Firma İşlemleri">Firma İşlemleri</option>
                <option value="Kişisel Gelişim">Kişisel Gelişim</option>
                <option value="Eğitim">Eğitim</option>
                <option value="Toplantı">Toplantı</option>
            </select>
        </div>
        <?php if ($source === 'file_detay') { ?>
        <div class="mb-3">
            <label for="personel" class="form-label">Personel</label>
            <select id="personel" name="personel" class="form-control">
                <option value="">Seçiniz</option>
                <?php foreach ($personnel as $person) { ?>
                    <option value="<?php echo htmlspecialchars($person['full_name']); ?>"><?php echo htmlspecialchars($person['full_name']); ?></option>
                <?php } ?>
            </select>
        </div>
        <?php } ?>
        <input type="hidden" name="dosya_linki" value="<?php echo htmlspecialchars($dosya_id); ?>">
        <input type="hidden" name="musteri" value="<?php echo htmlspecialchars($musteri_id); ?>">
        <div class="mb-3">
            <label for="tekrar_aylik" class="form-label">Ay Tekrarı</label>
            <select id="tekrar_aylik" name="tekrar_aylik" class="form-control">
                <option value="0">Ay Tekrarı Yok</option>
                <option value="1">Her Ay</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="tekrar_yillik" class="form-label">Yıl Tekrarı</label>
            <select id="tekrar_yillik" name="tekrar_yillik" class="form-control">
                <option value="0">Yıl Tekrarı Yok</option>
                <option value="1">Her Yıl</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Kaydet</button>
    </form>
</body>
</html>
