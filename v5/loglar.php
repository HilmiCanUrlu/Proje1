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
        .collapse {
            background-color: #f1f3f5;
        }
        .collapse .nav-link {
            padding: 0.6rem 1rem 0.6rem 2.5rem;
            font-size: 0.95rem;
        }
        .collapse .nav-link i {
            font-size: 1rem;
            width: 20px;
        }
        .collapse .nav-link:hover {
            background-color: #e9ecef;
        }
        .bi-chevron-down {
            transition: transform 0.3s ease;
            color: #6c757d !important;
        }
        [aria-expanded="true"] .bi-chevron-down {
            transform: rotate(180deg);
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sol Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Ana İçerik -->
            <div class="col-md-10 py-3">
                <!-- Başlık ve Açıklama -->
                <div class="page-header">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-clock-history text-primary" style="font-size: 24px;"></i>
                        <h2 class="mb-0">Sistem Logları</h2>
                    </div>
                </div>

                <!-- Filter Panel -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="section-title">
                            <i class="bi bi-funnel-fill"></i>
                            Filtreleme Seçenekleri
                        </div>
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="operation_type" class="form-label">İşlem Tipi</label>
                                    <select name="operation_type" class="form-select">
                                        <option value="">Tümü</option>
                                        <option value="LOGIN" <?php echo ($operation_type == 'LOGIN') ? 'selected' : ''; ?>>Giriş</option>
                                        <option value="LOGIN_FAILED" <?php echo ($operation_type == 'LOGIN_FAILED') ? 'selected' : ''; ?>>Başarısız Giriş</option>
                                        <option value="DOSYA_EKLE" <?php echo ($operation_type == 'DOSYA_EKLE') ? 'selected' : ''; ?>>Dosya Ekle</option>
                                        <option value="DOSYA_GUNCELLE" <?php echo ($operation_type == 'DOSYA_GUNCELLE') ? 'selected' : ''; ?>>Dosya Güncelle</option>
                                        <option value="MUSTERI_EKLE" <?php echo ($operation_type == 'MUSTERI_EKLE') ? 'selected' : ''; ?>>Müşteri Ekle</option>
                                        <option value="MUSTERI_GUNCELLE" <?php echo ($operation_type == 'MUSTERI_GUNCELLE') ? 'selected' : ''; ?>>Müşteri Güncelle</option>
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
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-2"></i>Filtrele
                            </button>
                            <a href="loglar.php" class="btn btn-secondary ms-2">
                                <i class="bi bi-arrow-repeat me-2"></i>Sıfırla
                            </a>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="section-title">
                            <i class="bi bi-list-ul"></i>
                            Log Kayıtları
                            <span class="badge bg-primary ms-2"><?php echo count($logs); ?> kayıt</span>
                            
                            <!-- İndirme butonları -->
                            <div class="ms-auto">
                                <button class="btn btn-sm btn-success" onclick="exportToExcel()">
                                    <i class="bi bi-file-earmark-excel me-1"></i>Excel İndir
                                </button>
                                <button class="btn btn-sm btn-danger ms-2" onclick="exportToPDF()">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF İndir
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover" id="logTable">
                                <thead>
                                    <tr>
                                        <th scope="col">#ID</th>
                                        <th scope="col">Personel</th>
                                        <th scope="col">İşlem Tipi</th>
                                        <th scope="col">İşlem Detayı</th>
                                        <th scope="col">IP Adresi</th>
                                        <th scope="col">Tarih</th>
                                        <th scope="col">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($logs) > 0): ?>
                                        <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($log['log_id']); ?></td>
                                            <td>
                                                <i class="bi bi-person-fill text-primary me-1"></i>
                                                <?php echo htmlspecialchars($log['ad'] . ' ' . $log['soyad']); ?>
                                            </td>
                                            <td>
                                                <?php 
                                                // İşlem tipine göre farklı ikonlar
                                                $icon = 'bi-activity';
                                                $badgeClass = 'bg-secondary';
                                                
                                                switch($log['islem_tipi']) {
                                                    case 'LOGIN':
                                                        $icon = 'bi-box-arrow-in-right';
                                                        $badgeClass = 'bg-success';
                                                        break;
                                                    case 'LOGIN_FAILED':
                                                        $icon = 'bi-x-circle';
                                                        $badgeClass = 'bg-danger';
                                                        break;
                                                    case 'DOSYA_EKLE':
                                                    case 'MUSTERI_EKLE':
                                                        $icon = 'bi-plus-circle';
                                                        $badgeClass = 'bg-primary';
                                                        break;
                                                    case 'DOSYA_GUNCELLE':
                                                    case 'MUSTERI_GUNCELLE':
                                                        $icon = 'bi-pencil-square';
                                                        $badgeClass = 'bg-warning text-dark';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>">
                                                    <i class="bi <?php echo $icon; ?> me-1"></i>
                                                    <?php echo htmlspecialchars($log['islem_tipi']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars(substr($log['islem_detay'], 0, 50) . (strlen($log['islem_detay']) > 50 ? '...' : '')); ?></td>
                                            <td><i class="bi bi-globe me-1"></i><?php echo htmlspecialchars($log['ip_adresi']); ?></td>
                                            <td><i class="bi bi-calendar3 me-1"></i><?php echo date('d.m.Y H:i:s', strtotime($log['tarih'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info log-detail-btn"
                                                   data-log-id="<?php echo htmlspecialchars($log['log_id']); ?>"
                                                   data-personel="<?php echo htmlspecialchars($log['ad'] . ' ' . $log['soyad']); ?>"
                                                   data-islem-tipi="<?php echo htmlspecialchars($log['islem_tipi']); ?>"
                                                   data-ip="<?php echo htmlspecialchars($log['ip_adresi']); ?>"
                                                   data-tarih="<?php echo htmlspecialchars($log['tarih']); ?>"
                                                   data-detay="<?php echo htmlspecialchars($log['islem_detay']); ?>">
                                                    <i class="bi bi-eye"></i> Detay
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 24px;"></i>
                                                <p class="mt-2">Filtreleme kriterlerine uygun log kaydı bulunamadı.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Detay Modal -->
    <div class="modal fade" id="logDetailModal" tabindex="-1" aria-labelledby="logDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logDetailModalLabel">Log Detayları</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6><i class="bi bi-person-badge"></i> Personel Bilgileri</h6>
                                    <div id="modal-personel"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6><i class="bi bi-activity"></i> İşlem Bilgileri</h6>
                                    <div id="modal-islem"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6><i class="bi bi-card-text"></i> İşlem Detayı</h6>
                                    <div id="modal-detay" class="border p-3 bg-white rounded"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- ExcelJS ve jsPDF için gerekli kütüphaneler -->
    <script src="https://unpkg.com/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    
    <script>
        // Detay butonlarını dinle
        document.addEventListener('DOMContentLoaded', function() {
            // Log detay butonlarına event listener ekle
            document.querySelectorAll('.log-detail-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const logId = this.getAttribute('data-log-id');
                    const personel = this.getAttribute('data-personel');
                    const islemTipi = this.getAttribute('data-islem-tipi');
                    const ip = this.getAttribute('data-ip');
                    const tarih = this.getAttribute('data-tarih');
                    const detay = this.getAttribute('data-detay');
                    
                    showLogDetail(logId, personel, islemTipi, ip, tarih, detay);
                });
            });
            
            // Aktif menüyü işaretle
            markActiveMenu();
        });
        
        // Log detay modalını göster
        function showLogDetail(logId, personel, islemTipi, ip, tarih, detay) {
            // Personel bilgileri
            document.getElementById('modal-personel').innerHTML = `
                <p><strong>Personel:</strong> ${personel}</p>
                <p><strong>IP Adresi:</strong> ${ip}</p>
            `;
            
            // İşlem bilgileri
            document.getElementById('modal-islem').innerHTML = `
                <p><strong>Log ID:</strong> ${logId}</p>
                <p><strong>İşlem Tipi:</strong> ${islemTipi}</p>
                <p><strong>Tarih:</strong> ${formatDate(tarih)}</p>
            `;
            
            // İşlem detayı
            document.getElementById('modal-detay').innerText = detay;
            
            // Modal başlığını güncelle
            document.getElementById('logDetailModalLabel').innerText = `Log Detayı #${logId}`;
            
            // Modalı göster
            const logDetailModal = new bootstrap.Modal(document.getElementById('logDetailModal'));
            logDetailModal.show();
        }
        
        // Tarih formatını düzenleyen fonksiyon
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('tr-TR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
        
        // Excel'e aktarma fonksiyonu
        function exportToExcel() {
            const table = document.getElementById('logTable');
            const ws = XLSX.utils.table_to_sheet(table);
            
            // Excel dosyasını oluştur
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Sistem Logları');
            
            // Dosya adını tarih ile birlikte oluştur
            const fileName = `sistem_loglari_${new Date().toISOString().slice(0, 10)}.xlsx`;
            
            // Excel dosyasını indir
            XLSX.writeFile(wb, fileName);
        }
        
        // PDF'e aktarma fonksiyonu
        function exportToPDF() {
            // jsPDF nesnesini oluştur
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // PDF başlığı
            doc.text('Sistem Logları', 14, 16);
            
            // Tabloyu PDF'e çevir
            doc.autoTable({
                html: '#logTable',
                startY: 20,
                styles: { fontSize: 8 },
                columnStyles: { 0: { cellWidth: 15 } },
                columns: [
                    { header: 'ID', dataKey: 0 },
                    { header: 'Personel', dataKey: 1 },
                    { header: 'İşlem Tipi', dataKey: 2 },
                    { header: 'İşlem Detay', dataKey: 3 },
                    { header: 'IP Adresi', dataKey: 4 },
                    { header: 'Tarih', dataKey: 5 }
                ],
                didDrawPage: function (data) {
                    // Sayfa alt bilgisi
                    doc.setFontSize(8);
                    doc.text(`Rapor Tarihi: ${new Date().toLocaleString('tr-TR')}`, 14, doc.internal.pageSize.height - 10);
                }
            });
            
            // Dosya adını tarih ile birlikte oluştur
            const fileName = `sistem_loglari_${new Date().toISOString().slice(0, 10)}.pdf`;
            
            // PDF dosyasını indir
            doc.save(fileName);
        }
        
        // Aktif menüyü işaretle
        function markActiveMenu() {
            const currentPath = window.location.pathname;
            const filename = currentPath.split('/').pop();

            // Tüm nav linklerinden active class'ını kaldır
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });

            // Collapse menülerini kapat
            document.querySelectorAll('.collapse').forEach(collapse => {
                collapse.classList.remove('show');
            });
            document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(toggle => {
                toggle.setAttribute('aria-expanded', 'false');
            });

            // Mevcut sayfaya göre ilgili menüyü aktif yap
            document.querySelectorAll('.nav-link').forEach(link => {
                const href = link.getAttribute('href');
                if (href && href === filename) {
                    link.classList.add('active');
                    
                    // Eğer bu bir alt menü öğesiyse, üst menüyü de aç
                    const parentCollapse = link.closest('.collapse');
                    if (parentCollapse) {
                        parentCollapse.classList.add('show');
                        const parentToggle = document.querySelector(`[href="#${parentCollapse.id}"]`);
                        if (parentToggle) {
                            parentToggle.classList.add('active');
                            parentToggle.setAttribute('aria-expanded', 'true');
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>