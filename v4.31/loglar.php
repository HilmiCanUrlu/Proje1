<?php
session_start();
require_once "database.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$conn = $database->getConnection();

$logs = [];
$operation_type = '';
$start_date = '';
$end_date = '';

// Filter logs based on operation type and date range
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $operation_type = $_POST['operation_type'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    $query = "SELECT l.*, p.ad, p.soyad 
              FROM sistem_loglar l 
              LEFT JOIN personel p ON l.personel_id = p.personel_id 
              WHERE 1=1";

    if ($operation_type) {
        $query .= " AND l.islem_tipi = :operation_type";
    }
    if ($start_date) {
        $query .= " AND l.tarih >= :start_date";
    }
    if ($end_date) {
        $query .= " AND l.tarih <= :end_date";
    }

    $query .= " ORDER BY l.tarih DESC";

    $stmt = $conn->prepare($query);

    if ($operation_type) {
        $stmt->bindParam(':operation_type', $operation_type);
    }
    if ($start_date) {
        $stmt->bindParam(':start_date', $start_date);
    }
    if ($end_date) {
        $stmt->bindParam(':end_date', $end_date);
    }

    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Default log retrieval without filters
    $query = "SELECT l.*, p.ad, p.soyad 
              FROM sistem_loglar l 
              LEFT JOIN personel p ON l.personel_id = p.personel_id 
              ORDER BY l.tarih DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Logları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sol Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Ana İçerik -->
            <div class="col-md-10 py-3">
                <h2 class="mb-4">Sistem Logları</h2>

                <!-- Filter Panel -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="operation_type" class="form-label">İşlem Tipi</label>
                                    <select name="operation_type" class="form-select">
                                        <option value="">Tümü</option>
                                        <option value="LOGIN" <?php echo ($operation_type == 'LOGIN') ? 'selected' : ''; ?>>Giriş</option>
                                        <option value="LOGIN_FAILED" <?php echo ($operation_type == 'LOGIN_FAILED') ? 'selected' : ''; ?>>Başarısız Giriş</option>
                                        <option value="DOSYA_EKLE" <?php echo ($operation_type == 'DOSYA_EKLE') ? 'selected' : ''; ?>>Dosya Ekle</option>
                                        <!-- Add more operation types as needed -->
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="start_date" class="form-label">Başlangıç Tarihi</label>
                                    <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="end_date" class="form-label">Bitiş Tarihi</label>
                                    <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Filtrele</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Log ID</th>
                                    <th>Personel Adı</th>
                                    <th>İşlem Tipi</th>
                                    <th>İşlem Detayı</th>
                                    <th>IP Adresi</th>
                                    <th>Tarih</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['log_id']); ?></td>
                                    <td><?php echo htmlspecialchars($log['ad'] . ' ' . $log['soyad']); ?></td>
                                    <td><?php echo htmlspecialchars($log['islem_tipi']); ?></td>
                                    <td><?php echo htmlspecialchars($log['islem_detay']); ?></td>
                                    <td><?php echo htmlspecialchars($log['ip_adresi']); ?></td>
                                    <td><?php echo htmlspecialchars($log['tarih']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>