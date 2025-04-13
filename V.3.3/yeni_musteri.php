<?php
session_start();
require_once "database.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Debug için form verilerini yazdır
        error_log("POST Verileri: " . print_r($_POST, true));
        
        $musteri_turu = $_POST['musteri_turu'];
        
        // Gerçek kişi için
        if ($musteri_turu == 'Gerçek Kişi') {
            if (empty($_POST['gercek_musteri_adi'])) {
                throw new Exception("Müşteri Adı alanı zorunludur!");
            }
            if (empty($_POST['gercek_telefon'])) {
                throw new Exception("Telefon alanı zorunludur!");
            }
            
            $musteri_adi = trim($_POST['gercek_musteri_adi']);
            $telefon = trim($_POST['gercek_telefon']);
            $email = !empty($_POST['gercek_email']) ? trim($_POST['gercek_email']) : null;
            $tc_kimlik_no = !empty($_POST['tc_kimlik_no']) ? trim($_POST['tc_kimlik_no']) : null;
            $firma_adi = null;
            $vergi_dairesi = null;
            $vergi_no = null;
        } 
        // Tüzel kişi için
        else {
            if (empty($_POST['tuzel_musteri_adi'])) {
                throw new Exception("Müşteri Adı alanı zorunludur!");
            }
            if (empty($_POST['tuzel_telefon'])) {
                throw new Exception("Telefon alanı zorunludur!");
            }
            
            $musteri_adi = trim($_POST['tuzel_musteri_adi']);
            $telefon = trim($_POST['tuzel_telefon']);
            $email = !empty($_POST['tuzel_email']) ? trim($_POST['tuzel_email']) : null;
            $tc_kimlik_no = null;
            $firma_adi = !empty($_POST['firma_adi']) ? trim($_POST['firma_adi']) : null;
            $vergi_dairesi = !empty($_POST['vergi_dairesi']) ? trim($_POST['vergi_dairesi']) : null;
            $vergi_no = !empty($_POST['vergi_no']) ? trim($_POST['vergi_no']) : null;
        }

        // Fatura adresi kontrolü
        if (empty($_POST['fatura_adresi'])) {
            throw new Exception("Fatura Adresi alanı zorunludur!");
        }
        $fatura_adresi = trim($_POST['fatura_adresi']);

        // SQL sorgusu
        $sql = "INSERT INTO musteriler (
            musteri_turu, 
            musteri_adi, 
            tc_kimlik_no, 
            telefon, 
            email, 
            fatura_adresi, 
            firma_adi, 
            vergi_dairesi, 
            vergi_no
        ) VALUES (
            :musteri_turu,
            :musteri_adi,
            :tc_kimlik_no,
            :telefon,
            :email,
            :fatura_adresi,
            :firma_adi,
            :vergi_dairesi,
            :vergi_no
        )";

        $stmt = $db->prepare($sql);
        
        $params = [
            ':musteri_turu' => $musteri_turu,
            ':musteri_adi' => $musteri_adi,
            ':tc_kimlik_no' => $tc_kimlik_no,
            ':telefon' => $telefon,
            ':email' => $email,
            ':fatura_adresi' => $fatura_adresi,
            ':firma_adi' => $firma_adi,
            ':vergi_dairesi' => $vergi_dairesi,
            ':vergi_no' => $vergi_no
        ];

        // Debug için SQL ve parametreleri yazdır
        error_log("SQL: " . $sql);
        error_log("Parametreler: " . print_r($params, true));

        if ($stmt->execute($params)) {
            $message = '<div class="alert alert-success">Müşteri başarıyla eklendi!</div>';
            $_POST = array(); // Formu temizle
        } else {
            throw new Exception("Müşteri eklenirken bir hata oluştu.");
        }

    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Veritabanı hatası: ' . $e->getMessage() . '</div>';
        error_log("PDO Exception: " . $e->getMessage());
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
        error_log("Exception: " . $e->getMessage());
    }
}

