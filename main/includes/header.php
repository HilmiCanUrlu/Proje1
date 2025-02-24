<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Büro Otomasyon</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Ana Sayfa</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Kullanıcı'; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
<<<<<<< HEAD
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                                    <i class="fas fa-user-circle"></i> Profil
                                </a>
                            </li>
=======
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-id-card"></i> Profil</a></li>
>>>>>>> 3566fc85cf77e1ad6aa3f20642a4ebc515ebb6cf
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

<<<<<<< HEAD
    <!-- Profil Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileModalLabel">Profil Bilgileri</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="user-info">
                        <p><strong>Kullanıcı Adı:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <p><strong>Rol:</strong> <?php echo htmlspecialchars($_SESSION['role']); ?></p>
                        <p><strong>Son Giriş:</strong> <?php echo $_SESSION['last_login'] ?? 'Bilgi yok'; ?></p>
                        <p><strong>Son Hatalı Giriş:</strong> <?php echo $_SESSION['last_failed_login'] ?? 'Bilgi yok'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

=======
>>>>>>> 3566fc85cf77e1ad6aa3f20642a4ebc515ebb6cf
    <!-- Ana Container -->
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                    <div class="position-sticky pt-3">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="dashboard.php">
                                    <i class="fas fa-home"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" onclick="checkPermission('users')">
                                    <i class="fas fa-users"></i> Kullanıcı Yönetimi
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" onclick="checkPermission('reports')">
                                    <i class="fas fa-chart-bar"></i> Raporlar
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" onclick="checkPermission('settings')">
                                    <i class="fas fa-cog"></i> Ayarlar
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>

                <!-- Ana İçerik Alanı -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
<<<<<<< HEAD
=======
                    <!-- Profil Modal -->
                    <div class="modal fade" id="profileModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Profil Bilgileri</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="user-info">
                                        <p><strong>Kullanıcı Adı:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                                        <p><strong>Rol:</strong> <?php echo htmlspecialchars($_SESSION['role']); ?></p>
                                        <p><strong>Son Giriş:</strong> <?php echo $_SESSION['last_login'] ?? 'Bilgi yok'; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
>>>>>>> 3566fc85cf77e1ad6aa3f20642a4ebc515ebb6cf
                </main>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
