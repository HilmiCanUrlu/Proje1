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

// Debug için müşteri sorgusunu ve sonuçları yazdır
try {
    $musteri_query = "SELECT musteri_id, musteri_adi, musteri_turu FROM musteriler ORDER BY musteri_adi ASC";
    $musteri_stmt = $db->prepare($musteri_query);
    $musteri_stmt->execute();
    $musteriler = $musteri_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug için müşteri sayısını ve verileri yazdır
    error_log("Müşteri Sayısı: " . count($musteriler));
    error_log("Müşteri Verileri: " . print_r($musteriler, true));
    
    if (empty($musteriler)) {
        error_log("Dikkat: Müşteri tablosu boş!");
    }
} catch(PDOException $e) {
    error_log("Müşteri sorgusu hatası: " . $e->getMessage());
}

// Form gönderildiğinde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $query = "INSERT INTO dosyalar (
            personel_id, 
            musteri_id, 
            islem_turu, 
            dosya_turu, 
            il, 
            ilce, 
            mahalle, 
            ada, 
            parsel, 
            dosya_durumu
        ) VALUES (
            :personel_id,
            :musteri_id,
            :islem_turu,
            :dosya_turu,
            :il,
            :ilce,
            :mahalle,
            :ada,
            :parsel,
            'Hazırlandı'
        )";

        $stmt = $db->prepare($query);
        
        // Form verilerini al
        $musteri_id = !empty($_POST['musteri_id']) ? $_POST['musteri_id'] : null;
        
        if (!$musteri_id) {
            throw new Exception("Lütfen bir müşteri seçin!");
        }

        $stmt->execute([
            ':personel_id' => $_SESSION['personel_id'],
            ':musteri_id' => $musteri_id,
            ':islem_turu' => $_POST['islem_turu'],
            ':dosya_turu' => $_POST['dosya_turu'],
            ':il' => $_POST['il'],
            ':ilce' => $_POST['ilce'],
            ':mahalle' => $_POST['mahalle'],
            ':ada' => $_POST['ada'],
            ':parsel' => $_POST['parsel']
        ]);

        // Log kaydı
        $logger->logKaydet(
            $_SESSION['personel_id'],
            'DOSYA_EKLE',
            "Yeni dosya eklendi: {$_POST['dosya_turu']} - Müşteri ID: {$musteri_id}"
        );

        $success_message = "Dosya başarıyla eklendi!";
    } catch(PDOException $e) {
        $error_message = "Dosya eklenirken bir hata oluştu: " . $e->getMessage();
    } catch(Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Dosya Ekle</title>
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
        .required:after {
            content: " *";
            color: red;
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
                <h2 class="mb-4">Yeni Dosya Ekle</h2>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Tarih</label>
                                    <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Müşteri</label>
                                    <div class="input-group">
                                        <select name="musteri_id" class="form-select" required>
                                            <option value="">Müşteri Seçiniz</option>
                                            <?php 
                                            if (!empty($musteriler)): 
                                                foreach ($musteriler as $musteri): 
                                            ?>
                                                <option value="<?php echo htmlspecialchars($musteri['musteri_id']); ?>">
                                                    <?php 
                                                    echo htmlspecialchars($musteri['musteri_adi']) . 
                                                         ' (' . htmlspecialchars($musteri['musteri_turu']) . ')'; 
                                                    ?>
                                                </option>
                                            <?php 
                                                endforeach; 
                                            else:
                                            ?>
                                                <option value="" disabled>Müşteri bulunamadı</option>
                                            <?php endif; ?>
                                        </select>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#yeniMusteriModal">
                                            <i class="bi bi-plus-circle"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Dosya Türü</label>
                                    <select name="dosya_turu" class="form-select" required>
                                        <option value="">Seçiniz</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">İşlem Türü</label>
                                    <select name="islem_turu" class="form-select" required>
                                        <option value="">Seçiniz</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label required">İl</label>
                                    <select name="il" class="form-select" required>
                                        <option value="">Seçiniz</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required">İlçe</label>
                                    <select name="ilce" class="form-select" required>
                                        <option value="">Seçiniz</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required">Öncelikli Adres</label>
                                    <input type="text" name="mahalle" class="form-control" required>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Ada No</label>
                                    <input type="text" name="ada" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Parsel No</label>
                                    <input type="text" name="parsel" class="form-control">
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Dosyayı Kaydet
                                </button>
                                <a href="dosya_takip.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> İptal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Yeni Müşteri Modal -->
    <div class="modal fade" id="yeniMusteriModal" tabindex="-1" aria-labelledby="yeniMusteriModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="yeniMusteriModalLabel">Yeni Müşteri Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body">
                    <iframe src="yeni_musteri.php?modal=true" style="width: 100%; height: 80vh; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Sayfa yüklendiğinde verileri al
    fetch('get_mahalleler.php')
        .then(response => response.json())
        .then(data => {
            // Dosya türlerini doldur
            const dosyaTuruSelect = document.querySelector('select[name="dosya_turu"]');
            data.dosya_turleri.forEach(tur => {
                dosyaTuruSelect.innerHTML += `<option value="${tur}">${tur}</option>`;
            });

            // İşlem türlerini doldur
            const islemTuruSelect = document.querySelector('select[name="islem_turu"]');
            data.islem_turleri.forEach(tur => {
                islemTuruSelect.innerHTML += `<option value="${tur}">${tur}</option>`;
            });

            // İl ve ilçe seçimi
            const ilSelect = document.querySelector('select[name="il"]');
            Object.keys(data.iller).forEach(il => {
                ilSelect.innerHTML += `<option value="${il}">${il}</option>`;
            });
            const ilceSelect = document.querySelector('select[name="ilce"]');

            ilSelect.addEventListener('change', function() {
            const il = this.value;
            ilceSelect.innerHTML = '<option value="">Seçiniz</option>';
            if (il) {
                data.iller[il].forEach(ilce => {
                    ilceSelect.innerHTML += `<option value="${ilce}">${ilce}</option>`;
                });
                }
            });
        });

    // Modal kapandığında müşteri listesini güncelle
    document.getElementById('yeniMusteriModal').addEventListener('hidden.bs.modal', function () {
        // Müşteri listesini güncelle
        fetch('get_musteriler.php')
            .then(response => response.json())
            .then(data => {
                const musteriSelect = document.querySelector('select[name="musteri_id"]');
                musteriSelect.innerHTML = '<option value="">Müşteri Seçiniz</option>';
                
                data.forEach(musteri => {
                    musteriSelect.innerHTML += `
                        <option value="${musteri.musteri_id}">
                            ${musteri.musteri_adi} (${musteri.musteri_turu})
                        </option>`;
                });
            });
    });
    </script>
</body>
</html> 