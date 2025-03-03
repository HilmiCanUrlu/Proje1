<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Veritabanı bağlantısı
$db = new Database();
$conn = $db->getConnection();

// Kullanıcıları çek
$stmt = $conn->prepare("SELECT * FROM users WHERE status = 'active'");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Yönetimi - Büro Otomasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid">
        <h1 class="mt-4">Kullanıcı Yönetimi</h1>
        
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted mb-2">Toplam Kullanıcı</div>
                            <h3 class="mb-0" id="totalUsers"><?php echo count($users); ?></h3>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10">
                            <i class="fas fa-users text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus"></i> Yeni Kullanıcı Ekle
            </button>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kullanıcı Adı</th>
                    <th>Ad Soyad</th>
                    <th>E-posta</th>
                    <th>Rol</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal" data-id="<?php echo $user['id']; ?>">
                                <i class="fas fa-edit"></i> Düzenle
                            </button>
                            <button class="btn btn-danger btn-sm" data-id="<?php echo $user['id']; ?>" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                <i class="fas fa-trash"></i> Sil
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Yeni Kullanıcı Ekle Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Yeni Kullanıcı Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Ad Soyad</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Şifre</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Rol</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="user">Kullanıcı</option>
                                <option value="manager">Yönetici</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Ekle</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Yeni kullanıcı ekleme işlemi
        $('#addUserForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            $.ajax({
                url: 'includes/add_user.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        location.reload(); // Sayfayı yenile
                    } else {
                        alert(response.message);
                    }
                }
            });
        });

        // Kullanıcı silme işlemi
        function deleteUser(userId) {
            if (confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?')) {
                $.ajax({
                    url: 'includes/delete_user.php',
                    type: 'POST',
                    data: { id: userId },
                    success: function(response) {
                        if (response.success) {
                            location.reload(); // Sayfayı yenile
                        } else {
                            alert(response.message);
                        }
                    }
                });
            }
        }
    </script>
</body>
</html>