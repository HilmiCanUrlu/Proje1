<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/database.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ana Sayfa - Büro Otomasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .top-bar {
            background: #343a40;
            color: white;
            padding: 0.5rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sidebar {
            width: 250px;
            background: white;
            min-height: calc(100vh - 48px);
            padding: 1rem;
        }
        .nav-link {
            color: #333;
            padding: 0.5rem 1rem;
            margin: 0.2rem 0;
            border-radius: 4px;
        }
        .nav-link.active {
            background: #0d6efd;
            color: white;
        }
        .nav-link:hover:not(.active) {
            background: #f8f9fa;
        }
        .nav-link i {
            margin-right: 10px;
            width: 20px;
        }
        .main-content {
            padding: 1rem;
        }
        .stats-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .stats-card h6 {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .stats-card .value {
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0;
        }
        .stats-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin-left: auto;
        }
        .activity-table th {
            color: #495057;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        .date-time {
            color: #6c757d;
            font-size: 0.9rem;
            text-align: right;
            margin: 1rem 0;
        }
        .quick-actions .btn {
            width: 100%;
            text-align: left;
            margin-bottom: 0.5rem;
            padding: 0.75rem 1rem;
        }
        .quick-actions .btn i {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <h4 class="mb-0">Ana Sayfa</h4>
        <div class="dropdown">
            <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <!-- Profil butonunu modal'a bağlayalım -->
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                        <i class="fas fa-user"></i> Profil
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="../auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Çıkış
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <h5 class="mb-3">Ana Sayfa</h5>
            <div class="nav flex-column">
                <a class="nav-link active" href="index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a class="nav-link" href="../users/list.php">
                    <i class="fas fa-users"></i> Kullanıcı Yönetimi
                </a>
                <a class="nav-link" href="reports.php">
                    <i class="fas fa-chart-bar"></i> Raporlar
                </a>
                <a class="nav-link" href="settings.php">
                    <i class="fas fa-cog"></i> Ayarlar
                </a>
            </div>
        </div>

        <!-- Ana İçerik -->
        <div class="main-content flex-grow-1">
            <h2>Dashboard</h2>
            <div class="date-time">
                23 Şubat 2025 Pazar 01:52:38
            </div>

            <!-- İstatistik Kartları -->
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="stats-card d-flex align-items-center">
                        <div>
                            <h6>Toplam Kullanıcı</h6>
                            <p class="value" id="totalUsers">0</p>
                        </div>
                        <div class="stats-icon bg-primary text-white">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card d-flex align-items-center">
                        <div>
                            <h6>Aktif Oturumlar</h6>
                            <p class="value" id="activeSessions">0</p>
                        </div>
                        <div class="stats-icon bg-success text-white">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card d-flex align-items-center">
                        <div>
                            <h6>Toplam İşlem</h6>
                            <p class="value" id="totalTransactions">0</p>
                        </div>
                        <div class="stats-icon bg-info text-white">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card d-flex align-items-center">
                        <div>
                            <h6>Sistem Durumu</h6>
                            <p class="value text-success" id="systemStatus">-</p>
                        </div>
                        <div class="stats-icon bg-warning text-white">
                            <i class="fas fa-server"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Aktivite Tablosu -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Son Aktiviteler</h5>
                            <div class="table-responsive">
                                <table class="table activity-table">
                                    <thead>
                                        <tr>
                                            <th>KULLANICI</th>
                                            <th>İŞLEM</th>
                                            <th>TARİH</th>
                                            <th>DURUM</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>user</td>
                                            <td>Çıkış yapıldı</td>
                                            <td>2025-02-21 03:20</td>
                                            <td><span class="badge bg-info">info</span></td>
                                        </tr>
                                        <tr>
                                            <td>admin</td>
                                            <td>Çıkış yapıldı</td>
                                            <td>2025-02-21 03:12</td>
                                            <td><span class="badge bg-info">info</span></td>
                                        </tr>
                                        <tr>
                                            <td>admin</td>
                                            <td>Çıkış yapıldı</td>
                                            <td>2025-02-21 02:41</td>
                                            <td><span class="badge bg-info">info</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hızlı İşlemler -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Hızlı İşlemler</h5>
                            <div class="quick-actions">
                                <button class="btn btn-primary" data-action="newUser">
                                    <i class="fas fa-user-plus"></i> Yeni Kullanıcı
                                </button>
                                <button class="btn btn-success" data-action="createReport">
                                    <i class="fas fa-file-alt"></i> Rapor Oluştur
                                </button>
                                <button class="btn btn-info text-white" data-action="settings">
                                    <i class="fas fa-cog"></i> Ayarları Düzenle
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/dashboard.js"></script>
</body>
</html> 