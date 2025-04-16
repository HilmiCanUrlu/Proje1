<?php
session_start();
require_once "database.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['dosya_id'])) {
    header("Location: dosya_takip.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Get file details
$query = "SELECT d.*, m.*, p.ad as personel_ad, p.soyad as personel_soyad 
          FROM dosyalar d 
          LEFT JOIN musteriler m ON d.musteri_id = m.musteri_id 
          LEFT JOIN personel p ON d.personel_id = p.personel_id 
          WHERE d.dosya_id = :dosya_id";

$stmt = $db->prepare($query);
$stmt->execute([':dosya_id' => $_GET['dosya_id']]);
$dosya = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dosya) {
    header("Location: dosya_takip.php");
    exit;
}

// Get customer's total file count
$query = "SELECT COUNT(*) as toplam_dosya FROM dosyalar WHERE musteri_id = :musteri_id";
$stmt = $db->prepare($query);
$stmt->execute([':musteri_id' => $dosya['musteri_id']]);
$toplam_dosya = $stmt->fetch(PDO::FETCH_ASSOC)['toplam_dosya'];

// Initialize accounting variables
$muhasebe = [
    'toplam_tutar' => 0,
    'yapilan_odeme' => 0,
    'kalan_tutar' => 0
];
$muhasebe_islemleri = [];

