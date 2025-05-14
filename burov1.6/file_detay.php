<?php
session_start();
require_once "database.php";
require_once "Logger.php"; // Add Logger class inclusion

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
$logger = new Logger($db); // Initialize Logger instance

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

// Fetch events related to the current file
$eventQuery = "SELECT * FROM etkinlikler WHERE dosya_linki = :dosya_id";
$eventStmt = $db->prepare($eventQuery);
$eventStmt->execute([':dosya_id' => $dosya['dosya_id']]);
$events = $eventStmt->fetchAll(PDO::FETCH_ASSOC);

// Kullanıcı oturumundan personel_id'yi al
$currentUserId = $_SESSION['personel_id'];

// Tüm personel bilgilerini çek
$kidemliQuery = "SELECT personel_id, ad, soyad FROM personel ORDER BY ad, soyad";
$kidemliStmt = $db->prepare($kidemliQuery);
$kidemliStmt->execute();
$kidemliPersonelList = $kidemliStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch current personnel assignments
$sorumluQuery = "SELECT ad, soyad FROM personel WHERE personel_id = :personel_id";
$sorumluStmt = $db->prepare($sorumluQuery);
$sorumluStmt->execute([':personel_id' => $dosya['personel_id']]);
$sorumluPersonel = $sorumluStmt->fetch(PDO::FETCH_ASSOC);

$kidemliQuery = "SELECT ad, soyad FROM personel WHERE personel_id = :izin_id";
$kidemliStmt = $db->prepare($kidemliQuery);
$kidemliStmt->execute([':izin_id' => $dosya['izin_id']]);
$kidemliPersonel = $kidemliStmt->fetch(PDO::FETCH_ASSOC);

// Check if the current user is admin or matches izin_id
$isAuthorizedToEdit = ($currentUserId == 1 || $currentUserId == $dosya['izin_id']);

