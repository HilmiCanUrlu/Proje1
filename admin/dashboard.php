<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

$auth = new Auth($db);
if(!$auth->isLoggedIn() || !$auth->hasPermission('admin')) {
    header('Location: ../login.php');
    exit();
}

// Dashboard verilerini hazırla
$financeManager = new FinanceManager($db);
$documentManager = new DocumentManager($db);
$taskManager = new TaskManager($db);
$clientManager = new ClientManager($db);

// Son 30 günlük özet
$startDate = date('Y-m-d', strtotime('-30 days'));
$endDate = date('Y-m-d');

$financialSummary = $financeManager->getTransactionSummary($startDate, $endDate);
$recentDocuments = $documentManager->getDocuments([], 1);
$pendingTasks = $taskManager->getTasks(null, 'pending');
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli - Büro Otomasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/admin-style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>
                
                <!-- İstatistik Kartları -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Gelir</h5>
                                <p class="card-text"><?= number_format($financialSummary['total_income'], 2) ?> ₺</p>
                            </div>
                        </div>
                    </div>
                    <!-- Diğer istatistik kartları -->
                </div>
                
                <!-- Grafikler -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <canvas id="incomeChart"></canvas>
                    </div>
                    <div class="col-md-6">
                        <canvas id="taskChart"></canvas>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/admin.js"></script>
</body>
</html> 