<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
        
        .quick-actions .btn {
            width: 100%;
            margin-bottom: 0.5rem;
            text-align: left;
        }
    </style>
</head>
<body>
    <?php
    require_once 'database.php';
    
    // Veritabanı bağlantısı
    $database = new Database();
    $conn = $database->getConnection();

    try {
        // Toplam dosya sayısı
        $stmt = $conn->query("SELECT COUNT(*) as total FROM dosyalar");
        $totalFiles = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Aktif dosya sayısı (durumu 'Tamamlandı' olmayanlar)
        $stmt = $conn->query("SELECT COUNT(*) as active FROM dosyalar WHERE dosya_durumu != 'Tamamlandı'");
        $activeFiles = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

        // Toplam müşteri sayısı
        $stmt = $conn->query("SELECT COUNT(*) as total FROM musteriler");
        $totalCustomers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Toplam personel sayısı
        $stmt = $conn->query("SELECT COUNT(*) as total FROM personel");
        $totalStaff = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Son aktiviteleri çek
        $stmt = $conn->query("SELECT sl.*, p.ad, p.soyad 
                             FROM sistem_loglar sl 
                             LEFT JOIN personel p ON sl.personel_id = p.personel_id 
                             ORDER BY sl.tarih DESC 
                             LIMIT 10");
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch(PDOException $e) {
        echo "Veritabanı hatası: " . $e->getMessage();
        $totalFiles = $activeFiles = $totalCustomers = $totalStaff = 0;
        $activities = [];
    }
    ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar py-3">
                <div class="text-center mb-4">
                    <h3 class="text-primary">LOGO</h3>
                    <div class="border-bottom border-2 mb-3"></div>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link active" href="dashboard.php"><i class="bi bi-house-door me-2"></i>Dashboard</a>
                    <a class="nav-link" href="kullanici_yonetim.php"><i class="bi bi-people me-2"></i>Kullanıcı Yönetimi</a>
                    <a class="nav-link" href="dosya_takip.php"><i class="bi bi-folder me-2"></i>Dosya Takip</a>
                    <a class="nav-link" href="musteri_takip.php"><i class="bi bi-person me-2"></i>Müşteri Takip</a>
                    <a class="nav-link" href="yeni_musteri.php"><i class="bi bi-person-plus me-2"></i>Yeni Müşteri</a>
                    <a class="nav-link" href="yeni_dosya.php"><i class="bi bi-file-plus me-2"></i>Yeni Dosya Ekle</a>
                    <a class="nav-link" href="#"><i class="bi bi-gear me-2"></i>Ayarlar</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 py-3">
                <h2 class="mb-4">Dashboard</h2>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card bg-white">
                            <h6>Toplam Dosya</h6>
                            <h2><?php echo $totalFiles; ?></h2>
                            <i class="bi bi-folder text-primary"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-white">
                            <h6>Aktif Dosya</h6>
                            <h2><?php echo $activeFiles; ?></h2>
                            <i class="bi bi-clock-history text-success"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-white">
                            <h6>Müşteriler</h6>
                            <h2><?php echo $totalCustomers; ?></h2>
                            <i class="bi bi-people text-warning"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-white">
                            <h6>Personel</h6>
                            <h2><?php echo $totalStaff; ?></h2>
                            <i class="bi bi-person-badge text-info"></i>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities & Quick Actions -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Son Aktiviteler</h5>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>KULLANICI</th>
                                            <th>İŞLEM</th>
                                            <th>TARİH</th>
                                            <th>DURUM</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($activities as $activity): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($activity['ad'] . ' ' . $activity['soyad']); ?></td>
                                            <td><?php echo htmlspecialchars($activity['islem_tipi']); ?></td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($activity['tarih'])); ?></td>
                                            <td>
                                                <span class="badge bg-success">Başarılı</span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Hızlı İşlemler</h5>
                            </div>
                            <div class="card-body quick-actions">
                                <a href="yeni_musteri.php" class="btn btn-primary mb-2 w-100">
                                    <i class="bi bi-person-plus me-2"></i>Yeni Müşteri
                                </a>
                                <a href="yeni_dosya.php" class="btn btn-success mb-2 w-100">
                                    <i class="bi bi-file-earmark-text me-2"></i>Yeni Dosya
                                </a>
                                <a href="kullanici_yonetim.php" class="btn btn-info text-white w-100">
                                    <i class="bi bi-gear me-2"></i>Kullanıcı Yönetimi
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Yeni Dosya Ekle Modal -->
    <div class="modal fade" id="yeniDosyaModal" tabindex="-1" aria-labelledby="yeniDosyaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="yeniDosyaModalLabel">Yeni Dosya Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe src="yeni_dosya.php" style="width: 100%; height: 500px; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 