// Display and allow updates for assigned personnel
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
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
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
                    <button id="statusBadge" class="btn status-badge <?php echo $dosya['durum'] == 'aktif' ? 'status-active' : 'status-passive'; ?>" 
                           onclick="toggleStatus(<?php echo $dosya['dosya_id']; ?>)" type="button">
                           <?php echo $dosya['durum'] == 'aktif' ? 'AKTİF' : 'PASİF'; ?>
                    </button>
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
                                    <div class="row g-2" id="musteri-bilgileri-content">
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
                                    <!-- Edit Form - Initially Hidden -->
                                    <div id="musteri-edit-form" style="display: none;">
                                        <div class="row g-2">
                                            <div class="col-md-12">
                                                <div class="mb-2">
                                                    <label class="small text-muted">Müşteri Adı</label>
                                                    <select class="form-select" id="edit-musteri-id">
                                                        <option value="">Müşteri Seçiniz</option>
                                                    </select>
                                                    <div id="musteri-loading" class="text-center mt-2">
                                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                        Müşteriler yükleniyor...
                                                    </div>
                                                    <div id="musteri-error" class="text-danger mt-2" style="display:none;"></div>
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
                                                <button class="btn btn-primary btn-sm" id="saveMusteriBilgileriBtn" onclick="saveMusteriBilgileri(<?php echo $dosya['dosya_id']; ?>)" disabled>Kaydet</button>
                                                <button class="btn btn-secondary btn-sm" onclick="cancelMusteriEdit()">İptal</button>
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
                                        <button class="btn btn-success btn-sm ms-auto" onclick="window.location.href='dosya_takip.php?muhasebe=<?php echo $dosya['dosya_id']; ?>'">
                                            </>Muhasebe
                                        </button>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="card bg-primary text-white">
                                                <div class="card-body">
                                                    <h6>Toplam Tutar</h6>
                                                    <input type="number" class="form-control" id="toplamTutar" name="toplam_tutar" step="0.01" value="<?php echo $muhasebe['toplam_tutar'] ?? 0; ?>" <?php echo $isAuthorizedToEdit ? '' : 'readonly'; ?>>
                                                    <?php if ($isAuthorizedToEdit): ?>
                                                        <button class="btn btn-success mt-2" onclick="updateToplamTutar()">Güncelle</button>
                                                    <?php endif; ?>
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
                            <div class="col-md-12">
                                <div class="section-card">
                                    <div class="section-title">
                                        <i class="bi bi-info-circle-fill"></i>
                                        Dosya Bilgileri
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="small text-muted">Oluşturulma Tarihi</label>
                                                <div><?php echo date('d.m.Y H:i', strtotime($dosya['olusturma_tarihi'])); ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="small text-muted">Oluşturan Personel</label>
                                                <div><?php echo htmlspecialchars($dosya['personel_ad'] . ' ' . $dosya['personel_soyad']); ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="small text-muted">Son Güncelleme</label>
                                                <div><?php echo isset($dosya['guncelleme_tarihi']) ? date('d.m.Y H:i', strtotime($dosya['guncelleme_tarihi'])) : '-'; ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="small text-muted">Dosya Durumu</label>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <button type="button" 
                                                            class="btn btn-sm <?php echo ($dosya['dosya_durumu'] ?? 'Hazırlandı') == 'Hazırlandı' ? 'btn-primary' : 'btn-outline-primary'; ?>"
                                                            onclick="updateDosyaDurumu(<?php echo $dosya['dosya_id']; ?>, 'Hazırlandı')">
                                                        <i class="bi bi-file-earmark me-1"></i>Hazırlandı
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm <?php echo ($dosya['dosya_durumu'] ?? '') == 'Belediyede' ? 'btn-info' : 'btn-outline-info'; ?>"
                                                            onclick="updateDosyaDurumu(<?php echo $dosya['dosya_id']; ?>, 'Belediyede')">
                                                        <i class="bi bi-building me-1"></i>Belediyede
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm <?php echo ($dosya['dosya_durumu'] ?? '') == 'Kadastroda' ? 'btn-info' : 'btn-outline-info'; ?>"
                                                            onclick="updateDosyaDurumu(<?php echo $dosya['dosya_id']; ?>, 'Kadastroda')">
                                                        <i class="bi bi-file-earmark-text me-1"></i>Kadastroda
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm <?php echo ($dosya['dosya_durumu'] ?? '') == 'Tamamlandı' ? 'btn-success' : 'btn-outline-success'; ?>"
                                                            onclick="updateDosyaDurumu(<?php echo $dosya['dosya_id']; ?>, 'Tamamlandı')">
                                                        <i class="bi bi-check-circle me-1"></i>Tamamlandı
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm <?php echo ($dosya['dosya_durumu'] ?? '') == 'Beklemede' ? 'btn-warning' : 'btn-outline-warning'; ?>"
                                                            onclick="updateDosyaDurumu(<?php echo $dosya['dosya_id']; ?>, 'Beklemede')">
                                                        <i class="bi bi-hourglass-split me-1"></i>Beklemede
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="small text-muted">Dosya Türü</label>
                                                <div><?php echo htmlspecialchars($dosya['dosya_turu']); ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="small text-muted">Dosya No</label>
                                                <div><?php echo htmlspecialchars($dosya['dosya_id']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Personel Sections - Side by Side -->
                            <div class="col-md-6">
                                <div class="section-card">
                                    <div class="section-title">
                                        <i class="bi bi-person-badge-fill"></i>
                                        Kıdemli Personel
                                    </div>
                                    <div class="mb-3">
                                        <?php if ($currentUserId == 1): ?>
                                            <select class="form-select" id="kidemliPersonelSelect">
                                                <option value="">Personel Seçiniz</option>
                                                <?php foreach ($kidemliPersonelList as $personel): ?>
                                                    <option value="<?php echo $personel['personel_id']; ?>" <?php echo $personel['personel_id'] == $dosya['izin_id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($personel['ad'] . ' ' . $personel['soyad']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button class="btn btn-primary mt-2" onclick="assignKidemliPersonel()">Kaydet</button>
                                        <?php else: ?>
                                            <span>
                                                <?php 
                                                if ($kidemliPersonel && isset($kidemliPersonel['ad']) && isset($kidemliPersonel['soyad'])) {
                                                    echo htmlspecialchars($kidemliPersonel['ad'] . ' ' . $kidemliPersonel['soyad']);
                                                } else {
                                                    echo "Atanmamış";
                                                }
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Sorumlu personel seçeneği -->
                            <div class="col-md-6">
                                <div class="section-card">
                                    <div class="section-title">
                                        <i class="bi bi-person-badge-fill"></i>
                                        Sorumlu Personel
                                    </div>
                                    <div class="mb-3">
                                        <?php if ($currentUserId == 1): ?>
                                            <select class="form-select" id="sorumluPersonelSelect">
                                                <option value="">Personel Seçiniz</option>
                                                <?php foreach ($kidemliPersonelList as $personel): ?>
                                                    <option value="<?php echo $personel['personel_id']; ?>" <?php echo $personel['personel_id'] == $dosya['personel_id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($personel['ad'] . ' ' . $personel['soyad']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button class="btn btn-primary mt-2" onclick="assignSorumluPersonel()">Kaydet</button>
                                        <?php else: ?>
                                            <span>
                                                <?php 
                                                if ($sorumluPersonel && isset($sorumluPersonel['ad']) && isset($sorumluPersonel['soyad'])) {
                                                    echo htmlspecialchars($sorumluPersonel['ad'] . ' ' . $sorumluPersonel['soyad']);
                                                } else {
                                                    echo "Atanmamış";
                                                }
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Tasks, File Attachments -->
                    <div class="col-md-3">
                        <!-- Tasks -->
                        <div class="section-card">
                            <div class="section-title">
                                <i class="bi bi-list-task"></i>
                                Görevler
                            <button class="btn btn-secondary btn-sm" onclick="openEtkinlikModal(<?php echo $dosya['dosya_id']; ?>)">Görev Ekle</button>
                            </div>
                            <?php if (!empty($events)) { ?>
                                <ul class="list-group">
                                    <?php foreach ($events as $event) { ?>
                                        <li class="list-group-item">
                                            <strong><a href="#" onclick="openEventDetailModal(<?php echo $event['id']; ?>)"><?php echo htmlspecialchars($event['baslik']); ?></a></strong><br>
                                            <small><?php echo htmlspecialchars($event['aciklama']); ?></small><br>
                                            <span class="text-muted"><?php echo date('d.m.Y', strtotime($event['tarih'])); ?></span>
                                        </li>
                                    <?php } ?>
                                </ul>
                            <?php } else { ?>
                                <div class="text-center text-muted py-4">
                                    Görev Eklenmedi
                                </div>
                            <?php } ?>
                        </div>

                        <!-- File Attachments -->
                        <div class="section-card">
                            <div class="section-title d-flex flex-wrap align-items-center gap-2">
                                <i class="bi bi-paperclip"></i>
                                <span>Dosya Ekleri</span>
                                <div class="btn-group ms-auto flex-wrap">
                                    <button class="btn btn-secondary btn-sm" onclick="toggleFileView('active')">Aktif</button>
                                    <button class="btn btn-secondary btn-sm" onclick="toggleFileView('deleted')">Silinen</button>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#fileUploadModal">Yükle</button>
                                </div>
                            </div>
                            <div id="filesList">
                                <?php
                                // Aktif dosyaları getir
                                $files_query = $db->prepare("SELECT f.*, p.ad, p.soyad 
                                                            FROM files f 
                                                            LEFT JOIN personel p ON f.uploaded_by = p.personel_id 
                                                            WHERE f.dosya_id = ? AND f.is_deleted = 0
                                                            ORDER BY f.upload_date DESC");
                                $files_query->execute([$dosya['dosya_id']]);
                                $active_files = $files_query->fetchAll(PDO::FETCH_ASSOC);
                                
                                // Silinen dosyaları getir
                                $deleted_files_query = $db->prepare("SELECT f.*, p.ad, p.soyad 
                                                                   FROM files f 
                                                                   LEFT JOIN personel p ON f.uploaded_by = p.personel_id 
                                                                   WHERE f.dosya_id = ? AND f.is_deleted = 1
                                                                   ORDER BY f.upload_date DESC");
                                $deleted_files_query->execute([$dosya['dosya_id']]);
                                $deleted_files = $deleted_files_query->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                
                                <!-- Aktif Dosyalar Tablosu -->
                                <div id="activeFiles">
                                    <?php if (count($active_files) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Dosya Adı</th>
                                                        <th>Boyut</th>
                                                        <th>Yükleyen</th>
                                                        <th>Tarih</th>
                                                        <th>İşlemler</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($active_files as $file): ?>
                                                        <tr id="file-row-<?php echo $file['file_id']; ?>">
                                                            <td><?php echo htmlspecialchars($file['original_name']); ?></td>
                                                            <td><?php echo number_format($file['file_size'] / 1024, 2) . ' KB'; ?></td>
                                                            <td><?php echo htmlspecialchars($file['ad'] . ' ' . $file['soyad']); ?></td>
                                                            <td><?php echo date('d.m.Y H:i', strtotime($file['upload_date'])); ?></td>
                                                            <td>
                                                                <a href="<?php echo htmlspecialchars($file['file_path']); ?>" class="btn btn-sm btn-info" download>
                                                                    <i class="bi bi-download"></i> İndir
                                                                </a>
                                                                <?php if ($_SESSION['personel_id'] == 1): ?>
                                                                    <button class="btn btn-sm btn-danger" onclick="softDeleteFile(<?php echo $file['file_id']; ?>)">
                                                                        <i class="bi bi-trash"></i> Sil
                                                                    </button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            Henüz dosya yüklenmemiş.
                                            <div class="mt-2">
                                                <small>Dosyanıza ait ekler yükleyebilirsiniz.</small><br>
                                                <small>Yüklenen ekleri firma personeli görüntüleyebilir.</small>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Silinen Dosyalar Tablosu -->
                                <div id="deletedFiles" style="display: none;">
                                    <?php if (count($deleted_files) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Dosya Adı</th>
                                                        <th>Boyut</th>
                                                        <th>Yükleyen</th>
                                                        <th>Silinme Tarihi</th>
                                                        <th>İşlemler</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($deleted_files as $file): ?>
                                                        <tr id="deleted-file-row-<?php echo $file['file_id']; ?>">
                                                            <td><?php echo htmlspecialchars($file['original_name']); ?></td>
                                                            <td><?php echo number_format($file['file_size'] / 1024, 2) . ' KB'; ?></td>
                                                            <td><?php echo htmlspecialchars($file['ad'] . ' ' . $file['soyad']); ?></td>
                                                            <td><?php echo date('d.m.Y H:i', strtotime($file['upload_date'])); ?></td>
                                                            <td>
                                                                <?php if ($_SESSION['personel_id'] == 1): ?>
                                                                    <button class="btn btn-sm btn-success" onclick="restoreFile(<?php echo $file['file_id']; ?>)">
                                                                        <i class="bi bi-arrow-counterclockwise"></i> Geri Al
                                                                    </button>
                                                                    <button class="btn btn-sm btn-danger" onclick="permanentDeleteFile(<?php echo $file['file_id']; ?>)">
                                                                        <i class="bi bi-trash"></i> Kalıcı Sil
                                                                    </button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            Silinmiş dosya bulunmuyor.
                                        </div>
                                    <?php endif; ?>
                                </div>
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

    <!-- Modal Structure -->
    <div class="modal fade" id="etkinlikModal" tabindex="-1" aria-labelledby="etkinlikModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="etkinlikModalLabel">Etkinlik Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe id="etkinlikIframe" src="" style="width: 100%; height: 500px; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- File Upload Modal -->
    <div class="modal fade" id="fileUploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Dosya Yükle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <input type="hidden" name="dosya_id" value="<?php echo $dosya['dosya_id']; ?>">
                        <div class="mb-3">
                            <label for="fileInput" class="form-label">Dosya Seçin</label>
                            <input type="file" class="form-control" name="file" id="fileInput" required>
                        </div>
                        <div class="progress mb-3 d-none">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Yükle</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="successMessage" class="alert alert-success" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 1000;">
        Logo başarıyla yüklendi.
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Müşteri düzenleme formunu başlangıçta gizle
        const editForm = document.getElementById('musteri-edit-form');
        if (editForm) {
            editForm.style.display = 'none';
        }
        
        // Initialize variables with null checks
        const uploadForm = document.getElementById('uploadForm');
        const fileInput = document.getElementById('fileInput');
        const progressBar = document.querySelector('.progress');
        const progressBarInner = progressBar?.querySelector('.progress-bar');
        
        // Add event listener for file input change
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file size (örneğin max 50MB)
                    const maxSize = 50 * 1024 * 1024; // 50MB in bytes
                    if (file.size > maxSize) {
                        alert('Dosya boyutu çok büyük. Maksimum 50MB yükleyebilirsiniz.');
                        fileInput.value = '';
                        return;
                    }
                }
            });
        }
        
        // Add event listener for file upload form
        if (uploadForm) {
            uploadForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Disable submit button and show loading state
                const submitButton = this.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Yükleniyor...';
                }
                
                // Show progress bar
                if (progressBar) {
                    progressBar.classList.remove('d-none');
                    if (progressBarInner) {
                        progressBarInner.style.width = '0%';
                        progressBarInner.textContent = '0%';
                    }
                }

                try {
                    const formData = new FormData(this);
                    
                    // Simulate progress (since we can't get real progress from PHP)
                    let progress = 0;
                    const progressInterval = setInterval(() => {
                        progress += 10;
                        if (progress <= 90) {
                            if (progressBarInner) {
                                progressBarInner.style.width = progress + '%';
                                progressBarInner.textContent = progress + '%';
                            }
                        }
                    }, 500);

                    const response = await fetch('upload_file.php', {
                        method: 'POST',
                        body: formData
                    });

                    clearInterval(progressInterval);
                    
                    // Show 100% progress
                    if (progressBarInner) {
                        progressBarInner.style.width = '100%';
                        progressBarInner.textContent = '100%';
                    }

                    // Check for valid response before parsing JSON
                    const responseText = await response.text();
                    
                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch (parseError) {
                        console.error('JSON parse error:', parseError);
                        console.error('Raw response:', responseText);
                        throw new Error('Sunucudan geçersiz yanıt alındı. Detaylar için konsolu kontrol edin.');
                    }
                    
                    if (data.success) {
                        // Hide modal and reset form
                        const modal = bootstrap.Modal.getInstance(document.getElementById('fileUploadModal'));
                        if (modal) {
                            modal.hide();
                        }
                        this.reset();
                        
                        // Show success message
                        alert('Dosya başarıyla yüklendi.');
                        
                        // Refresh the files list by reloading the page
                        location.reload();
                    } else {
                        throw new Error(data.message || 'Dosya yükleme başarısız oldu.');
                    }
                } catch (error) {
                    console.error('Yükleme hatası:', error);
                    alert('Hata: ' + (error.message || 'Dosya yükleme sırasında bir hata oluştu.'));
                } finally {
                    // Re-enable submit button
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = 'Yükle';
                    }
                    
                    // Hide progress bar after a short delay
                    setTimeout(() => {
                        if (progressBar) {
                            progressBar.classList.add('d-none');
                        }
                    }, 1000);
                }
            });
        }

        function showMuhasebeDetay(dosyaId, showModal = false) {
            currentDosyaId = dosyaId;
            document.getElementById('islemDosyaId').value = dosyaId;
            
            fetch(`muhasebe_detay.php?dosya_id=${dosyaId}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Muhasebe detay yanıtı:', data);

                    if (data.success) {
                        // Check if user is authorized to edit total amount
                        const isAuthorized = <?php echo $_SESSION['personel_id']; ?> == 1 || <?php echo $_SESSION['personel_id']; ?> == data.debug.izin_id;
                        
                        // Enable/disable the total amount change button based on authorization
                        const toplamTutarDegistirBtn = document.getElementById('toplamTutarDegistir');
                        if (toplamTutarDegistirBtn) {
                            toplamTutarDegistirBtn.disabled = !isAuthorized;
                            if (!isAuthorized) {
                                toplamTutarDegistirBtn.title = "Bu işlem için yetkiniz bulunmamaktadır";
                            } else {
                                toplamTutarDegistirBtn.title = "Toplam tutarı değiştirmek için tıklayın";
                            }
                        }

                        // Update summary information
                        const formatNumber = (num) => parseFloat(num).toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                        
                        // Update summary cards on the main page with null checks
                        const toplamTutarOzet = document.getElementById('toplamTutarOzet');
                        const yapilanOdemeOzet = document.getElementById('yapilanOdemeOzet');
                        const kalanTutarOzet = document.getElementById('kalanTutarOzet');
                        
                        // Update modal summary cards with null checks
                        const modalToplamTutar = document.getElementById('modalToplamTutar');
                        const modalYapilanOdeme = document.getElementById('modalYapilanOdeme');
                        const modalKalanTutar = document.getElementById('modalKalanTutar');
                        
                        // Update main page cards if they exist
                        if (toplamTutarOzet) toplamTutarOzet.textContent = formatNumber(data.ozet.toplam_tutar);
                        if (yapilanOdemeOzet) yapilanOdemeOzet.textContent = formatNumber(data.ozet.toplam_yapilan);
                        if (kalanTutarOzet) kalanTutarOzet.textContent = formatNumber(data.ozet.toplam_kalan);
                        
                        // Update modal cards if they exist
                        if (modalToplamTutar) modalToplamTutar.textContent = formatNumber(data.ozet.toplam_tutar);
                        if (modalYapilanOdeme) modalYapilanOdeme.textContent = formatNumber(data.ozet.toplam_yapilan);
                        if (modalKalanTutar) modalKalanTutar.textContent = formatNumber(data.ozet.toplam_kalan);

                        // Update transactions table
                        const muhasebeIslemlerListesi = document.getElementById('muhasebeIslemlerListesi');
                        if (muhasebeIslemlerListesi) {
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
                            muhasebeIslemlerListesi.innerHTML = islemlerHTML;
                        }

                        // Show modal if requested
                        if (showModal) {
                            const muhasebeModal = document.getElementById('muhasebeModal');
                            if (muhasebeModal) {
                                const modal = new bootstrap.Modal(muhasebeModal);
                                modal.show();
                            }
                        }
                    } else {
                        console.error('Hata:', data.message);
                        alert('Hata: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('AJAX Hatası:', error);
                    alert('Bir hata oluştu: ' + error.message);
                });
        }

        // İşlem ekleme modalını göster
        function showIslemEkle() {
            const islemEkleForm = document.getElementById('islemEkleForm');
            const islemDosyaId = document.getElementById('islemDosyaId');
            
            if (islemEkleForm) islemEkleForm.reset();
            if (islemDosyaId) islemDosyaId.value = currentDosyaId;

            // İlk işlem kontrolü yap
            fetch(`muhasebe_detay.php?dosya_id=${currentDosyaId}`)
                .then(response => response.json())
                .then(data => {
                    const toplamTutarDiv = document.getElementById('toplamTutarDiv');
                    const toplamTutarInput = document.getElementById('toplamTutar');
                    const kalanTutarInput = document.getElementById('kalanTutar');
                    const yapilanTutarInput = document.getElementById('yapilanTutar');
                    const toplamTutarDegistirBtn = document.getElementById('toplamTutarDegistir');
                    
                    if (!toplamTutarDiv || !toplamTutarInput || !kalanTutarInput || !yapilanTutarInput) {
                        console.error('Form elemanları bulunamadı');
                        return;
                    }
                    
                    if (!data.ilk_islem_var) {
                        // İlk işlem ise toplam tutar alanını düzenlenebilir yap
                        toplamTutarDiv.style.display = 'block';
                        toplamTutarInput.readOnly = false;
                        toplamTutarInput.required = true;
                        kalanTutarInput.value = '';
                        if (toplamTutarDegistirBtn) toplamTutarDegistirBtn.style.display = 'none';
                    } else {
                        // İlk işlem değilse toplam tutarı göster ama readonly yap
                        toplamTutarDiv.style.display = 'block';
                        toplamTutarInput.readOnly = true;
                        toplamTutarInput.required = false;
                        toplamTutarInput.value = data.ozet.toplam_tutar;
                        currentKalanTutar = data.ozet.toplam_kalan;
                        kalanTutarInput.value = currentKalanTutar.toFixed(2);
                        if (toplamTutarDegistirBtn) toplamTutarDegistirBtn.style.display = 'block';
                    }

                    // Yapılan ödeme değiştiğinde kalan tutarı güncelle
                    yapilanTutarInput.addEventListener('input', function() {
                        const toplamTutarEl = document.getElementById('toplamTutar');
                        const kalanTutarEl = document.getElementById('kalanTutar');
                        const yapilanOdemeOzet = document.getElementById('yapilanOdemeOzet');
                        const kalanTutarOzet = document.getElementById('kalanTutarOzet');
                        
                        if (!toplamTutarEl || !kalanTutarEl) return;
                        
                        const toplamTutar = parseFloat(toplamTutarEl.value) || 0;
                        const yapilanTutar = parseFloat(this.value) || 0;
                        const kalanTutar = parseFloat(kalanTutarEl.value) || toplamTutar;
                        const yeniKalanTutar = kalanTutar - yapilanTutar;
                        kalanTutarEl.value = yeniKalanTutar.toFixed(2);
                        
                        // Özet bilgilerini de güncelle
                        if (yapilanOdemeOzet && kalanTutarOzet) {
                            const mevcutYapilanOdeme = parseFloat(yapilanOdemeOzet.textContent.replace(/[^\d.-]/g, '')) || 0;
                            const yeniToplamYapilanOdeme = mevcutYapilanOdeme + yapilanTutar;
                            
                            yapilanOdemeOzet.textContent = 
                                yeniToplamYapilanOdeme.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                            kalanTutarOzet.textContent = 
                                yeniKalanTutar.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                        }
                    });

                    // Kalan tutar alanını sadece okunabilir yap
                    kalanTutarInput.readOnly = true;
                });

            const muhasebeModal = document.getElementById('muhasebeModal');
            const islemEkleModal = document.getElementById('islemEkleModal');
            
            if (muhasebeModal) {
                const bsModal = bootstrap.Modal.getInstance(muhasebeModal);
                if (bsModal) bsModal.hide();
            }
            
            if (islemEkleModal) {
                const modal = new bootstrap.Modal(islemEkleModal);
                modal.show();
            }
        }

        // Update the total amount change handler
        const toplamTutarDegistirBtn = document.getElementById('toplamTutarDegistir');
        if (toplamTutarDegistirBtn) {
            toplamTutarDegistirBtn.addEventListener('click', function() {
                const toplamTutarInput = document.getElementById('toplamTutar');
                const yapilanTutarInput = document.getElementById('yapilanTutar');
                const kalanTutarInput = document.getElementById('kalanTutar');
                
                if (!toplamTutarInput || !yapilanTutarInput || !kalanTutarInput) {
                    console.error('Form elemanları bulunamadı');
                    return;
                }
                
                const kaydetBtn = document.querySelector('#islemEkleForm button[type="submit"]');
                isToplamTutarEditable = !isToplamTutarEditable;
                
                if (isToplamTutarEditable) {
                    // Enable edit mode
                    toplamTutarInput.readOnly = false;
                    yapilanTutarInput.required = false;
                    yapilanTutarInput.value = '';
                    kalanTutarInput.value = '';
                    if (kaydetBtn) kaydetBtn.disabled = true;
                    this.classList.remove('btn-warning');
                    this.classList.add('btn-success');
                    this.textContent = 'Değişikliği Onayla';
                } else {
                    // Confirm and save changes
                    const yeniToplamTutar = parseFloat(toplamTutarInput.value) || 0;
                    
                    // Save total amount change using the new endpoint
                    const formData = new FormData();
                    formData.append('dosya_id', currentDosyaId);
                    formData.append('toplam_tutar', yeniToplamTutar);
                    
                    fetch('update_toplam_tutar.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            // Refresh the muhasebe details
                            showMuhasebeDetay(currentDosyaId);
                            yapilanTutarInput.required = true;
                            if (kaydetBtn) kaydetBtn.disabled = false;
                            alert('Toplam tutar başarıyla güncellendi');
                            
                            // Reset button state
                            toplamTutarInput.readOnly = true;
                            this.classList.remove('btn-success');
                            this.classList.add('btn-warning');
                            this.textContent = 'Toplam Tutarı Değiştir';
                            
                            // Close the modal
                            const islemEkleModal = document.getElementById('islemEkleModal');
                            if (islemEkleModal) {
                                const modal = bootstrap.Modal.getInstance(islemEkleModal);
                                if (modal) modal.hide();
                            }
                        } else {
                            alert('Toplam tutar güncellenirken bir hata oluştu: ' + data.message);
                            // Revert the button state
                            this.classList.remove('btn-success');
                            this.classList.add('btn-warning');
                            this.textContent = 'Toplam Tutarı Değiştir';
                            toplamTutarInput.readOnly = true;
                            if (kaydetBtn) kaydetBtn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('AJAX Hatası:', error);
                        alert('Bir hata oluştu: ' + error.message);
                        // Revert the button state
                        this.classList.remove('btn-success');
                        this.classList.add('btn-warning');
                        this.textContent = 'Toplam Tutarı Değiştir';
                        toplamTutarInput.readOnly = true;
                        if (kaydetBtn) kaydetBtn.disabled = false;
                    });
                }
            });
        }

        // İşlem ekleme formunu gönder
        const islemEkleForm = document.getElementById('islemEkleForm');
        if (islemEkleForm) {
            islemEkleForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('dosya_id', currentDosyaId);
                
                fetch('islem_ekle.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('İşlem başarıyla eklendi');
                        
                        const islemEkleModal = document.getElementById('islemEkleModal');
                        if (islemEkleModal) {
                            const modal = bootstrap.Modal.getInstance(islemEkleModal);
                            if (modal) modal.hide();
                        }
                        
                        // Özet bilgilerini güncelle
                        if (data.ozet) {
                            const toplamTutarOzet = document.getElementById('toplamTutarOzet');
                            const yapilanOdemeOzet = document.getElementById('yapilanOdemeOzet');
                            const kalanTutarOzet = document.getElementById('kalanTutarOzet');
                            
                            if (toplamTutarOzet) toplamTutarOzet.textContent = 
                                parseFloat(data.ozet.toplam_tutar || 0).toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                            if (yapilanOdemeOzet) yapilanOdemeOzet.textContent = 
                                parseFloat(data.ozet.toplam_yapilan || 0).toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                            if (kalanTutarOzet) kalanTutarOzet.textContent = 
                                parseFloat(data.ozet.toplam_kalan || 0).toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                        }
                        
                        // İşlem listesini güncelle
                        const muhasebeIslemlerListesi = document.getElementById('muhasebeIslemlerListesi');
                        if (muhasebeIslemlerListesi) {
                            let islemlerHTML = '';
                            if (data.islemler && data.islemler.length > 0) {
                                data.islemler.forEach(islem => {
                                    const tarih = new Date(islem.tarih);
                                    const formatliTarih = tarih.toLocaleString('tr-TR');
                                    
                                    islemlerHTML += `
                                        <tr>
                                            <td>${islem.muhasebe_id}</td>
                                            <td>${formatliTarih}</td>
                                            <td>${parseFloat(islem.toplam_tutar || 0).toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺</td>
                                            <td>${parseFloat(islem.yapilan_odeme || 0).toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺</td>
                                            <td>${parseFloat(islem.kalan_tutar || 0).toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺</td>
                                            <td>${islem.aciklama || ''}</td>
                                        </tr>
                                    `;
                                });
                            }
                            muhasebeIslemlerListesi.innerHTML = islemlerHTML;
                        }
                    } else {
                        alert('Bir hata oluştu: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('AJAX Hatası:', error);
                    alert('Bir hata oluştu: ' + error.message);
                });
            });
        }

        // Yapılan ödeme değiştiğinde kalan tutarı güncelle
        const yapilanTutarInput = document.getElementById('yapilanTutar');
        if (yapilanTutarInput) {
            yapilanTutarInput.addEventListener('input', function() {
                const toplamTutarEl = document.getElementById('toplamTutar');
                const kalanTutarEl = document.getElementById('kalanTutar');
                const yapilanOdemeOzet = document.getElementById('yapilanOdemeOzet');
                const kalanTutarOzet = document.getElementById('kalanTutarOzet');
                
                if (!toplamTutarEl || !kalanTutarEl) return;
                
                const toplamTutar = parseFloat(toplamTutarEl.value) || 0;
                const yapilanTutar = parseFloat(this.value) || 0;
                const kalanTutar = parseFloat(kalanTutarEl.value) || toplamTutar;
                const yeniKalanTutar = kalanTutar - yapilanTutar;
                kalanTutarEl.value = yeniKalanTutar.toFixed(2);
                
                // Özet bilgilerini de güncelle
                if (yapilanOdemeOzet && kalanTutarOzet) {
                    const mevcutYapilanOdeme = parseFloat(yapilanOdemeOzet.textContent.replace(/[^\d.-]/g, '')) || 0;
                    const yeniToplamYapilanOdeme = mevcutYapilanOdeme + yapilanTutar;
                    
                    yapilanOdemeOzet.textContent = 
                        yeniToplamYapilanOdeme.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                    kalanTutarOzet.textContent = 
                        yeniKalanTutar.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                }
            });
        }

        // Toplam tutar değiştiğinde kalan tutarı güncelle
        const toplamTutarInput = document.getElementById('toplamTutar');
        if (toplamTutarInput) {
            toplamTutarInput.addEventListener('input', function() {
                const kalanTutarEl = document.getElementById('kalanTutar');
                const yapilanTutarEl = document.getElementById('yapilanTutar');
                const toplamTutarOzet = document.getElementById('toplamTutarOzet');
                const kalanTutarOzet = document.getElementById('kalanTutarOzet');
                
                if (!kalanTutarEl || !yapilanTutarEl) return;
                
                const toplamTutar = parseFloat(this.value) || 0;
                const yapilanTutar = parseFloat(yapilanTutarEl.value) || 0;
                const kalanTutar = toplamTutar - yapilanTutar;
                kalanTutarEl.value = kalanTutar.toFixed(2);
                
                // Özet bilgilerini de güncelle
                if (toplamTutarOzet && kalanTutarOzet) {
                    toplamTutarOzet.textContent = 
                        toplamTutar.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                    kalanTutarOzet.textContent = 
                        kalanTutar.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                }
            });
        }

        // Call showMuhasebeDetay only if we have a dosya_id
        const dosyaId = <?php echo isset($dosya['dosya_id']) ? $dosya['dosya_id'] : 'null'; ?>;
        if (dosyaId !== null) {
            showMuhasebeDetay(dosyaId, false);
        }

        // Mark active menu
        markActiveMenu();
    });

    function deleteFile(fileId) {
        if (!confirm('Bu dosyayı silmek istediğinizden emin misiniz?')) {
            return;
        }
        
        fetch('delete_file.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'file_id=' + fileId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const fileRow = document.getElementById('file-row-' + fileId);
                if (fileRow) {
                    fileRow.remove();
                }
                
                const filesList = document.getElementById('filesList');
                const tbody = filesList?.querySelector('tbody');
                if (!tbody || tbody.children.length === 0) {
                    if (filesList) {
                        filesList.innerHTML = `
                            <div class="alert alert-info">
                                Henüz dosya yüklenmemiş.
                                <div class="mt-2">
                                    <small>Dosyanıza ait ekler yükleyebilirsiniz.</small><br>
                                    <small>Yüklenen ekleri firma personeli görüntüleyebilir.</small>
                                </div>
                            </div>
                        `;
                    }
                }
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Silme hatası:', error);
            alert('Dosya silme sırasında bir hata oluştu.');
        });
    }

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
        // Görünüm ve düzenleme formlarını göster/gizle
        const viewContent = document.getElementById('musteri-bilgileri-content');
        const editForm = document.getElementById('musteri-edit-form');
        
        if (viewContent && editForm) {
            viewContent.style.display = 'none';
            editForm.style.display = 'block';
            
            // Müşterileri AJAX ile yükle
            fetch('get_musteriler.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Müşteriler yüklenirken bir hata oluştu. Sunucu yanıtı: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    // Yükleme göstergesini gizle
                    document.getElementById('musteri-loading').style.display = 'none';
                    
                    const select = document.getElementById('edit-musteri-id');
                    
                    if (!data || !Array.isArray(data) || data.length === 0) {
                        document.getElementById('musteri-error').textContent = 'Hiç müşteri bulunamadı.';
                        document.getElementById('musteri-error').style.display = 'block';
                        return;
                    }
                    
                    // Önceki seçenekleri temizle
                    while (select.options.length > 1) {
                        select.remove(1);
                    }
                    
                    data.forEach(musteri => {
                        const option = document.createElement('option');
                        option.value = musteri.musteri_id;
                        option.text = musteri.musteri_adi + (musteri.musteri_turu ? ' (' + musteri.musteri_turu + ')' : '');
                        option.setAttribute('data-musteri-adi', musteri.musteri_adi);
                        if (musteri.musteri_id == <?php echo $dosya['musteri_id'] ?: 0; ?>) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });
                    
                    // Kaydet butonunu etkinleştir
                    document.getElementById('saveMusteriBilgileriBtn').disabled = false;
                    
                    // select değişikliğini dinle
                    select.addEventListener('change', function() {
                        const saveButton = document.getElementById('saveMusteriBilgileriBtn');
                        saveButton.disabled = !this.value;
                    });
                })
                .catch(error => {
                    // Yükleme göstergesini gizle ve hata mesajını göster
                    document.getElementById('musteri-loading').style.display = 'none';
                    document.getElementById('musteri-error').textContent = error.message;
                    document.getElementById('musteri-error').style.display = 'block';
                    console.error('AJAX Hatası:', error);
                });
        } else {
            console.error('Müşteri bilgileri görünüm veya düzenleme formu bulunamadı.');
        }
    }

    function cancelMusteriEdit() {
        // Görünüm ve düzenleme formlarını göster/gizle
        const viewContent = document.getElementById('musteri-bilgileri-content');
        const editForm = document.getElementById('musteri-edit-form');
        
        if (viewContent && editForm) {
            viewContent.style.display = 'block';
            editForm.style.display = 'none';
        } else {
            console.error('Müşteri bilgileri görünüm veya düzenleme formu bulunamadı.');
            location.reload(); // Sorun varsa sayfayı yenile
        }
    }
    
    function saveMusteriBilgileri(dosyaId) {
        const musteriId = document.getElementById('edit-musteri-id').value;
        if (!musteriId) {
            alert('Lütfen müşteri seçiniz.');
            return;
        }
        
        const saveButton = document.getElementById('saveMusteriBilgileriBtn');
        saveButton.disabled = true;
        saveButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Kaydediliyor...';
        
        const updateTapuMaliki = document.getElementById('update-tapu-maliki').checked;
        const select = document.getElementById('edit-musteri-id');
        const selectedIndex = select.selectedIndex;
        
        if (selectedIndex === -1) {
            alert('Müşteri seçimi geçerli değil.');
            saveButton.disabled = false;
            saveButton.textContent = 'Kaydet';
            return;
        }
        
        const musteriAdi = select.options[selectedIndex].getAttribute('data-musteri-adi');
        
        const formData = new FormData();
        formData.append('dosya_id', dosyaId);
        formData.append('musteri_id', musteriId);
        formData.append('log_action', true); // Add log flag
        
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
        .then(response => {
            if (!response.ok) {
                throw new Error('Sunucu yanıtı: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı',
                    text: 'Müşteri bilgileri başarıyla güncellendi',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    // Sayfayı yenileyerek güncel verileri göster
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata',
                    text: data.message || 'Müşteri bilgileri güncellenirken bir hata oluştu'
                });
                saveButton.disabled = false;
                saveButton.textContent = 'Kaydet';
            }
        })
        .catch(error => {
            console.error('AJAX Hatası:', error);
            Swal.fire({
                icon: 'error',
                title: 'İşlem Hatası',
                text: 'Bir hata oluştu: ' + error.message
            });
            saveButton.disabled = false;
            saveButton.textContent = 'Kaydet';
        });
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

    function toggleStatus(dosyaId) {
        // Mevcut durumu al
        const statusBadge = document.getElementById('statusBadge');
        const isAktif = statusBadge.textContent.trim() === 'AKTİF';
        const newStatus = isAktif ? 'pasif' : 'aktif';
        
        // AJAX isteği ile durum güncelleme
        fetch('update_dosya_durum.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `dosya_id=${dosyaId}&durum=${newStatus}&log_action=true` // Add log flag
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Başarılı güncelleme, görsel durum değiştir
                if (newStatus === 'aktif') {
                    statusBadge.textContent = 'AKTİF';
                    statusBadge.classList.remove('status-passive');
                    statusBadge.classList.add('status-active');
                } else {
                    statusBadge.textContent = 'PASİF';
                    statusBadge.classList.remove('status-active');
                    statusBadge.classList.add('status-passive');
                }
                
                // Başarı mesajı göster
                Swal.fire({
                    icon: 'success',
                    title: 'Durum güncellendi',
                    text: 'Dosya durumu ' + (newStatus === 'aktif' ? 'aktif' : 'pasif') + ' olarak değiştirildi.',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                // Hata mesajı göster
                Swal.fire({
                    icon: 'error',
                    title: 'Hata',
                    text: data.message || 'Durum güncellenirken bir hata oluştu.'
                });
            }
        })
        .catch(error => {
            console.error('AJAX Hatası:', error);
            Swal.fire({
                icon: 'error',
                title: 'Hata',
                text: 'Sunucu ile iletişim kurulurken bir hata oluştu.'
            });
        });
    }

    let currentDosyaId = <?php echo $dosya['dosya_id']; ?>;
    let currentKalanTutar = <?php echo $muhasebe['kalan_tutar'] ?? 0; ?>;
    let isToplamTutarEditable = false;

    function updateToplamTutar() {
        const toplamTutar = document.getElementById('toplamTutar').value;
        const formData = new FormData();
        formData.append('dosya_id', <?php echo $dosya['dosya_id']; ?>);
        formData.append('toplam_tutar', toplamTutar);
        formData.append('log_action', true); // Add log flag

        fetch('update_toplam_tutar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Toplam tutar başarıyla güncellendi');
                location.reload();
            } else {
                alert('Bir hata oluştu: ' + data.message);
            }
        })
        .catch(error => {
            console.error('AJAX Hatası:', error);
            alert('Bir hata oluştu: ' + error.message);
        });
    }

    function toggleFileView(type) {
        const activeFiles = document.getElementById('activeFiles');
        const deletedFiles = document.getElementById('deletedFiles');
        
        if (type === 'active') {
            activeFiles.style.display = 'block';
            deletedFiles.style.display = 'none';
        } else {
            activeFiles.style.display = 'none';
            deletedFiles.style.display = 'block';
        }
    }

    function softDeleteFile(fileId) {
        if (!confirm('Bu dosyayı silmek istediğinizden emin misiniz?')) {
            return;
        }
        
        fetch('delete_file.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'file_id=' + fileId + '&log_action=true' // Add log flag
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Sayfayı yenile
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Silme hatası:', error);
            alert('Dosya silme sırasında bir hata oluştu.');
        });
    }

    function restoreFile(fileId) {
        if (!confirm('Bu dosyayı geri almak istediğinizden emin misiniz?')) {
            return;
        }
        
        fetch('restore_file.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'file_id=' + fileId + '&log_action=true' // Add log flag
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Sayfayı yenile
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Geri alma hatası:', error);
            alert('Dosya geri alma sırasında bir hata oluştu.');
        });
    }

    function permanentDeleteFile(fileId) {
        if (!confirm('Bu dosyayı kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')) {
            return;
        }
        
        fetch('permanent_delete_file.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'file_id=' + fileId + '&log_action=true' // Add log flag
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Sayfayı yenile
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Kalıcı silme hatası:', error);
            alert('Dosya kalıcı silme sırasında bir hata oluştu.');
        });
    }

    function updateDosyaDurumu(dosyaId, newStatus) {
        // Update the status in the database
        fetch('update_dosya_durumu.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `dosya_id=${dosyaId}&dosya_durumu=${newStatus}&log_action=true`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update all buttons to outline style
                const buttons = document.querySelectorAll('.btn-sm');
                buttons.forEach(btn => {
                    if (btn.classList.contains('btn-primary')) {
                        btn.classList.replace('btn-primary', 'btn-outline-primary');
                    } else if (btn.classList.contains('btn-info')) {
                        btn.classList.replace('btn-info', 'btn-outline-info');
                    } else if (btn.classList.contains('btn-success')) {
                        btn.classList.replace('btn-success', 'btn-outline-success');
                    } else if (btn.classList.contains('btn-warning')) {
                        btn.classList.replace('btn-warning', 'btn-outline-warning');
                    }
                });

                // Update the clicked button to filled style
                const clickedButton = event.target.closest('button');
                if (clickedButton) {
                    if (newStatus === 'Hazırlandı') {
                        clickedButton.classList.replace('btn-outline-primary', 'btn-primary');
                    } else if (newStatus === 'Belediyede' || newStatus === 'Kadastroda') {
                        clickedButton.classList.replace('btn-outline-info', 'btn-info');
                    } else if (newStatus === 'Tamamlandı') {
                        clickedButton.classList.replace('btn-outline-success', 'btn-success');
                    } else if (newStatus === 'Beklemede') {
                        clickedButton.classList.replace('btn-outline-warning', 'btn-warning');
                    }
                }
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı',
                    text: 'Dosya durumu güncellendi',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    // Reload the page to ensure all states are correct
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata',
                    text: data.message || 'Dosya durumu güncellenirken bir hata oluştu'
                });
            }
        })
        .catch(error => {
            console.error('AJAX Hatası:', error);
            location.reload();
        });
    }

    function assignKidemliPersonel() {
        const selectedPersonelId = document.getElementById('kidemliPersonelSelect').value;
        if (!selectedPersonelId) {
            alert('Lütfen bir personel seçin.');
            return;
        }
        
        const formData = new FormData();
        formData.append('dosya_id', <?php echo $dosya['dosya_id']; ?>);
        formData.append('personel_id', selectedPersonelId);
        formData.append('log_action', true); // Add log flag
        
        fetch('update_izin_id.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Kıdemli personel başarıyla atandı');
                location.reload();
            } else {
                alert('Bir hata oluştu: ' + data.message);
            }
        })
        .catch(error => {
            console.error('AJAX Hatası:', error);
            alert('Bir hata oluştu: ' + error.message);
        });
    }

    function assignSorumluPersonel() {
        const selectedPersonelId = document.getElementById('sorumluPersonelSelect').value;
        if (!selectedPersonelId) {
            alert('Lütfen bir personel seçin.');
            return;
        }
        
        const formData = new FormData();
        formData.append('dosya_id', <?php echo $dosya['dosya_id']; ?>);
        formData.append('personel_id', selectedPersonelId);
        formData.append('log_action', true); // Add log flag
        
        fetch('update_sorumlu_id.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Sorumlu personel başarıyla atandı');
                location.reload();
            } else {
                alert('Bir hata oluştu: ' + data.message);
            }
        })
        .catch(error => {
            console.error('AJAX Hatası:', error);
            alert('Bir hata oluştu: ' + error.message);
        });
    }

    function openEtkinlikModal(dosyaId) {
        // Set the iframe source to load etkinlik_ekle.php with the dosya_id and source
        document.getElementById('etkinlikIframe').src = `etkinlik_ekle.php?dosya_id=${dosyaId}&source=file_detay`;
        // Show the modal
        new bootstrap.Modal(document.getElementById('etkinlikModal')).show();
    }

    function openEventDetailModal(eventId) {
        const modal = new bootstrap.Modal(document.getElementById('etkinlikModal'));
        const iframe = document.getElementById('etkinlikIframe');
        iframe.src = `etkinlik_detay.php?etkinlik_id=${eventId}`;
        modal.show();
    }

    function showSuccessMessage() {
        const messageDiv = document.getElementById('successMessage');
        messageDiv.style.display = 'block';
        messageDiv.classList.add('fade-in');

        // Hide the message after 3 seconds
        setTimeout(() => {
            messageDiv.classList.remove('fade-in');
            messageDiv.style.display = 'none';
        }, 3000);
    }

    // Call this function after a successful upload
    <?php if (isset($_SESSION['success_message'])): ?>
        showSuccessMessage();
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    </script>
</body>
</html>