<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
        
        .quick-actions .btn {
            width: 100%;
            margin-bottom: 0.5rem;
            text-align: left;
        }
        
        .calendar {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            overflow-x: auto;
        }
        .calendar table {
            width: 100%;
            table-layout: fixed;
        }
        .calendar th, .calendar td {
            text-align: center;
            padding: 8px;
            word-wrap: break-word;
            cursor: pointer;
            font-size: 12px;
        }
        .calendar .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .calendar .header button {
            background-color: #0d6efd;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .calendar .header button:hover {
            background-color: #0056b3;
        }
        .calendar .today {
            background-color: #007bff;
            color: white;
        }
        .calendar .event-day {
            background-color: #28a745;
            color: white;
        }
        .calendar .today-event {
            background-color: #ffc107;
            color: white;
        }
        .calendar.past-day {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <?php
    // Oturum başlat
    session_start();
    
    // Oturum kontrolü
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("Location: login.php");
        exit;
    }
    
    require_once 'database.php';
    
    // Veritabanı bağlantısı
    $database = new Database();
    $conn = $database->getConnection();

    try {
        // Kullanıcı bilgilerini al
        if (isset($_SESSION['personel_id'])) {
            $query = "SELECT ad, soyad, kullanici_adi FROM personel WHERE personel_id = :personel_id";
            $stmt = $conn->prepare($query);
            $stmt->execute([':personel_id' => $_SESSION['personel_id']]);
            $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($kullanici && isset($kullanici['kullanici_adi'])) {
                $_SESSION['kullanici_adi'] = $kullanici['kullanici_adi'];
                $_SESSION['ad'] = $kullanici['ad'];
                $_SESSION['soyad'] = $kullanici['soyad'];
            }
        }

        // Etkinlik tarihlerini al (örnek tablo adı: etkinlikler)
        $stmt = $conn->query("SELECT tarih FROM etkinlikler");
        $eventDates = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Toplam dosya sayısı
        $stmt = $conn->query("SELECT COUNT(*) as total FROM dosyalar");
        $totalFiles = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Aktif dosya sayısı (durumu 'Tamamlandı' olmayanlar)
        $stmt = $conn->query("SELECT COUNT(*) as active FROM dosyalar WHERE dosya_durumu != 'Tamamlandı'");
        $activeFiles = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

        // Toplam müşteri sayısı
        $stmt = $conn->query("SELECT COUNT(*) as total FROM musteriler");
        $totalCustomers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Toplam personel sayısı
        $stmt = $conn->query("SELECT COUNT(*) as total FROM personel");
        $totalStaff = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Son aktiviteleri çek
        $stmt = $conn->query("SELECT sl.*, p.ad, p.soyad 
                             FROM sistem_loglar sl 
                             LEFT JOIN personel p ON sl.personel_id = p.personel_id 
                             ORDER BY sl.tarih DESC 
                             LIMIT 10");
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch upcoming events ordered by date
        $stmt = $conn->query("SELECT id,baslik, tarih, aciklama, tekrar_aylik, tekrar_yillik FROM etkinlikler ORDER BY tarih ASC LIMIT 5");
        $upcomingEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch(PDOException $e) {
        echo "Veritabanı hatası: " . $e->getMessage();
        $totalFiles = $activeFiles = $totalCustomers = $totalStaff = 0;
        $activities = [];
        $upcomingEvents = [];
    }
    ?>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data['action'] === 'delete') {
            try {
                if ($data['deleteType'] === 'hepsi') {
                    $stmt = $conn->prepare("DELETE FROM etkinlikler WHERE recurrence_id = (SELECT recurrence_id FROM etkinlikler WHERE id = ?)");
                } else {
                    $stmt = $conn->prepare("DELETE FROM etkinlikler WHERE id = ?");
                }
                $stmt->execute([$data['id']]);
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            } catch(PDOException $e) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
        }
    }
    ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-10 py-3">
                <h2 class="mb-4">Dashboard</h2>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card bg-white">
                            <h6>Toplam Dosya</h6>
                            <h2><?php echo $totalFiles; ?></h2>
                            <i class="bi bi-folder text-primary"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-white">
                            <h6>Aktif Dosya</h6>
                            <h2><?php echo $activeFiles; ?></h2>
                            <i class="bi bi-clock-history text-success"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-white">
                            <h6>Müşteriler</h6>
                            <h2><?php echo $totalCustomers; ?></h2>
                            <i class="bi bi-people text-warning"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-white">
                            <h6>Personel</h6>
                            <h2><?php echo $totalStaff; ?></h2>
                            <i class="bi bi-person-badge text-info"></i>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities & Quick Actions -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Yaklaşan Etkinlikler</h5>
                                <a href="tum_etkinlik.php" class="btn btn-primary btn-sm">Tümünü Göster</a>
                            </div>
                            <div class="card-body">
                                <ul class="list-group">
                                    <?php foreach($upcomingEvents as $event): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>
                                                <?php echo htmlspecialchars($event['baslik']); ?>
                                                <strong><a href="#" onclick="openEventDetailModal(<?php echo $event['id']; ?>)"><?php echo date('d.m.Y', strtotime($event['tarih'])); ?></a></strong>: 
                                                <?php echo htmlspecialchars($event['aciklama']); ?>
                                            </span>
                                            <button class="btn btn-danger btn-sm" onclick="deleteEvent(<?php echo $event['id']; ?>, <?php echo $event['tekrar_aylik']; ?>, <?php echo $event['tekrar_yillik']; ?>)">Sil</button>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Son Aktiviteler</h5>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>KULLANICI</th>
                                            <th>İŞLEM</th>
                                            <th>TARİH</th>
                                            <th>DURUM</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($activities as $activity): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($activity['ad'] . ' ' . $activity['soyad']); ?></td>
                                            <td><?php echo htmlspecialchars($activity['islem_tipi']); ?></td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($activity['tarih'])); ?></td>
                                            <td>
                                                <span class="badge bg-success">Başarılı</span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Takvim</h5>
                            </div>
                            <div class="card-body">
                                <div id="calendar" class="calendar"></div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Hızlı İşlemler</h5>
                            </div>
                            <div class="card-body quick-actions">
                                <a href="yeni_musteri.php" class="btn btn-primary mb-2 w-100">
                                    <i class="bi bi-person-plus me-2"></i>Yeni Müşteri
                                </a>
                                <a href="yeni_dosya.php" class="btn btn-success mb-2 w-100">
                                    <i class="bi bi-file-earmark-text me-2"></i>Yeni Dosya
                                </a>
                                <a href="kullanici_yonetim.php" class="btn btn-info text-white w-100">
                                    <i class="bi bi-gear me-2"></i>Kullanıcı Yönetimi
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Etkinlik Ekle Modal -->
    <div class="modal fade" id="etkinlikEkleModal" tabindex="-1" aria-labelledby="etkinlikEkleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Etkinlik Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="etkinlikEkleFrame" style="width:100%; height:400px; border:none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Yeni Dosya Ekle Modal -->
    <div class="modal fade" id="yeniDosyaModal" tabindex="-1" aria-labelledby="yeniDosyaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="yeniDosyaModalLabel">Yeni Dosya Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe src="yeni_dosya.php" style="width: 100%; height: 500px; border: none;"></iframe>
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
        // Takvim ve etkinlik silme için gerekli değişkenler
        const calendar = document.getElementById('calendar');
        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();
        const today = new Date();
        let deleteEventModal;
        let currentEventToDelete = null;

        // Etkinlik tarihlerini set olarak tanımla
        const events = new Set([
            <?php foreach($eventDates as $date): ?>
                "<?php echo date('j-n-Y', strtotime($date)); ?>",
            <?php endforeach; ?>
        ]);

        // Takvim fonksiyonları
        function renderCalendar(month, year) {
            const monthNames = ["Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"];
            const firstDay = new Date(year, month).getDay();
            const daysInMonth = 32 - new Date(year, month, 32).getDate();

            let table = `<div class="header">
                            <button onclick="changeMonth(-1)">&#8249;</button>
                            <h3>${monthNames[month]} ${year}</h3>
                            <button onclick="changeMonth(1)">&#8250;</button>
                         </div>
                         <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Pazartesi</th>
                                    <th>Salı</th>
                                    <th>Çarşamba</th>
                                    <th>Perşembe</th>
                                    <th>Cuma</th>
                                    <th>Cumartesi</th>
                                    <th>Pazar</th>
                                </tr>
                            </thead>
                            <tbody><tr>`;

            let day = 1;
            for (let i = 0; i < 6; i++) {
                for (let j = 0; j < 7; j++) {
                    if (i === 0 && j < firstDay) {
                        table += "<td></td>";
                    } else if (day > daysInMonth) {
                        break;
                    } else {
                        let className = "";
                        const dateKey = `${day}-${month + 1}-${year}`;
                        if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate() && events.has(dateKey)) {
                            className = "today-event"; // Yellow
                        } else if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
                            className = "today"; // Blue
                        } else if (events.has(dateKey)) {
                            className = "event-day"; // Green
                        } else if (new Date(year, month, day) < today) {
                            className = "past-day"; // Grey
                        }
                        table += `<td class="${className}" onclick="addEvent(${day}, ${month}, ${year})">${day}</td>`;
                        day++;
                    }
                }
                table += "</tr><tr>";
            }
            table += "</tr></tbody></table>";
            calendar.innerHTML = table;
        }

        function changeMonth(step) {
            currentMonth += step;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            } else if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            renderCalendar(currentMonth, currentYear);
        }

        function addEvent(day, month, year) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const modal = new bootstrap.Modal(document.getElementById('etkinlikEkleModal'));
            const iframe = document.getElementById('etkinlikEkleFrame');
            iframe.src = `etkinlik_ekle.php?tarih=${dateStr}`;
            modal.show();
        }

        // Etkinlik silme fonksiyonları
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

        function openEventDetailModal(eventId) {
            const modal = new bootstrap.Modal(document.getElementById('etkinlikEkleModal'));
            const iframe = document.getElementById('etkinlikEkleFrame');
            iframe.src = `etkinlik_detay.php?etkinlik_id=${eventId}`;
            modal.show();
        }

        // Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Takvimi başlangıçta render et
            renderCalendar(currentMonth, currentYear);

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