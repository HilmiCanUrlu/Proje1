<?php
session_start();
require_once "database.php";
require_once "Logger.php";

// Oturum kontrolü
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$logger = new Logger($db);

// İşlem türü ekleme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    try {
        $query = "INSERT INTO d_islem (islem_turu) VALUES (:islem_turu)";
        $stmt = $db->prepare($query);
        $stmt->execute([':islem_turu' => $_POST['islem_turu']]);

        $logger->logKaydet(
            $_SESSION['personel_id'],
            'ISLEM_TURU_EKLE',
            "Yeni işlem türü eklendi: {$_POST['islem_turu']}"
        );

        $success_message = "İşlem türü başarıyla eklendi!";
    } catch(PDOException $e) {
        $error_message = "İşlem türü eklenirken bir hata oluştu: " . $e->getMessage();
    }
}

// İşlem türü silme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    try {
        $query = "DELETE FROM d_islem WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $_POST['id']]);

        $logger->logKaydet(
            $_SESSION['personel_id'],
            'ISLEM_TURU_SIL',
            "İşlem türü silindi: ID {$_POST['id']}"
        );

        $success_message = "İşlem türü başarıyla silindi!";
    } catch(PDOException $e) {
        $error_message = "İşlem türü silinirken bir hata oluştu: " . $e->getMessage();
    }
}

// İşlem türlerini getir
try {
    $query = "SELECT id, islem_turu FROM d_islem ORDER BY islem_turu ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $islem_turleri = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "İşlem türleri getirilirken bir hata oluştu: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İşlem Türü Yönetimi</title>
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sol Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Ana İçerik -->
            <div class="col-md-10 py-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>İşlem Türü Yönetimi</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#yeniIslemTuruModal">
                        <i class="bi bi-plus-circle"></i> Yeni İşlem Türü Ekle
                    </button>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>İşlem Türü</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($islem_turleri as $islem): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($islem['id']); ?></td>
                                        <td><?php echo htmlspecialchars($islem['islem_turu']); ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Bu işlem türünü silmek istediğinizden emin misiniz?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $islem['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash"></i> Sil
                                                </button>
                                            </form>
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

    <!-- Yeni İşlem Türü Modal -->
    <div class="modal fade" id="yeniIslemTuruModal" tabindex="-1" aria-labelledby="yeniIslemTuruModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="yeniIslemTuruModalLabel">Yeni İşlem Türü Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="islem_turu" class="form-label">İşlem Türü</label>
                            <input type="text" class="form-control" id="islem_turu" name="islem_turu" required>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 