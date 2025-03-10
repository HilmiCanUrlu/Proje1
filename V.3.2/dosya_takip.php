<?php
session_start();
require_once "database.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Filtreleme parametrelerini al
$where = "1=1";
$params = array();

if (isset($_GET['musteri_adi']) && !empty($_GET['musteri_adi'])) {
    $where .= " AND m.musteri_adi LIKE :musteri_adi";
    $params[':musteri_adi'] = '%' . $_GET['musteri_adi'] . '%';
}

if (isset($_GET['dosya_turu']) && !empty($_GET['dosya_turu'])) {
    $where .= " AND d.dosya_turu = :dosya_turu";
    $params[':dosya_turu'] = $_GET['dosya_turu'];
}

if (isset($_GET['islem_turu']) && !empty($_GET['islem_turu'])) {
    $where .= " AND d.islem_turu = :islem_turu";
    $params[':islem_turu'] = $_GET['islem_turu'];
}

if (isset($_GET['il']) && !empty($_GET['il'])) {
    $where .= " AND d.il = :il";
    $params[':il'] = $_GET['il'];
}

// Dosyaları getir
$query = "SELECT d.*, m.musteri_adi 
          FROM dosyalar d 
          LEFT JOIN musteriler m ON d.musteri_id = m.musteri_id 
          WHERE $where 
          ORDER BY d.olusturma_tarihi DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$dosyalar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kartlar için dosya türlerini say