// Form başlamadan önce mesajı göster
if (!empty($message)) {
    echo $message;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Müşteri Ekle</title>
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
            <?php if (!isset($_GET['modal'])): ?>
            <!-- Sidebar -->
            <div class="col-md-2 sidebar py-3">
                <div class="text-center mb-4">
                    <h3 class="text-primary">LOGO</h3>
                    <div class="border-bottom border-2 mb-3"></div>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php"><i class="bi bi-house-door me-2"></i>Dashboard</a>
                    <a class="nav-link" href="kullanici_yonetim.php"><i class="bi bi-people me-2"></i>Kullanıcı Yönetimi</a>
                    <a class="nav-link" href="dosya_takip.php"><i class="bi bi-folder me-2"></i>Dosya Takip</a>
                    <a class="nav-link" href="musteri_takip.php"><i class="bi bi-person me-2"></i>Müşteri Takip</a>
                    <a class="nav-link active" href="yeni_musteri.php"><i class="bi bi-person-plus me-2"></i>Yeni Müşteri</a>
                    <a class="nav-link" href="yeni_dosya.php"><i class="bi bi-file-plus me-2"></i>Yeni Dosya Ekle</a>
                    <a class="nav-link" href="#"><i class="bi bi-gear me-2"></i>Ayarlar</a>
                </nav>
            </div>
            <?php endif; ?>

            <!-- Ana İçerik -->
            <div class="<?php echo isset($_GET['modal']) ? 'col-md-12' : 'col-md-10'; ?> py-3">
                <h2 class="mb-4">Yeni Müşteri Ekle</h2>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <!-- Müşteri Türü Radio Buttons -->
                            <div class="mb-3">
                                <div class="btn-group" role="group" aria-label="Müşteri Türü">
                                    <input type="radio" class="btn-check" name="musteri_turu" id="gercek_kisi" value="Gerçek Kişi" checked>
                                    <label class="btn btn-outline-primary" for="gercek_kisi">Gerçek Kişi</label>

                                    <input type="radio" class="btn-check" name="musteri_turu" id="tuzel_kisi" value="Tüzel kişi">
                                    <label class="btn btn-outline-primary" for="tuzel_kisi">Tüzel Kişi</label>
                                </div>
                            </div>

                            <!-- Gerçek Kişi Alanları -->
                            <div id="gercek_kisi_alanlari">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Müşteri Adı *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" name="gercek_musteri_adi" id="gercek_musteri_adi" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">TC Kimlik No</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                            <input type="text" name="tc_kimlik_no" class="form-control" maxlength="11">
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Telefon *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                            <input type="tel" name="gercek_telefon" id="gercek_telefon" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">E-posta</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                            <input type="email" name="gercek_email" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tüzel Kişi Alanları -->
                            <div id="tuzel_kisi_alanlari" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Müşteri Adı *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" name="tuzel_musteri_adi" id="tuzel_musteri_adi" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Firma Adı *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-building"></i></span>
                                            <input type="text" name="firma_adi" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Telefon *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                            <input type="tel" name="tuzel_telefon" id="tuzel_telefon" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">E-posta</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                            <input type="email" name="tuzel_email" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Vergi Dairesi *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-building"></i></span>
                                            <input type="text" name="vergi_dairesi" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Vergi No *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-upc"></i></span>
                                            <input type="text" name="vergi_no" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Ortak Alan -->
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Fatura Adresi *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                        <textarea name="fatura_adresi" class="form-control" rows="3" required></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Butonları -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Müşteri Ekle
                                    </button>
                                    <a href="musteri_takip.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> İptal
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const gercekKisiRadio = document.getElementById('gercek_kisi');
        const tuzelKisiRadio = document.getElementById('tuzel_kisi');
        const gercekKisiAlanlari = document.getElementById('gercek_kisi_alanlari');
        const tuzelKisiAlanlari = document.getElementById('tuzel_kisi_alanlari');

        function musteriTuruDegisti() {
            if (gercekKisiRadio.checked) {
                gercekKisiAlanlari.style.display = 'block';
                tuzelKisiAlanlari.style.display = 'none';
                // Gerçek kişi alanlarını required yap
                document.getElementById('gercek_musteri_adi').required = true;
                document.getElementById('gercek_telefon').required = true;
                // Tüzel kişi alanlarından required kaldır
                document.getElementById('tuzel_musteri_adi').required = false;
            } else {
                gercekKisiAlanlari.style.display = 'none';
                tuzelKisiAlanlari.style.display = 'block';
                // Tüzel kişi alanlarını required yap
                document.getElementById('tuzel_musteri_adi').required = true;
                // Gerçek kişi alanlarından required kaldır
                document.getElementById('gercek_musteri_adi').required = false;
                document.getElementById('gercek_telefon').required = false;
            }
        }

        gercekKisiRadio.addEventListener('change', musteriTuruDegisti);
        tuzelKisiRadio.addEventListener('change', musteriTuruDegisti);
        
        // Sayfa yüklendiğinde gerçek kişi formunu göster
        musteriTuruDegisti();
    });
    </script>
</body>
</html>
