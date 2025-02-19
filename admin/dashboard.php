<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

session_start();

// Oturum kontrolü
if(!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Dashboard';
$extra_css = [
    BASE_URL . '/admin/css/admin-style.css'
];
$extra_js = [
    'https://cdn.jsdelivr.net/npm/chart.js',
    BASE_URL . '/admin/js/admin.js'
];

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-primary">
                        <i class="fas fa-users me-2"></i>
                        Toplam Müşteri
                    </h5>
                    <h2 class="mt-3 mb-0">150</h2>
                    <small class="text-muted">Son 30 gün: +12</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-success">
                        <i class="fas fa-file-alt me-2"></i>
                        Aktif Belgeler
                    </h5>
                    <h2 class="mt-3 mb-0">85</h2>
                    <small class="text-muted">Tamamlanan: 245</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-warning">
                        <i class="fas fa-tasks me-2"></i>
                        Bekleyen Görevler
                    </h5>
                    <h2 class="mt-3 mb-0">24</h2>
                    <small class="text-muted">Bugün son tarihli: 5</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-info">
                        <i class="fas fa-chart-line me-2"></i>
                        Aylık Gelir
                    </h5>
                    <h2 class="mt-3 mb-0">₺45,250</h2>
                    <small class="text-muted">Geçen ay: ₺42,180</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Gelir Grafiği</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary">Haftalık</button>
                        <button type="button" class="btn btn-sm btn-primary">Aylık</button>
                        <button type="button" class="btn btn-sm btn-outline-primary">Yıllık</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="incomeChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Son Aktiviteler</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Yeni müşteri eklendi</h6>
                                <small>3 dk önce</small>
                            </div>
                            <p class="mb-1">Ahmet Yılmaz</p>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Belge güncellendi</h6>
                                <small>1 saat önce</small>
                            </div>
                            <p class="mb-1">DOC-2024-0125</p>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Yeni görev oluşturuldu</h6>
                                <small>2 saat önce</small>
                            </div>
                            <p class="mb-1">Dosya kontrolü</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Gelir Grafiği</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary">Haftalık</button>
                        <button type="button" class="btn btn-sm btn-primary">Aylık</button>
                        <button type="button" class="btn btn-sm btn-outline-primary">Yıllık</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="incomeChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Son Aktiviteler</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Yeni müşteri eklendi</h6>
                                <small>3 dk önce</small>
                            </div>
                            <p class="mb-1">Ahmet Yılmaz</p>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Belge güncellendi</h6>
                                <small>1 saat önce</small>
                            </div>
                            <p class="mb-1">DOC-2024-0125</p>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Yeni görev oluşturuldu</h6>
                                <small>2 saat önce</small>
                            </div>
                            <p class="mb-1">Dosya kontrolü</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>