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
    </style>
</head>
<body>
    <?php
    require_once 'database.php';
    
    // Veritabanı bağlantısı
    $database = new Database();
    $conn = $database->getConnection();

    try {

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
        $stmt = $conn->query("SELECT * FROM etkinlikler ORDER BY tarih ASC LIMIT 5");
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
            $stmt = $conn->prepare("DELETE FROM etkinlikler WHERE id = ?");
            $success = $stmt->execute([$data['id']]);
            echo json_encode(['success' => $success]);
            exit;
        }
    }
    ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar py-3">
                <div class="text-center mb-4">
                    <h3 class="text-primary">LOGO</h3>
                    <div class="border-bottom border-2 mb-3"></div>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link active" href="dashboard.php"><i class="bi bi-house-door me-2"></i>Dashboard</a>
                    <a class="nav-link" href="kullanici_yonetim.php"><i class="bi bi-people me-2"></i>Kullanıcı Yönetimi</a>
                    <a class="nav-link" href="dosya_takip.php"><i class="bi bi-folder me-2"></i>Dosya Takip</a>
                    <a class="nav-link" href="musteri_takip.php"><i class="bi bi-person me-2"></i>Müşteri Takip</a>
                    <a class="nav-link" href="yeni_musteri.php"><i class="bi bi-person-plus me-2"></i>Yeni Müşteri</a>
                    <a class="nav-link" href="yeni_dosya.php"><i class="bi bi-file-plus me-2"></i>Yeni Dosya Ekle</a>
                    <a class="nav-link" href="#"><i class="bi bi-gear me-2"></i>Ayarlar</a>
                </nav>
            </div>

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
                            </div>
                            <div class="card-body">
                                <ul class="list-group">
                                    <?php foreach($upcomingEvents as $event): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>
                                                <strong><?php echo date('d.m.Y', strtotime($event['tarih'])); ?></strong>: 
                                                <?php echo htmlspecialchars($event['aciklama']); ?>
                                            </span>
                                            <button class="btn btn-danger btn-sm" onclick="deleteEvent(<?php echo $event['id']; ?>)">Sil</button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const calendar = document.getElementById('calendar');
        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();
        const today = new Date();
        
    const events = new Set([
        <?php foreach($eventDates as $date): ?>
            "<?php echo date('j-n-Y', strtotime($date)); ?>",
        <?php endforeach; ?>
    ]);



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
                        if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
                            className = "today";
                        }
                        if (events.has(dateKey)) {
                            className = "event-day";
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

        renderCalendar(currentMonth, currentYear);
        window.addEventListener('message', function(event) {
    if (event.data === 'eventAdded') {
        // Etkinlik eklendi, sayfayı yeniden yükle veya sadece takvimi güncelle
        location.reload(); // en kolay çözüm
    }
});

    function deleteEvent(eventId) {
        if (confirm('Etkinliği silmek istediğinizden emin misiniz?')) {
            fetch('dashboard.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'delete', id: eventId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Etkinlik başarıyla silindi.');
                    
                } else {
                    alert('Etkinlik silinirken bir hata oluştu.');
                }
            })
            .catch(error => console.error('Error:', error));
            location.reload();
        }
    }

    </script>
    
</body>
</html> 