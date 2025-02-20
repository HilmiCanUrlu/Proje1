<?php include 'includes/header.php'; ?>

<!-- CSS eklemeleri -->
<link href="assets/css/dashboard.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
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

        <!-- Ana içerik -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1>Dashboard</h1>
                <div class="date-time">
                    <span id="currentDateTime"></span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted">Toplam Kullanıcı</h6>
                                    <h3 class="mb-0" id="totalUsers">0</h3>
                                </div>
                                <div class="icon-box bg-primary">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted">Aktif Oturumlar</h6>
                                    <h3 class="mb-0" id="activeSessions">0</h3>
                                </div>
                                <div class="icon-box bg-success">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted">Toplam İşlem</h6>
                                    <h3 class="mb-0" id="totalTransactions">0</h3>
                                </div>
                                <div class="icon-box bg-warning">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted">Sistem Durumu</h6>
                                    <h3 class="mb-0 text-success">Aktif</h3>
                                </div>
                                <div class="icon-box bg-info">
                                    <i class="fas fa-server"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Son Aktiviteler</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
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
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Hızlı İşlemler</h5>
                        </div>
                        <div class="card-body">
                            <div class="quick-actions">
                                <button class="btn btn-primary mb-2 w-100" onclick="checkPermission('users')">
                                    <i class="fas fa-user-plus"></i> Yeni Kullanıcı
                                </button>
                                <button class="btn btn-success mb-2 w-100" onclick="checkPermission('reports')">
                                    <i class="fas fa-file-alt"></i> Rapor Oluştur
                                </button>
                                <button class="btn btn-info mb-2 w-100" onclick="checkPermission('settings')">
                                    <i class="fas fa-cog"></i> Ayarları Düzenle
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- JavaScript dosyaları -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/dashboard.js"></script>

<script>
// Tarih ve saat güncelleme
function updateDateTime() {
    const now = new Date();
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    };
    document.getElementById('currentDateTime').textContent = now.toLocaleDateString('tr-TR', options);
}

setInterval(updateDateTime, 1000);
updateDateTime();
</script>

<?php include 'includes/footer.php'; ?> 