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
        body {
            background-color: #f5f5f5;
        }
        .container-fluid {
            padding: 20px;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        .nav-link {
            color: #333;
            padding: 0.8rem 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background-color: #e9ecef;
            color: #333;
        }
        .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
        .nav-link i {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
            color: #28a745;
            transition: color 0.3s ease;
        }
        .nav-link:hover i {
            color: #218838;
        }
        .nav-link.active i {
            color: white;
        }
        .collapse {
            background-color: #f1f3f5;
        }
        .collapse .nav-link {
            padding: 0.6rem 1rem 0.6rem 2.5rem;
            font-size: 0.95rem;
        }
        .collapse .nav-link i {
            font-size: 1rem;
            width: 20px;
        }
        .collapse .nav-link:hover {
            background-color: #e9ecef;
        }
        .bi-chevron-down {
            transition: transform 0.3s ease;
            color: #6c757d !important;
        }
        [aria-expanded="true"] .bi-chevron-down {
            transform: rotate(180deg);
        }
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .page-header {
            background-color: #f8f9fa;
            color: #333;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .section-title {
            color: #28a745;
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .table {
            font-size: 14px;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .stats-card {
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
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
                <!-- Başlık ve Açıklama -->
                <div class="page-header">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-people text-primary" style="font-size: 24px;"></i>
                        <h2 class="mb-0">Müşteri Takip</h2>
                    </div>
                </div>

                <!-- Üst Kartlar -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card bg-white">
                            <h6><i class="bi bi-person text-primary me-2"></i>Gerçek Müşteri</h6>
                            <h2><?php echo $gercek_musteri_count; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card bg-white">
                            <h6><i class="bi bi-building text-success me-2"></i>Tüzel Müşteri</h6>
                            <h2><?php echo $tuzel_musteri_count; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card bg-white">
                            <h6><i class="bi bi-person-lines-fill text-warning me-2"></i>Toplam Müşteri</h6>
                            <h2><?php echo $toplam_musteri_count; ?></h2>
                        </div>
                    </div>
                </div>

                <!-- Filter Panel -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="section-title">
                            <i class="bi bi-funnel-fill"></i>
                            Filtreleme Seçenekleri
                        </div>
                        <form method="GET" class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Müşteri Adı</label>
                                <input type="text" name="musteri_adi" class="form-control" value="<?php echo $_GET['musteri_adi'] ?? ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Müşteri Türü</label>
                                <select name="musteri_turu" class="form-select">
                                    <option value="">Seçiniz</option>
                                    <option value="Gerçek Kişi" <?php echo (isset($_GET['musteri_turu']) && $_GET['musteri_turu'] == 'Gerçek Kişi') ? 'selected' : ''; ?>>Gerçek Kişi</option>
                                    <option value="Tüzel kişi" <?php echo (isset($_GET['musteri_turu']) && $_GET['musteri_turu'] == 'Tüzel kişi') ? 'selected' : ''; ?>>Tüzel Kişi</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-2"></i>Filtrele
                                </button>
                                <a href="musteri_takip.php" class="btn btn-secondary ms-2">
                                    <i class="bi bi-arrow-repeat me-2"></i>Sıfırla
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Müşteri Listesi -->
                <div class="card">
                    <div class="card-body">
                        <div class="section-title">
                            <i class="bi bi-list-ul"></i>
                            Müşteri Listesi
                            <span class="badge bg-primary ms-2"><?php echo count($musteriler); ?> kayıt</span>
                        </div>
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
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-primary" onclick="openEditModal(<?php echo $musteri['musteri_id']; ?>)">
                                                    <i class="bi bi-pencil me-1"></i>Düzenle
                                                </button>
                                                <button class="btn btn-danger" onclick="deleteCustomer(<?php echo $musteri['musteri_id']; ?>)">
                                                    <i class="bi bi-trash me-1"></i>Sil
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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
                    <h5 class="modal-title" id="duzenleModalLabel">Müşteri Düzenle</h5>
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
                modal.hide();
                location.reload();
            }
        });
    </script>
</body>
</html> 