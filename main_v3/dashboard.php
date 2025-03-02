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
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar py-3">
                <h4 class="mb-4 ps-3">Dashboard</h4>
                <nav class="nav flex-column">
                    <a class="nav-link active" href="dashboard.php"><i class="bi bi-house-door me-2"></i>Dashboard</a>
                    <a class="nav-link" href="#"><i class="bi bi-people me-2"></i>Kullanıcı Yönetimi</a>
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
                            <h6>Toplam Kullanıcı</h6>
                            <h2>3</h2>
                            <i class="bi bi-people text-primary"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-white">
                            <h6>Aktif Oturumlar</h6>
                            <h2>2</h2>
                            <i class="bi bi-clock-history text-success"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-white">
                            <h6>Toplam İşlem</h6>
                            <h2>0</h2>
                            <i class="bi bi-graph-up text-warning"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-white">
                            <h6>Sistem Durumu</h6>
                            <h2 class="text-success">Aktif</h2>
                            <i class="bi bi-check-circle text-success"></i>
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
                                        <!-- Table rows will be populated dynamically -->
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
                                <button class="btn btn-primary"><i class="bi bi-person-plus me-2"></i>Yeni Kullanıcı</button>
                                <button class="btn btn-success"><i class="bi bi-file-earmark-text me-2"></i>Rapor Oluştur</button>
                                <button class="btn btn-info text-white"><i class="bi bi-gear me-2"></i>Ayarları Düzenle</button>
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