$shkm_count = count(array_filter($dosyalar, function($d) { return $d['dosya_turu'] == 'SHKM Dosyaları'; }));
$lihkab_count = count(array_filter($dosyalar, function($d) { return $d['dosya_turu'] == 'LİHKAB'; }));
$takim_proje_count = count(array_filter($dosyalar, function($d) { return $d['dosya_turu'] == 'Takım Proje'; }));
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya Takip</title>
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
        .stats-card {
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sol Sidebar -->
            <div class="col-md-2 sidebar py-3">
                <div class="text-center mb-4">
                    <h3 class="text-primary">LOGO</h3>
                    <div class="border-bottom border-2 mb-3"></div>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php"><i class="bi bi-house-door me-2"></i>Dashboard</a>
                    <a class="nav-link" href="kullanici_yonetim.php"><i class="bi bi-people me-2"></i>Kullanıcı Yönetimi</a>
                    <a class="nav-link active" href="dosya_takip.php"><i class="bi bi-folder me-2"></i>Dosya Takip</a>
                    <a class="nav-link" href="musteri_takip.php"><i class="bi bi-person me-2"></i>Müşteri Takip</a>
                    <a class="nav-link" href="yeni_musteri.php"><i class="bi bi-person-plus me-2"></i>Yeni Müşteri</a>
                    <a class="nav-link" href="yeni_dosya.php"><i class="bi bi-file-plus me-2"></i>Yeni Dosya Ekle</a>
                    <a class="nav-link" href="#"><i class="bi bi-gear me-2"></i>Ayarlar</a>
                </nav>
            </div>

            <!-- Ana İçerik -->
            <div class="col-md-10 py-3">
                <h2 class="mb-4">Dosya Takip</h2>
                <!-- Üst Kartlar -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card bg-white">
                            <h6>Tapu Dosyaları</h6>
                            <h2><?php echo $shkm_count; ?></h2>
                            <i class="bi bi-folder text-primary"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card bg-white">
                            <h6>Belediye Dosyaları</h6>
                            <h2><?php echo $lihkab_count; ?></h2>
                            <i class="bi bi-folder text-success"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card bg-white">
                            <h6>Tamamlanan Dosyalar</h6>
                            <h2><?php echo $takim_proje_count; ?></h2>
                            <i class="bi bi-folder text-warning"></i>
                        </div>
                    </div>
                </div>

                <!-- Hızlı İşlemler ve Filtreleme Formu -->
                <div class="row">
                    <div class="col-md-8">
                        <!-- Dosya Listesi -->
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Dosyalar</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 25%">Müşteri Adı</th>
                                                <th style="width: 15%">Dosya Türü</th>
                                                <th style="width: 25%">İlçe-Mahalle</th>
                                                <th style="width: 15%">Ada/Parsel</th>
                                                <th style="width: 20%">İşlemler</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dosyalar as $dosya): ?>
                                            <tr>
                                                <td class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($dosya['musteri_adi']); ?>">
                                                    <?php echo htmlspecialchars($dosya['musteri_adi']); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($dosya['dosya_turu']); ?></td>
                                                <td class="text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($dosya['ilce'] . ' ' . $dosya['mahalle']); ?>">
                                                    <?php echo htmlspecialchars($dosya['ilce'] . ' ' . $dosya['mahalle']); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($dosya['ada'] . '/' . $dosya['parsel']); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-primary" onclick="showDosyaDetay(<?php echo $dosya['dosya_id']; ?>)">
                                                            Detay
                                                        </button>
                                                        <button class="btn btn-success" onclick="showMuhasebeDetay(<?php echo $dosya['dosya_id']; ?>)">
                                                            Muhasebe
                                                        </button>
                                                        <button class="btn btn-danger">
                                                            Sil
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Dosya Süz</h5>
                            </div>
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Müşteri Adı</label>
                                        <input type="text" name="musteri_adi" class="form-control" value="<?php echo $_GET['musteri_adi'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Dosya Türü</label>
                                        <select name="dosya_turu" class="form-select">
                                            <option value="">Seçiniz</option>
                                            <!-- JavaScript ile doldurulacak -->
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">İşlem Türü</label>
                                        <select name="islem_turu" class="form-select">
                                            <option value="">Seçiniz</option>
                                            <!-- JavaScript ile doldurulacak -->
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">İl</label>
                                        <select name="il" class="form-select">
                                            <option value="">Seçiniz</option>
                                            <!-- JavaScript ile doldurulacak -->
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">Filtrele</button>
                                        <a href="dosya_takip.php" class="btn btn-secondary">Sıfırla</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Muhasebe Modal -->
                <div class="modal fade" id="muhasebeModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Muhasebe Detayları</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Özet Kartları -->
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body">
                                                <h6>Toplam Tutar</h6>
                                                <h3 id="toplamTutarOzet">0.00 ₺</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-success text-white">
                                            <div class="card-body">
                                                <h6>Yapılan Ödemeler</h6>
                                                <h3 id="yapilanOdemeOzet">0.00 ₺</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-warning text-white">
                                            <div class="card-body">
                                                <h6>Kalan Tutar</h6>
                                                <h3 id="kalanTutarOzet">0.00 ₺</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- İşlemler Tablosu -->
                                <div class="table-responsive">
                                    <table class="table table-striped">
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
                                        <tbody id="muhasebeIslemlerListesi"></tbody>
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

                <!-- İşlem Ekleme Modal -->
                <div class="modal fade" id="islemEkleModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Yeni İşlem Ekle</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="islemEkleForm">
                                    <input type="hidden" id="islemDosyaId" name="dosya_id">
                                    <div class="mb-3" id="toplamTutarDiv">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label mb-0">Toplam Tutar</label>
                                            <button type="button" class="btn btn-sm btn-warning" id="toplamTutarDegistir">
                                                Toplam Tutarı Değiştir
                                            </button>
                                        </div>
                                        <input type="number" class="form-control" id="toplamTutar" name="toplam_tutar" step="0.01" readonly>
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
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Filtreleme seçeneklerini doldur
    fetch('get_mahalleler.php')
        .then(response => response.json())
        .then(data => {
            const dosyaTuruSelect = document.querySelector('select[name="dosya_turu"]');
            const islemTuruSelect = document.querySelector('select[name="islem_turu"]');
            const ilSelect = document.querySelector('select[name="il"]');

            // Dosya türlerini doldur
            data.dosya_turleri.forEach(tur => {
                dosyaTuruSelect.innerHTML += `<option value="${tur}">${tur}</option>`;
            });

            // İşlem türlerini doldur
            data.islem_turleri.forEach(tur => {
                islemTuruSelect.innerHTML += `<option value="${tur}">${tur}</option>`;
            });

            // İlleri doldur
            Object.keys(data.iller).forEach(il => {
                ilSelect.innerHTML += `<option value="${il}">${il}</option>`;
            });
        });

    // Dosya detaylarını göster
    function showDosyaDetay(dosyaId) {
        fetch(`get_dosya_detay.php?dosya_id=${dosyaId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('dosyaBilgileri').innerHTML = `
                    <p><strong>Dosya Türü:</strong> ${data.dosya.dosya_turu}</p>
                    <p><strong>İşlem Türü:</strong> ${data.dosya.islem_turu}</p>
                    <p><strong>İl/İlçe:</strong> ${data.dosya.il}/${data.dosya.ilce}</p>
                    <p><strong>Mahalle:</strong> ${data.dosya.mahalle}</p>
                    <p><strong>Ada/Parsel:</strong> ${data.dosya.ada}/${data.dosya.parsel}</p>
                    <p><strong>Durum:</strong> ${data.dosya.dosya_durumu}</p>
                `;

                document.getElementById('musteriBilgileri').innerHTML = `
                    <p><strong>Müşteri Adı:</strong> ${data.musteri.musteri_adi}</p>
                    <p><strong>Telefon:</strong> ${data.musteri.telefon}</p>
                    <p><strong>Email:</strong> ${data.musteri.email}</p>
                `;

                // İşlemleri listele
                let islemlerHTML = `
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Toplam Tutar</th>
                                <th>Yapılan Ödeme</th>
                                <th>Kalan Tutar</th>
                                <th>Açıklama</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                data.islemler.forEach(islem => {
                    // Tarihi formatla
                    const tarih = new Date(islem.olusturma_tarihi);
                    const formatliTarih = tarih.toLocaleString('tr-TR', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    islemlerHTML += `
                        <tr>
                            <td>${formatliTarih}</td>
                            <td>${parseFloat(islem.toplam_tutar).toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺</td>
                            <td>${parseFloat(islem.yapilan_tutar).toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺</td>
                            <td>${parseFloat(islem.kalan_tutar).toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺</td>
                            <td>${islem.aciklama || ''}</td>
                        </tr>
                    `;
                });

                islemlerHTML += '</tbody></table>';
                document.getElementById('islemlerListesi').innerHTML = islemlerHTML;
                
                // Modal'ı göster
                new bootstrap.Modal(document.getElementById('dosyaDetayModal')).show();
            });
    }

    let currentDosyaId = null;
    let currentKalanTutar = 0;
    let isToplamTutarEditable = false;

    function showMuhasebeDetay(dosyaId) {
        currentDosyaId = dosyaId;
        document.getElementById('islemDosyaId').value = dosyaId;
        
        fetch(`muhasebe_detay.php?dosya_id=${dosyaId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Özet bilgileri güncelle
                    document.getElementById('toplamTutarOzet').textContent = 
                        data.ozet.toplam_tutar.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                    document.getElementById('yapilanOdemeOzet').textContent = 
                        data.ozet.toplam_yapilan.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                    document.getElementById('kalanTutarOzet').textContent = 
                        data.ozet.toplam_kalan.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';

                    // İşlemler tablosunu güncelle
                    let islemlerHTML = '';
                    data.islemler.forEach(islem => {
                        // Tarihi formatla
                        const tarih = new Date(islem.olusturma_tarihi);
                        const formatliTarih = tarih.toLocaleString('tr-TR', {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        islemlerHTML += `
                            <tr>
                                <td>${islem.islem_id}</td>
                                <td>${formatliTarih}</td>
                                <td>${parseFloat(islem.toplam_tutar).toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺</td>
                                <td>${parseFloat(islem.yapilan_tutar).toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺</td>
                                <td>${parseFloat(islem.kalan_tutar).toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺</td>
                                <td>${islem.aciklama || ''}</td>
                            </tr>
                        `;
                    });
                    document.getElementById('muhasebeIslemlerListesi').innerHTML = islemlerHTML;

                    // Modalı göster
                    new bootstrap.Modal(document.getElementById('muhasebeModal')).show();
                } else {
                    alert('Veri yüklenirken bir hata oluştu');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu');
            });
    }

    // Toplam tutar değiştirme butonunu yönet
    document.getElementById('toplamTutarDegistir').addEventListener('click', function() {
        const toplamTutarInput = document.getElementById('toplamTutar');
        const yapilanTutarInput = document.getElementById('yapilanTutar');
        const kalanTutarInput = document.getElementById('kalanTutar');
        isToplamTutarEditable = !isToplamTutarEditable;
        
        if (isToplamTutarEditable) {
            // Düzenleme modunu aç
            toplamTutarInput.readOnly = false;
            yapilanTutarInput.required = false; // Yapılan ödeme zorunlu değil
            yapilanTutarInput.value = ''; // Yapılan ödemeyi temizle
            kalanTutarInput.value = ''; // Kalan tutarı temizle
            this.classList.remove('btn-warning');
            this.classList.add('btn-success');
            this.textContent = 'Değişikliği Onayla';
        } else {
            // Değişikliği onayla ve kaydet
            toplamTutarInput.readOnly = true;
            this.classList.remove('btn-success');
            this.classList.add('btn-warning');
            this.textContent = 'Toplam Tutarı Değiştir';
            
            const yeniToplamTutar = parseFloat(toplamTutarInput.value) || 0;
            
            // Toplam tutar değişikliğini kaydet
            const formData = new FormData();
            formData.append('dosya_id', currentDosyaId);
            formData.append('toplam_tutar', yeniToplamTutar);
            formData.append('is_toplam_tutar_update', '1'); // Toplam tutar güncelleme flag'i
            
            fetch('islem_ekle.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Başarılı olursa muhasebe detayını yenile
                    showMuhasebeDetay(currentDosyaId);
                    yapilanTutarInput.required = true; // Yapılan ödeme tekrar zorunlu
                } else {
                    alert('Toplam tutar güncellenirken bir hata oluştu: ' + data.message);
                }
            });
        }
    });

    // İşlem ekleme modalını göster
    function showIslemEkle() {
        document.getElementById('islemEkleForm').reset();
        document.getElementById('islemDosyaId').value = currentDosyaId;

        // İlk işlem kontrolü yap
        fetch(`muhasebe_detay.php?dosya_id=${currentDosyaId}`)
            .then(response => response.json())
            .then(data => {
                const toplamTutarDiv = document.getElementById('toplamTutarDiv');
                const toplamTutarInput = document.getElementById('toplamTutar');
                const kalanTutarInput = document.getElementById('kalanTutar');
                const yapilanTutarInput = document.getElementById('yapilanTutar');
                const toplamTutarDegistirBtn = document.getElementById('toplamTutarDegistir');
                
                if (!data.ilk_islem_var) {
                    // İlk işlem ise toplam tutar alanını düzenlenebilir yap
                    toplamTutarDiv.style.display = 'block';
                    toplamTutarInput.readOnly = false;
                    toplamTutarInput.required = true;
                    kalanTutarInput.value = '';
                    toplamTutarDegistirBtn.style.display = 'none';
                } else {
                    // İlk işlem değilse toplam tutarı göster ama readonly yap
                    toplamTutarDiv.style.display = 'block';
                    toplamTutarInput.readOnly = true;
                    toplamTutarInput.required = false;
                    toplamTutarInput.value = data.ozet.toplam_tutar;
                    currentKalanTutar = data.ozet.toplam_kalan;
                    kalanTutarInput.value = currentKalanTutar.toFixed(2);
                    toplamTutarDegistirBtn.style.display = 'block';
                }

                // Yapılan ödeme değiştiğinde kalan tutarı güncelle
                yapilanTutarInput.addEventListener('input', function() {
                    const yapilanTutar = parseFloat(this.value) || 0;
                    const kalanTutar = (parseFloat(toplamTutarInput.value) || 0) - yapilanTutar;
                    kalanTutarInput.value = kalanTutar.toFixed(2);
                });

                // Kalan tutar alanını sadece okunabilir yap
                kalanTutarInput.readOnly = true;
            });

        bootstrap.Modal.getInstance(document.getElementById('muhasebeModal')).hide();
        new bootstrap.Modal(document.getElementById('islemEkleModal')).show();
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
                showMuhasebeDetay(currentDosyaId);
            } else {
                alert('Bir hata oluştu: ' + data.message);
            }
        });
    });
    </script>
</body>
</html>
