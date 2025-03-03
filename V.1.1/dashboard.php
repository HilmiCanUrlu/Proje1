<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Büro Otomasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar Eklendi -->
    <nav class="top-navbar d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0">Ana Sayfa</h4>
        </div>
        <div class="dropdown user-dropdown">
            <div class="d-flex align-items-center" data-bs-toggle="dropdown">
                <i class="fas fa-user-circle me-2"></i>
                <span><?php echo $_SESSION['username']; ?></span>
                <i class="fas fa-chevron-down ms-2"></i>
            </div>
            <ul class="dropdown-menu shadow">
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                    <i class="fas fa-user"></i> Profil
                </a></li>
                <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> Ayarlar</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Çıkış
                </a></li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <div class="d-flex flex-column">
                    <h4 class="px-3 mb-4">Ana Sayfa</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users"></i> Kullanıcı Yönetimi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-chart-bar"></i> Raporlar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog"></i> Ayarlar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Ana İçerik -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Dashboard</h1>
                    <div class="text-muted" id="currentDateTime"></div>
                </div>

                <!-- İstatistik Kartları -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted mb-2">Toplam Kullanıcı</div>
                                    <h3 class="mb-0" id="totalUsers">150</h3>
                                </div>
                                <div class="stat-icon bg-primary bg-opacity-10">
                                    <i class="fas fa-users text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted mb-2">Aktif Oturumlar</div>
                                    <h3 class="mb-0" id="activeSessions">45</h3>
                                </div>
                                <div class="stat-icon bg-success bg-opacity-10">
                                    <i class="fas fa-clock text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted mb-2">Toplam İşlem</div>
                                    <h3 class="mb-0" id="totalTransactions">1,234</h3>
                                </div>
                                <div class="stat-icon bg-warning bg-opacity-10">
                                    <i class="fas fa-chart-line text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted mb-2">Sistem Durumu</div>
                                    <h3 class="mb-0 text-success">Aktif</h3>
                                </div>
                                <div class="stat-icon bg-info bg-opacity-10">
                                    <i class="fas fa-server text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <!-- Aktivite Tablosu -->
                    <div class="col-md-8">
                        <div class="activity-table">
                            <h5 class="mb-4">Son Aktiviteler</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Kullanıcı</th>
                                            <th>İşlem</th>
                                            <th>Tarih</th>
                                            <th>Durum</th>
                                        </tr>
                                    </thead>
                                    <tbody id="activityLog">
                                        <!-- JavaScript ile doldurulacak -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Hızlı İşlemler -->
                    <div class="col-md-4">
                        <div class="quick-actions">
                            <h5 class="mb-4">Hızlı İşlemler</h5>
                            <button class="btn btn-primary" onclick="checkPermission('users')">
                                <i class="fas fa-user-plus me-2"></i> Yeni Kullanıcı
                            </button>
                            <button class="btn btn-success" onclick="checkPermission('reports')">
                                <i class="fas fa-file-alt me-2"></i> Rapor Oluştur
                            </button>
                            <button class="btn btn-info text-white" onclick="checkPermission('settings')">
                                <i class="fas fa-cog me-2"></i> Ayarları Düzenle
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profil Modal'ı ekle - Body tag'i kapanmadan hemen önce -->
    <div class="modal fade" id="profileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Profil Bilgileri</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="avatar-circle mb-3">
                            <i class="fas fa-user-circle fa-4x text-primary"></i>
                        </div>
                        <h4><?php echo $_SESSION['username']; ?></h4>
                        <span class="badge bg-primary"><?php echo ucfirst($_SESSION['role']); ?></span>
                    </div>
                    
                    <div class="user-info">
                        <div class="info-item d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Ad Soyad</span>
                            <span class="fw-bold"><?php echo $_SESSION['full_name'] ?? 'Belirtilmemiş'; ?></span>
                        </div>
                        <div class="info-item d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">E-posta</span>
                            <span class="fw-bold"><?php echo $_SESSION['email'] ?? 'Belirtilmemiş'; ?></span>
                        </div>
                        <div class="info-item d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Son Giriş</span>
                            <span class="fw-bold"><?php echo $_SESSION['last_login'] ?? 'Belirtilmemiş'; ?></span>
                        </div>
                        <div class="info-item d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Hesap Durumu</span>
                            <span class="badge bg-success">Aktif</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                    <button type="button" class="btn btn-primary" onclick="window.location.href='profile.php'">
                        <i class="fas fa-edit me-2"></i>Profili Düzenle
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <script>
    function updateDateTime() {
        const now = new Date();
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            weekday: 'long',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        document.getElementById('currentDateTime').textContent = now.toLocaleDateString('tr-TR', options);
    }

    setInterval(updateDateTime, 1000);
    updateDateTime();

    // Profil modal'ı için gerekli fonksiyonlar
    document.addEventListener('DOMContentLoaded', function() {
        var profileModal = document.getElementById('profileModal');
        profileModal.addEventListener('show.bs.modal', function (event) {
            // Modal açıldığında yapılacak işlemler
        });
    });
    </script>
</body>
</html> 