// Check if muhasebe table exists and get data
try {
    $query = "SELECT * FROM muhasebe WHERE dosya_id = :dosya_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':dosya_id' => $dosya['dosya_id']]);
    $muhasebe = $stmt->fetch(PDO::FETCH_ASSOC) ?: $muhasebe;
    
    // Check if muhasebe_islemleri table exists and get data
    $query = "SELECT * FROM muhasebe_islemleri WHERE dosya_id = :dosya_id ORDER BY olusturma_tarihi DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([':dosya_id' => $dosya['dosya_id']]);
    $muhasebe_islemleri = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Tables don't exist, we'll use empty data
    error_log("Accounting tables not found: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya Detay - <?php echo htmlspecialchars($dosya['dosya_turu']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
        }
        .container-fluid {
            padding: 20px;
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
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            cursor: pointer;
        }
        .status-active {
            background-color: #28a745;
            color: white;
        }
        .status-passive {
            background-color: #dc3545;
            color: white;
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
        .section-title i {
            font-size: 20px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 8px 0;
            border: none;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 200px;
            color: #666;
        }
        .section-card {
            background: white;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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
        }
        .card-body {
            padding: 1.25rem;
        }
        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .text-muted {
            color: #6c757d !important;
        }
        .form-select, .form-control {
            border: 1px solid #ced4da;
        }
        .form-select:focus, .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        .muhasebe-table th {
            background-color: #f8f9fa;
        }
        .muhasebe-table tr:hover {
            background-color: #f8f9fa;
        }
        .nav-item.dropdown {
            margin-bottom: 0.5rem;
        }
        .nav-item.dropdown .nav-link[aria-expanded="true"] {
            background-color: #0d6efd;
            color: white;
        }
        .nav-item.dropdown .nav-link[aria-expanded="true"] i {
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sol Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-10 py-3">
                <!-- Header -->
                <div class="page-header">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-folder-fill text-primary"></i>
                        <h2 class="mb-0"><?php echo htmlspecialchars($dosya['dosya_turu']); ?></h2>
                    </div>
                    <span class="status-badge status-active" onclick="toggleStatus(this)" data-status="active">Aktif</span>
                </div>

                <div class="row">
                    <!-- Left Column - Main Content -->
                    <div class="col-md-9">
                        <div class="row g-3">
                            <!-- Plot and Customer Info Row -->
                            <div class="col-md-6">
                                <!-- Plot Information -->
                                <div class="section-card">
                                    <div class="section-title">
                                        <i class="bi bi-geo-alt-fill"></i>
                                        Parsel Bilgileri
                                        <button class="btn btn-secondary btn-sm ms-auto" onclick="editParselBilgileri()">Düzenle</button>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-12">
                                            <div class="mb-2">
                                                <label class="small text-muted">Tapu Maliki</label>
                                                <div class="d-flex align-items-center">
                                                    <span id="tapuMaliki"><?php echo htmlspecialchars($dosya['tapu_maliki'] ?? $dosya['musteri_adi']); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="small text-muted">İli</label>
                                                <div><?php echo htmlspecialchars($dosya['il']); ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="small text-muted">İlçesi</label>
                                                <div><?php echo htmlspecialchars($dosya['ilce']); ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-2">
                                                <label class="small text-muted">Mahallesi</label>
                                                <div><?php echo htmlspecialchars($dosya['mahalle']); ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="small text-muted">Ada No</label>
                                                <div><?php echo htmlspecialchars($dosya['ada']); ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="small text-muted">Parsel No</label>
                                                <div><?php echo htmlspecialchars($dosya['parsel']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Customer Information -->
                                <div class="section-card">
                                    <div class="section-title">
                                        <i class="bi bi-person-vcard-fill"></i>
                                        Müşteri Bilgileri
                                        <button class="btn btn-secondary btn-sm ms-auto" onclick="editMusteriBilgileri()">Düzenle</button>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-12">
                                            <div class="mb-2">
                                                <label class="small text-muted">Müşteri Adı</label>
                                                <div><?php echo htmlspecialchars($dosya['musteri_adi']); ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="small text-muted">Telefon</label>
                                                <div><?php echo htmlspecialchars($dosya['telefon']); ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="small text-muted">E-posta</label>
                                                <div><?php echo htmlspecialchars($dosya['email'] ?? ''); ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="small text-muted">TC Kimlik No</label>
                                                <div><?php echo htmlspecialchars($dosya['tc_no'] ?? ''); ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="small text-muted">Vergi No</label>
                                                <div><?php echo htmlspecialchars($dosya['vergi_no'] ?? ''); ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="small text-muted">Dosya Sayısı</label>
                                                <div><?php echo $toplam_dosya; ?> Dosya Kayıtlı</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Hesap Kartı -->
                            <div class="col-md-12">
                                <div class="section-card">
                                    <div class="section-title">
                                        <i class="bi bi-credit-card-fill"></i>
                                        Hesap Kartı
                                        <button class="btn btn-success btn-sm ms-auto" onclick="showMuhasebeDetay(<?php echo $dosya['dosya_id']; ?>, true)">
                                            </>Muhasebe
                                        </button>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="card bg-primary text-white">
                                                <div class="card-body">
                                                    <h6>Toplam Tutar</h6>
                                                    <h3 id="toplamTutarOzet"><?php echo number_format($muhasebe['toplam_tutar'] ?? 0, 2, ',', '.'); ?> ₺</h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-success text-white">
                                                <div class="card-body">
                                                    <h6>Yapılan Ödemeler</h6>
                                                    <h3 id="yapilanOdemeOzet"><?php echo number_format($muhasebe['yapilan_odeme'] ?? 0, 2, ',', '.'); ?> ₺</h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-warning text-white">
                                                <div class="card-body">
                                                    <h6>Kalan Tutar</h6>
                                                    <h3 id="kalanTutarOzet"><?php echo number_format($muhasebe['kalan_tutar'] ?? 0, 2, ',', '.'); ?> ₺</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- File Info and Personnel -->
                            <div class="col-md-6">
                                <div class="section-card">
                                    <div class="section-title">
                                        <i class="bi bi-info-circle-fill"></i>
                                        Dosya Bilgileri
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="small text-muted">Oluşturulma Tarihi</label>
                                                <div><?php echo date('d.m.Y H:i', strtotime($dosya['olusturma_tarihi'])); ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="small text-muted">Oluşturan Personel</label>
                                                <div><?php echo htmlspecialchars($dosya['personel_ad'] . ' ' . $dosya['personel_soyad']); ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="small text-muted">Son Güncelleme</label>
                                                <div><?php echo isset($dosya['guncelleme_tarihi']) ? date('d.m.Y H:i', strtotime($dosya['guncelleme_tarihi'])) : '-'; ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="small text-muted">Dosya Durumu</label>
                                                <div><?php echo htmlspecialchars($dosya['dosya_durumu'] ?? 'Aktif'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="section-card">
                                    <div class="section-title">
                                        <i class="bi bi-people-fill"></i>
                                        Yetkili Personel
                                        <button class="btn btn-secondary btn-sm ms-auto" onclick="editYetkiliPersonel()">Personel Ekle</button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Personel</th>
                                                    <th>Yetki</th>
                                                    <th>Durum</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($dosya['personel_ad'] . ' ' . $dosya['personel_soyad']); ?></td>
                                                    <td>Tam Yetki</td>
                                                    <td><span class="badge bg-success">Aktif</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Tasks, File Attachments, Notes -->
                    <div class="col-md-3">
                        <!-- Tasks -->
                        <div class="section-card">
                            <div class="section-title">
                                <i class="bi bi-list-task"></i>
                                Görevler
                                <button class="btn btn-secondary btn-sm ms-auto">Görev Ekle</button>
                            </div>
                            <div class="text-center text-muted py-4">
                                Görev Eklenmedi
                            </div>
                        </div>

                        <!-- File Attachments -->
                        <div class="section-card">
                            <div class="section-title">
                                <i class="bi bi-paperclip"></i>
                                Dosya Ekleri
                                <button class="btn btn-secondary btn-sm ms-auto">Ek Yükle</button>
                            </div>
                            <div class="text-center text-muted py-4">
                                Dosya Eki Yüklenmedi
                                <div class="mt-2">
                                    <small>Dosyanıza ait ekler yükleyebilirsiniz.</small><br>
                                    <small>Yüklenen ekleri firma personeli görüntüleyebilir.</small>
                                </div>
                            </div>
                        </div>

                        <!-- File Notes -->
                        <div class="section-card">
                            <div class="section-title">
                                <i class="bi bi-journal-text"></i>
                                Dosya Notları
                            </div>
                            <div class="mb-3">
                                <select class="form-select mb-3">
                                    <option>İşlem Seçiniz</option>
                                </select>
                                <textarea class="form-control" rows="3" placeholder="Not Ekle"></textarea>
                                <button class="btn btn-primary w-100 mt-3">KAYDET</button>
                            </div>
                            <div class="text-center text-muted py-4">
                                Not Eklenmedi
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Accounting Modal -->
    <div class="modal fade" id="muhasebeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Muhasebe Detayları</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6>Toplam Tutar</h6>
                                    <h3 id="modalToplamTutar"><?php echo number_format($muhasebe['toplam_tutar'] ?? 0, 2, ',', '.'); ?> ₺</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6>Yapılan Ödemeler</h6>
                                    <h3 id="modalYapilanOdeme"><?php echo number_format($muhasebe['yapilan_odeme'] ?? 0, 2, ',', '.'); ?> ₺</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h6>Kalan Tutar</h6>
                                    <h3 id="modalKalanTutar"><?php echo number_format($muhasebe['kalan_tutar'] ?? 0, 2, ',', '.'); ?> ₺</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div class="table-responsive">
                        <table class="table table-striped muhasebe-table">
                            <thead>
                                <tr>
                                    <th>İşlem No</th>
                                    <th>Tarih</th>
                                    <th>Toplam Tutar</th>
                                    <th>Yapılan Ödeme</th>
                                    <th>Kalan Tutar</th>
                                    <th>Açıklama</th>
                                </tr>
                            </thead>
                            <tbody id="muhasebeIslemlerListesi">
                                <?php foreach ($muhasebe_islemleri as $islem): ?>
                                <tr>
                                    <td><?php echo $islem['islem_id']; ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($islem['olusturma_tarihi'])); ?></td>
                                    <td><?php echo number_format($islem['toplam_tutar'], 2, ',', '.'); ?> ₺</td>
                                    <td><?php echo number_format($islem['yapilan_tutar'], 2, ',', '.'); ?> ₺</td>
                                    <td><?php echo number_format($islem['kalan_tutar'], 2, ',', '.'); ?> ₺</td>
                                    <td><?php echo htmlspecialchars($islem['aciklama'] ?? ''); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="showIslemEkle()">
                        <i class="bi bi-plus-circle me-2"></i>Yeni İşlem Ekle
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Transaction Modal -->
    <div class="modal fade" id="islemEkleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni İşlem Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="islemEkleForm">
                        <input type="hidden" id="islemDosyaId" name="dosya_id" value="<?php echo $dosya['dosya_id']; ?>">
                        <div class="mb-3" id="toplamTutarDiv">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Toplam Tutar</label>
                                <button type="button" class="btn btn-sm btn-warning" id="toplamTutarDegistir">
                                    Toplam Tutarı Değiştir
                                </button>
                            </div>
                            <input type="number" class="form-control" id="toplamTutar" name="toplam_tutar" step="0.01" value="<?php echo $muhasebe['toplam_tutar'] ?? 0; ?>" readonly>
                            <small class="text-muted">Bu alan sadece ilk işlemde veya değiştirme butonu ile düzenlenebilir.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Yapılan Ödeme</label>
                            <input type="number" class="form-control" id="yapilanTutar" name="yapilan_tutar" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kalan Tutar</label>
                            <input type="number" class="form-control" id="kalanTutar" name="kalan_tutar" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Açıklama</label>
                            <textarea class="form-control" name="aciklama" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Sayfa yüklendiğinde muhasebe bilgilerini çek
    document.addEventListener('DOMContentLoaded', function() {
        showMuhasebeDetay(<?php echo $dosya['dosya_id']; ?>, false);
        markActiveMenu();
    });

    // Parsel Bilgileri Düzenleme
    function editParselBilgileri() {
        // Müşterileri AJAX ile getir ve sonra form oluştur
        fetch('get_musteriler.php')
            .then(response => response.json())
            .then(musteriler => {
                // Parsel bilgilerini düzenlemek için form oluştur
                const parselForm = `
                    <div class="parsel-edit-form">
                        <div class="row g-2">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="small text-muted">Tapu Maliki</label>
                                    <select class="form-select form-select-sm mb-2" id="edit-malik-select" onchange="updateTapuMaliki()">
                                        <option value="">-- Kayıtlı müşterilerden seçin --</option>
                                        ${musteriler.map(musteri => 
                                            `<option value="${musteri.musteri_adi}" ${musteri.musteri_id == <?php echo $dosya['musteri_id'] ?: 0; ?> ? 'selected' : ''}>${musteri.musteri_adi}</option>`
                                        ).join('')}
                                    </select>
                                    <input type="text" class="form-control form-control-sm" id="edit-tapu-maliki" value="${<?php echo json_encode(htmlspecialchars($dosya['tapu_maliki'] ?? $dosya['musteri_adi'])); ?>}">
                                    <small class="text-muted">Birden fazla malik için isimleri virgülle ayırarak yazabilirsiniz.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="small text-muted">İli</label>
                                    <input type="text" class="form-control form-control-sm" id="edit-il" value="${<?php echo json_encode(htmlspecialchars($dosya['il'])); ?>}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="small text-muted">İlçesi</label>
                                    <input type="text" class="form-control form-control-sm" id="edit-ilce" value="${<?php echo json_encode(htmlspecialchars($dosya['ilce'])); ?>}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-2">
                                    <label class="small text-muted">Mahallesi</label>
                                    <input type="text" class="form-control form-control-sm" id="edit-mahalle" value="${<?php echo json_encode(htmlspecialchars($dosya['mahalle'])); ?>}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="small text-muted">Ada No</label>
                                    <input type="text" class="form-control form-control-sm" id="edit-ada" value="${<?php echo json_encode(htmlspecialchars($dosya['ada'])); ?>}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="small text-muted">Parsel No</label>
                                    <input type="text" class="form-control form-control-sm" id="edit-parsel" value="${<?php echo json_encode(htmlspecialchars($dosya['parsel'])); ?>}">
                                </div>
                            </div>
                            <div class="col-12 mt-3">
                                <button class="btn btn-primary btn-sm" onclick="saveParselBilgileri(<?php echo $dosya['dosya_id']; ?>)">Kaydet</button>
                                <button class="btn btn-secondary btn-sm" onclick="cancelParselEdit()">İptal</button>
                            </div>
                        </div>
                    </div>
                `;
                
                // Form içeriğini görüntüle
                const parselBilgileriCard = document.querySelector('.section-card:nth-child(1)');
                const parselBilgileriContent = parselBilgileriCard.querySelector('.row.g-2');
                
                // Mevcut içeriği sakla ve formu göster
                parselBilgileriContent.setAttribute('data-original', parselBilgileriContent.innerHTML);
                parselBilgileriContent.innerHTML = parselForm;
            })
            .catch(error => {
                console.error('AJAX Hatası:', error);
                alert('Müşteri bilgileri yüklenirken bir hata oluştu: ' + error.message);
            });
    }

    // Müşteri seçildiğinde tapu maliki alanını güncelle
    function updateTapuMaliki() {
        const malikSelect = document.getElementById('edit-malik-select');
        const tapuMalikiInput = document.getElementById('edit-tapu-maliki');
        
        if (malikSelect.value) {
            tapuMalikiInput.value = malikSelect.value;
        }
    }

    function saveParselBilgileri(dosyaId) {
        const tapuMaliki = document.getElementById('edit-tapu-maliki').value;
        const il = document.getElementById('edit-il').value;
        const ilce = document.getElementById('edit-ilce').value;
        const mahalle = document.getElementById('edit-mahalle').value;
        const ada = document.getElementById('edit-ada').value;
        const parsel = document.getElementById('edit-parsel').value;
        
        const formData = new FormData();
        formData.append('dosya_id', dosyaId);
        formData.append('tapu_maliki', tapuMaliki);
        formData.append('il', il);
        formData.append('ilce', ilce);
        formData.append('mahalle', mahalle);
        formData.append('ada', ada);
        formData.append('parsel', parsel);
        formData.append('action', 'updateParselBilgileri');
        
        // AJAX isteği gönderiyoruz
        fetch('update_dosya.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Parsel bilgileri başarıyla güncellendi');
                // Sayfayı yenileyerek güncel verileri göster
                location.reload();
            } else {
                alert('Bir hata oluştu: ' + data.message);
                cancelParselEdit();
            }
        })
        .catch(error => {
            console.error('AJAX Hatası:', error);
            alert('Bir hata oluştu: ' + error.message);
            cancelParselEdit();
        });
    }

    function cancelParselEdit() {
        const parselBilgileriCard = document.querySelector('.section-card:nth-child(1)');
        const parselBilgileriContent = parselBilgileriCard.querySelector('.row.g-2');
        
        // Orijinal içeriği geri yükle
        parselBilgileriContent.innerHTML = parselBilgileriContent.getAttribute('data-original');
    }

    // Müşteri Bilgileri Düzenleme
    function editMusteriBilgileri() {
        // Müşteri bilgilerini düzenlemek için form oluştur
        const musteriForm = `
            <div class="musteri-edit-form">
                <div class="row g-2">
                    <div class="col-md-12">
                        <div class="mb-2">
                            <label class="small text-muted">Müşteri Adı</label>
                            <select class="form-select form-select-sm" id="edit-musteri-id">
                                <option value="">Müşteri Seçiniz</option>
                                <!-- Müşterileri AJAX ile yükle -->
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="update-tapu-maliki" checked>
                            <label class="form-check-label small" for="update-tapu-maliki">
                                Tapu malikini de güncelle
                            </label>
                            <small class="text-muted d-block">İşaretlenirse, dosyanın tapu maliki seçilen müşteri olarak güncellenecektir</small>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <button class="btn btn-primary btn-sm" onclick="saveMusteriBilgileri(<?php echo $dosya['dosya_id']; ?>)">Kaydet</button>
                        <button class="btn btn-secondary btn-sm" onclick="cancelMusteriEdit()">İptal</button>
                    </div>
                </div>
            </div>
        `;
        
        // Form içeriğini görüntüle
        const musteriBilgileriCard = document.querySelector('.section-card:nth-child(2)');
        const musteriBilgileriContent = musteriBilgileriCard.querySelector('.row.g-2');
        
        // Mevcut içeriği sakla ve formu göster
        musteriBilgileriContent.setAttribute('data-original', musteriBilgileriContent.innerHTML);
        musteriBilgileriContent.innerHTML = musteriForm;
        
        // Müşterileri AJAX ile yükle
        fetch('get_musteriler.php')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('edit-musteri-id');
                data.forEach(musteri => {
                    const option = document.createElement('option');
                    option.value = musteri.musteri_id;
                    option.text = musteri.musteri_adi + ' (' + musteri.musteri_turu + ')';
                    option.setAttribute('data-musteri-adi', musteri.musteri_adi);
                    if (musteri.musteri_id == <?php echo $dosya['musteri_id'] ?: 0; ?>) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            })
            .catch(error => {
                console.error('AJAX Hatası:', error);
            });
    }

    function saveMusteriBilgileri(dosyaId) {
        const musteriId = document.getElementById('edit-musteri-id').value;
        const updateTapuMaliki = document.getElementById('update-tapu-maliki').checked;
        const select = document.getElementById('edit-musteri-id');
        const musteriAdi = select.options[select.selectedIndex].getAttribute('data-musteri-adi');
        
        const formData = new FormData();
        formData.append('dosya_id', dosyaId);
        formData.append('musteri_id', musteriId);
        
        // Eğer tapu malikini de güncelleme seçeneği işaretlendiyse
        if (updateTapuMaliki && musteriAdi) {
            formData.append('tapu_maliki', musteriAdi);
            formData.append('update_tapu_maliki', '1');
        }
        
        formData.append('action', 'updateMusteriBilgileri');
        
        // AJAX isteği gönderiyoruz
        fetch('update_dosya.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Müşteri bilgileri başarıyla güncellendi');
                // Sayfayı yenileyerek güncel verileri göster
                location.reload();
            } else {
                alert('Bir hata oluştu: ' + data.message);
                cancelMusteriEdit();
            }
        })
        .catch(error => {
            console.error('AJAX Hatası:', error);
            alert('Bir hata oluştu: ' + error.message);
            cancelMusteriEdit();
        });
    }

    function cancelMusteriEdit() {
        const musteriBilgileriCard = document.querySelector('.section-card:nth-child(2)');
        const musteriBilgileriContent = musteriBilgileriCard.querySelector('.row.g-2');
        
        // Orijinal içeriği geri yükle
        musteriBilgileriContent.innerHTML = musteriBilgileriContent.getAttribute('data-original');
    }

    // Yetkili Personel Ekleme
    function editYetkiliPersonel() {
        // Personel listesini getir ve bir modal göster
        alert('Personel ekleme fonksiyonu henüz aktif değil.');
    }

    function duzenle(elementId) {
        const element = document.getElementById(elementId);
        const currentValue = element.textContent;
        element.innerHTML = `
            <input type="text" class="form-control form-control-sm" value="${currentValue}">
            <button class="btn btn-sm btn-success ms-2" onclick="kaydet('${elementId}')">
                <i class="bi bi-check"></i>
            </button>
            <button class="btn btn-sm btn-danger ms-2" onclick="iptal('${elementId}', '${currentValue}')">
                <i class="bi bi-x"></i>
            </button>
        `;
    }

    function kaydet(elementId) {
        const input = document.getElementById(elementId).querySelector('input');
        const newValue = input.value;
        document.getElementById(elementId).textContent = newValue;
    }

    function iptal(elementId, oldValue) {
        document.getElementById(elementId).textContent = oldValue;
    }

    function toggleStatus(element) {
        const isActive = element.getAttribute('data-status') === 'active';
        if (isActive) {
            element.classList.remove('status-active');
            element.classList.add('status-passive');
            element.textContent = 'Pasif';
            element.setAttribute('data-status', 'passive');
        } else {
            element.classList.remove('status-passive');
            element.classList.add('status-active');
            element.textContent = 'Aktif';
            element.setAttribute('data-status', 'active');
        }
    }

    let currentDosyaId = <?php echo $dosya['dosya_id']; ?>;
    let currentKalanTutar = <?php echo $muhasebe['kalan_tutar'] ?? 0; ?>;
    let isToplamTutarEditable = false;

    function showMuhasebeDetay(dosyaId, showModal = false) {
        currentDosyaId = dosyaId;
        document.getElementById('islemDosyaId').value = dosyaId;
        
        fetch(`muhasebe_detay.php?dosya_id=${dosyaId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Güncelle hem modal hem de ana sayfadaki özet kartları
                    const formatNumber = (num) => parseFloat(num).toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                    
                    // Ana sayfadaki özet kartları güncelle
                    document.getElementById('toplamTutarOzet').textContent = formatNumber(data.ozet.toplam_tutar);
                    document.getElementById('yapilanOdemeOzet').textContent = formatNumber(data.ozet.toplam_yapilan);
                    document.getElementById('kalanTutarOzet').textContent = formatNumber(data.ozet.toplam_kalan);

                    // Modal içindeki özet kartları güncelle
                    document.getElementById('modalToplamTutar').textContent = formatNumber(data.ozet.toplam_tutar);
                    document.getElementById('modalYapilanOdeme').textContent = formatNumber(data.ozet.toplam_yapilan);
                    document.getElementById('modalKalanTutar').textContent = formatNumber(data.ozet.toplam_kalan);

                    // İşlemler tablosunu güncelle
                    let islemlerHTML = '';
                    if (data.islemler && data.islemler.length > 0) {
                        data.islemler.forEach(islem => {
                            const tarih = new Date(islem.tarih);
                            const formatliTarih = tarih.toLocaleString('tr-TR');

                            islemlerHTML += `
                                <tr>
                                    <td>${islem.muhasebe_id}</td>
                                    <td>${formatliTarih}</td>
                                    <td>${formatNumber(islem.toplam_tutar)}</td>
                                    <td>${formatNumber(islem.yapilan_odeme)}</td>
                                    <td>${formatNumber(islem.kalan_tutar)}</td>
                                    <td>${islem.aciklama || ''}</td>
                                </tr>
                            `;
                        });
                    } else {
                        islemlerHTML = '<tr><td colspan="6" class="text-center">Henüz işlem bulunmuyor</td></tr>';
                    }
                    document.getElementById('muhasebeIslemlerListesi').innerHTML = islemlerHTML;

                    // Modalı sadece istenirse göster
                    if (showModal) {
                        new bootstrap.Modal(document.getElementById('muhasebeModal')).show();
                    }
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                console.error('AJAX Hatası:', error);
                alert('Bir hata oluştu: ' + error.message);
            });
    }

    // İşlem ekleme formunu gönder
    document.getElementById('islemEkleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('islem_ekle.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('İşlem başarıyla eklendi');
                bootstrap.Modal.getInstance(document.getElementById('islemEkleModal')).hide();
                // Sayfayı yenilemek yerine verileri güncelle
                showMuhasebeDetay(currentDosyaId);
            } else {
                alert('Bir hata oluştu: ' + data.message);
            }
        })
        .catch(error => {
            console.error('AJAX Hatası:', error);
            alert('Bir hata oluştu: ' + error.message);
        });
    });

    // Toplam tutar değiştirme işlemi sonrası
    document.getElementById('toplamTutarDegistir').addEventListener('click', function() {
        const toplamTutarInput = document.getElementById('toplamTutar');
        const yapilanTutarInput = document.getElementById('yapilanTutar');
        const kalanTutarInput = document.getElementById('kalanTutar');
        isToplamTutarEditable = !isToplamTutarEditable;
        
        if (isToplamTutarEditable) {
            // Enable edit mode
            toplamTutarInput.readOnly = false;
            yapilanTutarInput.required = false;
            yapilanTutarInput.value = '';
            kalanTutarInput.value = '';
            this.classList.remove('btn-warning');
            this.classList.add('btn-success');
            this.textContent = 'Değişikliği Onayla';
        } else {
            // Confirm and save changes
            toplamTutarInput.readOnly = true;
            this.classList.remove('btn-success');
            this.classList.add('btn-warning');
            this.textContent = 'Toplam Tutarı Değiştir';
            
            const yeniToplamTutar = parseFloat(toplamTutarInput.value) || 0;
            
            // Save total amount change
            const formData = new FormData();
            formData.append('dosya_id', currentDosyaId);
            formData.append('toplam_tutar', yeniToplamTutar);
            formData.append('is_toplam_tutar_update', '1');
            
            fetch('islem_ekle.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Sayfayı yenilemek yerine verileri güncelle
                    showMuhasebeDetay(currentDosyaId);
                    yapilanTutarInput.required = true;
                } else {
                    alert('Toplam tutar güncellenirken bir hata oluştu: ' + data.message);
                }
            });
        }
    });

    // Sayfa yüklendiğinde ve URL değiştiğinde aktif menüyü işaretle
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
        if (filename === 'file_detay.php' || filename === 'dosya_takip.php' || filename === 'yeni_dosya.php') {
            const dosyaCollapse = document.querySelector('#dosyaCollapse');
            const dosyaToggle = document.querySelector('[href="#dosyaCollapse"]');
            dosyaCollapse.classList.add('show');
            dosyaToggle.classList.add('active');
            dosyaToggle.setAttribute('aria-expanded', 'true');
        } else if (filename === 'musteri_takip.php' || filename === 'yeni_musteri.php') {
            const musteriCollapse = document.querySelector('#musteriCollapse');
            const musteriToggle = document.querySelector('[href="#musteriCollapse"]');
            musteriCollapse.classList.add('show');
            musteriToggle.classList.add('active');
            musteriToggle.setAttribute('aria-expanded', 'true');
        }

        // Alt menü öğelerini kontrol et
        document.querySelectorAll('.nav-link').forEach(link => {
            if (link.getAttribute('href') === filename) {
                link.classList.add('active');
                // Eğer bu bir alt menü öğesiyse, üst menüyü de aç
                const parentCollapse = link.closest('.collapse');
                if (parentCollapse) {
                    parentCollapse.classList.add('show');
                    const parentToggle = document.querySelector(`[href="#${parentCollapse.id}"]`);
                    parentToggle.classList.add('active');
                    parentToggle.setAttribute('aria-expanded', 'true');
                }
            }
        });
    }
    </script>
</body>
</html>