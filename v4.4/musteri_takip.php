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

if (isset($_GET['musteri_turu']) && !empty($_GET['musteri_turu'])) {
    $where .= " AND m.musteri_turu = :musteri_turu";
    $params[':musteri_turu'] = $_GET['musteri_turu'];
}

// Müşterileri getir
$query = "SELECT * FROM musteriler m WHERE $where ORDER BY m.musteri_adi ASC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$musteriler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Müşteri türlerini say
$gercek_musteri_count = count(array_filter($musteriler, function($m) { return $m['musteri_turu'] == 'Gerçek Kişi'; }));
$tuzel_musteri_count = count(array_filter($musteriler, function($m) { return $m['musteri_turu'] == 'Tüzel kişi'; }));
$toplam_musteri_count = count($musteriler);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteri Takip</title>
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
            <?php include 'sidebar.php'; ?>

            <!-- Ana İçerik -->
            <div class="col-md-10 py-3">
                <h2 class="mb-4">Müşteri Takip</h2>

                <!-- Üst Kartlar -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card bg-white">
                            <h6>Gerçek Müşteri</h6>
                            <h2><?php echo $gercek_musteri_count; ?></h2>
                            <i class="bi bi-person text-primary"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card bg-white">
                            <h6>Tüzel Müşteri</h6>
                            <h2><?php echo $tuzel_musteri_count; ?></h2>
                            <i class="bi bi-building text-success"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card bg-white">
                            <h6>Toplam Müşteri</h6>
                            <h2><?php echo $toplam_musteri_count; ?></h2>
                            <i class="bi bi-person-lines-fill text-warning"></i>
                        </div>
                    </div>
                </div>

                <!-- Müşteri Listesi ve Arama Formu -->
                <div class="row">
                    <div class="col-md-8">
                        <!-- Müşteri Listesi -->
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Müşteriler</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Müşteri Adı</th>
                                                <th>Telefon No</th>
                                                <th>İş Sayısı</th>
                                                <th>İşlem</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($musteriler as $musteri): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($musteri['musteri_adi']); ?></td>
                                                <td><?php echo htmlspecialchars($musteri['telefon']); ?></td>
                                                <td>1</td>
                                                <td>
                                                <button class="btn btn-primary" onclick="openEditModal(<?php echo $musteri['musteri_id']; ?>)">Düzenle</button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteCustomer(<?php echo $musteri['musteri_id']; ?>)">Sil</button>
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
                        <!-- Arama Formu -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Ara</h5>
                            </div>
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Müşteri Adı</label>
                                        <input type="text" name="musteri_adi" class="form-control" value="<?php echo $_GET['musteri_adi'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Müşteri Türü</label>
                                        <select name="musteri_turu" class="form-select">
                                            <option value="">Seçiniz</option>
                                            <option value="Gerçek Kişi" <?php echo (isset($_GET['musteri_turu']) && $_GET['musteri_turu'] == 'Gerçek Kişi') ? 'selected' : ''; ?>>Gerçek Kişi</option>
                                            <option value="Tüzel kişi" <?php echo (isset($_GET['musteri_turu']) && $_GET['musteri_turu'] == 'Tüzel kişi') ? 'selected' : ''; ?>>Tüzel Kişi</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">Filtrele</button>
                                        <a href="musteri_takip.php" class="btn btn-secondary">Sıfırla</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Düzenle Modal -->
<div class="modal fade" id="duzenleModal" tabindex="-1" aria-labelledby="duzenleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="duzenleModalLabel">Etkinliği Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <iframe id="duzenleFrame" src="" style="width:100%; height:500px; border:none;"></iframe>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openEditModal(eventId) {
    const modal = new bootstrap.Modal(document.getElementById('duzenleModal'));
    const iframe = document.getElementById('duzenleFrame');
    iframe.src = `musteri_duzenle.php?id=${eventId}`;
    modal.show();
}


        function deleteCustomer(id) {
            window.location.href = 'musteri_sil.php?id=' + id;
        }
    </script>
    <script>
    window.addEventListener("message", function(event) {
        if (event.data === "refreshPage") {
            const modal = bootstrap.Modal.getInstance(document.getElementById('duzenleModal'));
            modal.hide(); // modalı kapat
            location.reload(); // sayfayı yenile
        }
    });
</script>

</body>
</html> 