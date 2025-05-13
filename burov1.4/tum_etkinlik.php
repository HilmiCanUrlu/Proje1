<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tüm Etkinlikler</title>
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
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
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
        .nav-item.dropdown {
            margin-bottom: 0.5rem;
        }
        .stats-card {
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php
    session_start();
    
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("Location: login.php");
        exit;
    }
    
    require_once 'database.php';
    $database = new Database();
    $conn = $database->getConnection();

    // Filtreleme parametrelerini al
    $baslik = isset($_GET['baslik']) ? $_GET['baslik'] : '';
    $tarih_baslangic = isset($_GET['tarih_baslangic']) ? $_GET['tarih_baslangic'] : '';
    $tarih_bitis = isset($_GET['tarih_bitis']) ? $_GET['tarih_bitis'] : '';
    $tekrar_aylik = isset($_GET['tekrar_aylik']) ? $_GET['tekrar_aylik'] : '';
    $tekrar_yillik = isset($_GET['tekrar_yillik']) ? $_GET['tekrar_yillik'] : '';

    // SQL sorgusunu oluştur
    $sql = "SELECT * FROM etkinlikler WHERE 1=1";
    $params = [];

    if (!empty($baslik)) {
        $sql .= " AND baslik LIKE :baslik";
        $params[':baslik'] = "%$baslik%";
    }

    if (!empty($tarih_baslangic)) {
        $sql .= " AND tarih >= :tarih_baslangic";
        $params[':tarih_baslangic'] = $tarih_baslangic;
    }

    if (!empty($tarih_bitis)) {
        $sql .= " AND tarih <= :tarih_bitis";
        $params[':tarih_bitis'] = $tarih_bitis;
    }

    if ($tekrar_aylik !== '') {
        $sql .= " AND tekrar_aylik = :tekrar_aylik";
        $params[':tekrar_aylik'] = $tekrar_aylik;
    }

    if ($tekrar_yillik !== '') {
        $sql .= " AND tekrar_yillik = :tekrar_yillik";
        $params[':tekrar_yillik'] = $tekrar_yillik;
    }

    $sql .= " ORDER BY tarih ASC";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // İstatistikleri hesapla
        $toplam_etkinlik = count($events);
        $aylik_tekrar = count(array_filter($events, function($e) { return $e['tekrar_aylik'] == 1; }));
        $yillik_tekrar = count(array_filter($events, function($e) { return $e['tekrar_yillik'] == 1; }));

    } catch(PDOException $e) {
        echo "Veritabanı hatası: " . $e->getMessage();
        $events = [];
        $toplam_etkinlik = $aylik_tekrar = $yillik_tekrar = 0;
    }
    ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sol Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Ana İçerik -->
            <div class="col-md-10 py-3">
                <h2 class="mb-4">Tüm Etkinlikler</h2>
                
                <!-- Üst Kartlar -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card bg-white">
                            <h6>Toplam Etkinlik</h6>
                            <h2><?php echo $toplam_etkinlik; ?></h2>
                            <i class="bi bi-calendar-event text-primary"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card bg-white">
                            <h6>Aylık Tekrar</h6>
                            <h2><?php echo $aylik_tekrar; ?></h2>
                            <i class="bi bi-calendar-month text-success"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card bg-white">
                            <h6>Yıllık Tekrar</h6>
                            <h2><?php echo $yillik_tekrar; ?></h2>
                            <i class="bi bi-calendar-year text-warning"></i>
                        </div>
                    </div>
                </div>

                <!-- Hızlı İşlemler ve Filtreleme Formu -->
                <div class="row">
                    <div class="col-md-8">
                        <!-- Etkinlik Listesi -->
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Etkinlikler</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 25%">Başlık</th>
                                                <th style="width: 15%">Tarih</th>
                                                <th style="width: 25%">Açıklama</th>
                                                <th style="width: 15%">Tekrar</th>
                                                <th style="width: 20%">İşlemler</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($events as $event): ?>
                                            <tr>
                                                <td class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($event['baslik']); ?>">
                                                    <?php echo htmlspecialchars($event['baslik']); ?>
                                                </td>
                                                <td><?php echo date('d.m.Y', strtotime($event['tarih'])); ?></td>
                                                <td class="text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($event['aciklama']); ?>">
                                                    <?php echo htmlspecialchars($event['aciklama']); ?>
                                                </td>
                                                <td>
                                                    <?php if($event['tekrar_aylik']): ?>
                                                        <span class="badge bg-info">Aylık</span>
                                                    <?php endif; ?>
                                                    <?php if($event['tekrar_yillik']): ?>
                                                        <span class="badge bg-warning">Yıllık</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-primary" onclick="openEventDetailModal(<?php echo $event['id']; ?>)">
                                                            <i class="bi bi-eye"></i> Detay
                                                        </button>
                                                        <button class="btn btn-danger" onclick="deleteEvent(<?php echo $event['id']; ?>, <?php echo $event['tekrar_aylik']; ?>, <?php echo $event['tekrar_yillik']; ?>)">
                                                            <i class="bi bi-trash"></i> Sil
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
                                <h5 class="card-title mb-0">Etkinlik Süz</h5>
                            </div>
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Başlık</label>
                                        <input type="text" name="baslik" class="form-control" value="<?php echo htmlspecialchars($baslik); ?>">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Başlangıç Tarihi</label>
                                        <input type="date" name="tarih_baslangic" class="form-control" value="<?php echo htmlspecialchars($tarih_baslangic); ?>">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Bitiş Tarihi</label>
                                        <input type="date" name="tarih_bitis" class="form-control" value="<?php echo htmlspecialchars($tarih_bitis); ?>">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Aylık Tekrar</label>
                                        <select name="tekrar_aylik" class="form-select">
                                            <option value="">Tümü</option>
                                            <option value="1" <?php echo $tekrar_aylik === '1' ? 'selected' : ''; ?>>Evet</option>
                                            <option value="0" <?php echo $tekrar_aylik === '0' ? 'selected' : ''; ?>>Hayır</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Yıllık Tekrar</label>
                                        <select name="tekrar_yillik" class="form-select">
                                            <option value="">Tümü</option>
                                            <option value="1" <?php echo $tekrar_yillik === '1' ? 'selected' : ''; ?>>Evet</option>
                                            <option value="0" <?php echo $tekrar_yillik === '0' ? 'selected' : ''; ?>>Hayır</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">Filtrele</button>
                                        <a href="tum_etkinlik.php" class="btn btn-secondary">Sıfırla</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Etkinlik Detay Modal -->
    <div class="modal fade" id="etkinlikEkleModal" tabindex="-1" aria-labelledby="etkinlikEkleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Etkinlik Detay</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="etkinlikEkleFrame" style="width:100%; height:400px; border:none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Etkinlik Silme Modal -->
    <div class="modal fade" id="deleteEventModal" tabindex="-1" aria-labelledby="deleteEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteEventModalLabel">Etkinlik Silme</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteEventMessage"></p>
                </div>
                <div class="modal-footer" id="deleteEventFooter">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-danger" id="btnDeleteSingle">Sadece Bu Etkinliği Sil</button>
                    <button type="button" class="btn btn-danger" id="btnDeleteAll">Tüm Tekrarları Sil</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let deleteEventModal;
        let currentEventToDelete = null;

        function openEventDetailModal(eventId) {
            const modal = new bootstrap.Modal(document.getElementById('etkinlikEkleModal'));
            const iframe = document.getElementById('etkinlikEkleFrame');
            iframe.src = `etkinlik_detay.php?etkinlik_id=${eventId}`;
            modal.show();
        }

        function deleteEvent(eventId, isMonthlyRecurring, isYearlyRecurring) {
            if (!deleteEventModal) {
                deleteEventModal = new bootstrap.Modal(document.getElementById('deleteEventModal'));
            }
            
            currentEventToDelete = { id: eventId, isRecurring: isMonthlyRecurring || isYearlyRecurring };
            
            const messageElement = document.getElementById('deleteEventMessage');
            const btnDeleteAll = document.getElementById('btnDeleteAll');
            
            if (currentEventToDelete.isRecurring) {
                messageElement.textContent = 'Bu etkinlik tekrar eden bir etkinlik. Nasıl silmek istersiniz?';
                btnDeleteAll.style.display = 'block';
            } else {
                messageElement.textContent = 'Bu etkinliği silmek istediğinizden emin misiniz?';
                btnDeleteAll.style.display = 'none';
            }
            
            deleteEventModal.show();
        }

        function performDelete(eventId, deleteType) {
            fetch('dashboard.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'delete', id: eventId, deleteType: deleteType })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    deleteEventModal.hide();
                    alert('Etkinlik başarıyla silindi.');
                    location.reload();
                } else {
                    deleteEventModal.hide();
                    alert('Etkinlik silinirken bir hata oluştu.');
                    location.reload();
                }
            })
            .catch(error => {
                deleteEventModal.hide();                
                location.reload();
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Etkinlik silme butonları için event listener'lar
            document.getElementById('btnDeleteSingle').addEventListener('click', function() {
                if (currentEventToDelete) {
                    performDelete(currentEventToDelete.id, 'sadece');
                }
            });

            document.getElementById('btnDeleteAll').addEventListener('click', function() {
                if (currentEventToDelete && currentEventToDelete.isRecurring) {
                    performDelete(currentEventToDelete.id, 'hepsi');
                }
            });

            // Etkinlik ekleme için message listener
            window.addEventListener('message', function(event) {
                if (event.data === 'eventAdded') {
                    location.reload();
                }
            });
        });
    </script>
</body>
</html>
