<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/app/auth/login.php');
    exit();
}

// Aktif menü elemanını belirleme
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo APP_URL; ?>/assets/css/style.css" rel="stylesheet">
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link href="<?php echo APP_URL . $css; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo APP_URL; ?>/app/dashboard/index.php">
                <?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> 
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                                    <i class="fas fa-user-circle"></i> Profil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?php echo APP_URL; ?>/app/auth/logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Çıkış
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar ve Ana İçerik Container -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" 
                               href="<?php echo APP_URL; ?>/app/dashboard/index.php">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" 
                               href="<?php echo APP_URL; ?>/app/users/list.php">
                                <i class="fas fa-users"></i> Kullanıcılar
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>" 
                               href="<?php echo APP_URL; ?>/app/dashboard/reports.php">
                                <i class="fas fa-chart-bar"></i> Raporlar
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Ana İçerik -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <!-- Flash mesajları -->
                <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show mt-3" role="alert">
                        <?php echo $_SESSION['flash_message']['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['flash_message']); ?>
                <?php endif; ?>

                <!-- Profil Modal -->
                <div class="modal fade" id="profileModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Profil Bilgileri</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="user-info mb-4">
                                    <div class="text-center mb-3">
                                        <?php if ($_SESSION['profile_image']): ?>
                                            <img src="<?php echo $_SESSION['profile_image']; ?>" class="rounded-circle profile-image" alt="Profil">
                                        <?php else: ?>
                                            <i class="fas fa-user-circle fa-5x text-secondary"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tr>
                                                <th>Kullanıcı Adı:</th>
                                                <td><?php echo htmlspecialchars($_SESSION['username']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Ad Soyad:</th>
                                                <td><?php echo htmlspecialchars($_SESSION['full_name']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>E-posta:</th>
                                                <td><?php echo htmlspecialchars($_SESSION['email']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Rol:</th>
                                                <td><?php echo htmlspecialchars($_SESSION['role']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Son Giriş:</th>
                                                <td><?php echo $_SESSION['last_login'] ? date('d.m.Y H:i', strtotime($_SESSION['last_login'])) : 'Bilgi yok'; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <!-- Şifre Değiştirme Formu -->
                                <form id="changePasswordForm" action="../users/change_password.php" method="POST" class="mt-4">
                                    <h6 class="mb-3">Şifre Değiştir</h6>
                                    <div class="mb-3">
                                        <label for="currentPassword" class="form-label">Mevcut Şifre</label>
                                        <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="newPassword" class="form-label">Yeni Şifre</label>
                                        <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirmPassword" class="form-label">Yeni Şifre (Tekrar)</label>
                                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Şifre Değiştir</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 