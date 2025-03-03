<?php
session_start();
require_once "database.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Filtreleme parametrelerini al
$where = "1=1";
$params = array();

if (isset($_GET['musteri_adi']) && !empty($_GET['musteri_adi'])) {
    $where .= " AND m.musteri_adi LIKE :musteri_adi";
    $params[':musteri_adi'] = '%' . $_GET['musteri_adi'] . '%';
}

if (isset($_GET['dosya_turu']) && !empty($_GET['dosya_turu'])) {
    $where .= " AND d.dosya_turu = :dosya_turu";
    $params[':dosya_turu'] = $_GET['dosya_turu'];
}

if (isset($_GET['islem_turu']) && !empty($_GET['islem_turu'])) {
    $where .= " AND d.islem_turu = :islem_turu";
    $params[':islem_turu'] = $_GET['islem_turu'];
}

if (isset($_GET['il']) && !empty($_GET['il'])) {
    $where .= " AND d.il = :il";
    $params[':il'] = $_GET['il'];
}

// Dosyaları getir
$query = "SELECT d.*, m.musteri_adi 
          FROM dosyalar d 
          LEFT JOIN musteriler m ON d.musteri_id = m.musteri_id 
          WHERE $where 
          ORDER BY d.olusturma_tarihi DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$dosyalar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kartlar için dosya türlerini say
$shkm_count = count(array_filter($dosyalar, function($d) { return $d['dosya_turu'] == 'SHKM Dosyaları'; }));
$lihkab_count = count(array_filter($dosyalar, function($d) { return $d['dosya_turu'] == 'LİHKAB'; }));
$takim_proje_count = count(array_filter($dosyalar, function($d) { return $d['dosya_turu'] == 'Takım Proje'; }));
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        .nav-link {
            color: #333;
            padding: 0.8rem 1rem;
        }
        .nav-link:hover {
            background-color: #e9ecef;
        }
        .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
        .stats-card {
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sol Sidebar -->
            <div class="col-md-2 sidebar py-3">
                <h4 class="mb-4 ps-3">Dosya Takip</h4>
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php"><i class="bi bi-house-door me-2"></i>Dashboard</a>
                    <a class="nav-link" href="#"><i class="bi bi-people me-2"></i>Kullanıcı Yönetimi</a>
                    <a class="nav-link active" href="dosya_takip.php"><i class="bi bi-folder me-2"></i>Dosya Takip</a>
                    <a class="nav-link" href="musteri_takip.php"><i class="bi bi-person me-2"></i>Müşteri Takip</a>
                    <a class="nav-link" href="yeni_musteri.php"><i class="bi bi-person-plus me-2"></i>Yeni Müşteri</a>
                    <a class="nav-link" href="yeni_dosya.php"><i class="bi bi-file-plus me-2"></i>Yeni Dosya Ekle</a>
                    <a class="nav-link" href="#"><i class="bi bi-gear me-2"></i>Ayarlar</a>
                </nav>
            </div>

            <!-- Ana İçerik -->
            <div class="col-md-10 py-3">
                <h2 class="mb-4">Dosya Takip</h2>
                <!-- Üst Kartlar -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card bg-white">
                            <h6>SHKM Dosyaları</h6>
                            <h2><?php echo $shkm_count; ?></h2>
                            <i class="bi bi-folder text-primary"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card bg-white">
                            <h6>LİHKAB Dosyaları</h6>
                            <h2><?php echo $lihkab_count; ?></h2>
                            <i class="bi bi-folder text-success"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card bg-white">
                            <h6>Takım Proje</h6>
                            <h2><?php echo $takim_proje_count; ?></h2>
                            <i class="bi bi-folder text-warning"></i>
                        </div>
                    </div>
                </div>

                <!-- Hızlı İşlemler ve Filtreleme Formu -->
                <div class="row">
                    <div class="col-md-8">
                        <!-- Dosya Listesi -->
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Dosyalar</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Müşteri Adı</th>
                                                <th>Dosya Türü</th>
                                                <th>İlçe-Mahalle</th>
                                                <th>Ada/Parsel</th>
                                                <th>İşlemler</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dosyalar as $dosya): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($dosya['musteri_adi']); ?></td>
                                                <td><?php echo htmlspecialchars($dosya['dosya_turu']); ?></td>
                                                <td><?php echo htmlspecialchars($dosya['ilce'] . ' ' . $dosya['mahalle']); ?></td>
                                                <td><?php echo htmlspecialchars($dosya['ada'] . '/' . $dosya['parsel']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary">Düzenle</button>
                                                    <button class="btn btn-sm btn-danger">Sil</button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Dosya Süz</h5>
                            </div>
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Müşteri Adı</label>
                                        <input type="text" name="musteri_adi" class="form-control" value="<?php echo $_GET['musteri_adi'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Dosya Türü</label>
                                        <select name="dosya_turu" class="form-select">
                                            <option value="">Seçiniz</option>
                                            <!-- JavaScript ile doldurulacak -->
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">İşlem Türü</label>
                                        <select name="islem_turu" class="form-select">
                                            <option value="">Seçiniz</option>
                                            <!-- JavaScript ile doldurulacak -->
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">İl</label>
                                        <select name="il" class="form-select">
                                            <option value="">Seçiniz</option>
                                            <!-- JavaScript ile doldurulacak -->
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">Filtrele</button>
                                        <a href="dosya_takip.php" class="btn btn-secondary">Sıfırla</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Filtreleme seçeneklerini doldur
    fetch('get_mahalleler.php')
        .then(response => response.json())
        .then(data => {
            const dosyaTuruSelect = document.querySelector('select[name="dosya_turu"]');
            const islemTuruSelect = document.querySelector('select[name="islem_turu"]');
            const ilSelect = document.querySelector('select[name="il"]');

            // Dosya türlerini doldur
            data.dosya_turleri.forEach(tur => {
                dosyaTuruSelect.innerHTML += `<option value="${tur}">${tur}</option>`;
            });

            // İşlem türlerini doldur
            data.islem_turleri.forEach(tur => {
                islemTuruSelect.innerHTML += `<option value="${tur}">${tur}</option>`;
            });

            // İlleri doldur
            Object.keys(data.iller).forEach(il => {
                ilSelect.innerHTML += `<option value="${il}">${il}</option>`;
            });
        });
    </script>
</body>
</html>
