<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Yönetimi</title>
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
        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
        }
        .modal-content {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
    </style>
</head>
<body>
    <?php
    require_once 'database.php';
    
    // Create database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // CRUD Operations
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['action'])) {
            try {
                switch ($_POST['action']) {
                    case 'add':
                        $sql = "INSERT INTO personel (ad, soyad, kullanici_adi, email, sifre, tc_kimlik_no, telefon) 
                                VALUES (:ad, :soyad, :kullanici_adi, :email, :sifre, :tc_kimlik_no, :telefon)";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([
                            ':ad' => $_POST['ad'],
                            ':soyad' => $_POST['soyad'],
                            ':kullanici_adi' => $_POST['kullanici_adi'],
                            ':email' => $_POST['email'],
                            ':sifre' => $_POST['sifre'],
                            ':tc_kimlik_no' => $_POST['tc_kimlik_no'],
                            ':telefon' => $_POST['telefon']
                        ]);
                        break;

                    case 'edit':
                        $sql = "UPDATE personel SET 
                                ad = :ad, 
                                soyad = :soyad, 
                                kullanici_adi = :kullanici_adi, 
                                email = :email, 
                                tc_kimlik_no = :tc_kimlik_no, 
                                telefon = :telefon 
                                WHERE personel_id = :personel_id";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([
                            ':ad' => $_POST['ad'],
                            ':soyad' => $_POST['soyad'],
                            ':kullanici_adi' => $_POST['kullanici_adi'],
                            ':email' => $_POST['email'],
                            ':tc_kimlik_no' => $_POST['tc_kimlik_no'],
                            ':telefon' => $_POST['telefon'],
                            ':personel_id' => $_POST['personel_id']
                        ]);
                        break;

                    case 'delete':
                        $sql = "DELETE FROM personel WHERE personel_id = :personel_id";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([':personel_id' => $_POST['personel_id']]);
                        break;
                }
            } catch(PDOException $e) {
                echo "Hata: " . $e->getMessage();
            }
        }
    }

    // Fetch all users
    try {
        $sql = "SELECT * FROM personel";
        $stmt = $conn->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "Hata: " . $e->getMessage();
        $result = [];
    }
    ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-10 py-3">
                <div class="page-header">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-people text-primary" style="font-size: 24px;"></i>
                        <h2 class="mb-0">Kullanıcı Yönetimi</h2>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-person-plus me-2"></i>Yeni Kullanıcı Ekle
                    </button>
                </div>

                <!-- Users Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="section-title">
                            <i class="bi bi-list-ul"></i>
                            Kullanıcı Listesi
                            <span class="badge bg-primary ms-2"><?php echo count($result); ?> kayıt</span>
                        </div>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Ad</th>
                                    <th>Soyad</th>
                                    <th>Kullanıcı Adı</th>
                                    <th>Email</th>
                                    <th>TC Kimlik No</th>
                                    <th>Telefon</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($result as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['personel_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ad']); ?></td>
                                    <td><?php echo htmlspecialchars($row['soyad']); ?></td>
                                    <td><?php echo htmlspecialchars($row['kullanici_adi']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['tc_kimlik_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['telefon']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-user" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editUserModal"
                                                data-id="<?php echo htmlspecialchars($row['personel_id']); ?>"
                                                data-ad="<?php echo htmlspecialchars($row['ad']); ?>"
                                                data-soyad="<?php echo htmlspecialchars($row['soyad']); ?>"
                                                data-kullanici="<?php echo htmlspecialchars($row['kullanici_adi']); ?>"
                                                data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                                data-tc="<?php echo htmlspecialchars($row['tc_kimlik_no']); ?>"
                                                data-telefon="<?php echo htmlspecialchars($row['telefon']); ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-user"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteUserModal"
                                                data-id="<?php echo htmlspecialchars($row['personel_id']); ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
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

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Kullanıcı Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Ad</label>
                            <input type="text" class="form-control" name="ad" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Soyad</label>
                            <input type="text" class="form-control" name="soyad" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" name="kullanici_adi" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Şifre</label>
                            <input type="password" class="form-control" name="sifre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">TC Kimlik No</label>
                            <input type="text" class="form-control" name="tc_kimlik_no" required maxlength="11">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telefon</label>
                            <input type="text" class="form-control" name="telefon" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kullanıcı Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="personel_id" id="edit_personel_id">
                        <div class="mb-3">
                            <label class="form-label">Ad</label>
                            <input type="text" class="form-control" name="ad" id="edit_ad" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Soyad</label>
                            <input type="text" class="form-control" name="soyad" id="edit_soyad" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" name="kullanici_adi" id="edit_kullanici_adi" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">TC Kimlik No</label>
                            <input type="text" class="form-control" name="tc_kimlik_no" id="edit_tc_kimlik_no" required maxlength="11">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telefon</label>
                            <input type="text" class="form-control" name="telefon" id="edit_telefon" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kullanıcı Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bu kullanıcıyı silmek istediğinizden emin misiniz?</p>
                    <form method="POST" id="deleteUserForm">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="personel_id" id="delete_personel_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" form="deleteUserForm" class="btn btn-danger">Sil</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Edit user modal data population
        document.querySelectorAll('.edit-user').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('edit_personel_id').value = this.dataset.id;
                document.getElementById('edit_ad').value = this.dataset.ad;
                document.getElementById('edit_soyad').value = this.dataset.soyad;
                document.getElementById('edit_kullanici_adi').value = this.dataset.kullanici;
                document.getElementById('edit_email').value = this.dataset.email;
                document.getElementById('edit_tc_kimlik_no').value = this.dataset.tc;
                document.getElementById('edit_telefon').value = this.dataset.telefon;
            });
        });

        // Delete user modal data population
        document.querySelectorAll('.delete-user').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('delete_personel_id').value = this.dataset.id;
            });
        });
    </script>
</body>
</html> 