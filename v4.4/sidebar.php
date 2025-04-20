<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
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
        background-color: #e9ecef;
        color: #333;
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
        color: #28a745;
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
    .nav-item.dropdown {
        margin-bottom: 0.5rem;
    }
    .nav-item.dropdown .nav-link[aria-expanded="true"] {
        color: #333;
        background-color: #e9ecef;
    }
    .nav-item.dropdown .nav-link[aria-expanded="true"] i {
        color: #28a745;
    }
    /* Dropdown başlıkları için sabit stil */
    .nav-item.dropdown > .nav-link {
        color: #333 !important;
        background-color: transparent !important;
    }
    .nav-item.dropdown > .nav-link i:not(.bi-chevron-down) {
        color: #28a745 !important;
    }
    /* Alt menü aktif öğesi için gri arka plan */
    .collapse .nav-link.active {
        background-color: #e9ecef;
        color: #333;
    }
    .collapse .nav-link.active i {
        color: #28a745;
    }
</style>

<!-- Sol Sidebar -->
<div class="col-md-2 sidebar py-3">
    <div class="text-center mb-4">
        <h3 class="text-primary">LOGO</h3>
        <div class="border-bottom border-2 mb-3"></div>
    </div>
    <nav class="nav flex-column">
        <a class="nav-link mb-2" href="dashboard.php">
            <i class="bi bi-house-door"></i>Dashboard
        </a>
        <a class="nav-link mb-2" href="kullanici_yonetim.php">
            <i class="bi bi-people"></i>Personel Yönetimi
        </a>
        
        <!-- Dosya İşlemleri Dropdown -->
        <div class="nav-item dropdown mb-2">
            <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#dosyaCollapse" role="button" aria-expanded="false" aria-controls="dosyaCollapse">
                <span><i class="bi bi-folder"></i>Dosya İşlemleri</span>
                <i class="bi bi-chevron-down"></i>
            </a>
            <div class="collapse" id="dosyaCollapse">
                <div class="nav flex-column">
                    <a class="nav-link ps-4" href="dosya_takip.php">
                        <i class="bi bi-folder-symlink"></i>Dosya Takip
                    </a>
                    <a class="nav-link ps-4" href="yeni_dosya.php">
                        <i class="bi bi-file-earmark-plus"></i>Yeni Dosya Ekle
                    </a>
                </div>
            </div>
        </div>

        <!-- Müşteri İşlemleri Dropdown -->
        <div class="nav-item dropdown mb-2">
            <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#musteriCollapse" role="button" aria-expanded="false" aria-controls="musteriCollapse">
                <span><i class="bi bi-person"></i>Müşteri İşlemleri</span>
                <i class="bi bi-chevron-down"></i>
            </a>
            <div class="collapse" id="musteriCollapse">
                <div class="nav flex-column">
                    <a class="nav-link ps-4" href="musteri_takip.php">
                        <i class="bi bi-people-fill"></i>Müşteri Takip
                    </a>
                    <a class="nav-link ps-4" href="yeni_musteri.php">
                        <i class="bi bi-person-plus-fill"></i>Yeni Müşteri
                    </a>
                </div>
            </div>
        </div>

        <a class="nav-link" href="loglar.php">
            <i class="bi bi-gear"></i>Sistem Logları
        </a>
    </nav>
</div>

<script>
    // Sayfa yüklendiğinde aktif menüyü işaretle
    document.addEventListener('DOMContentLoaded', function() {
        markActiveMenu();
    });

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