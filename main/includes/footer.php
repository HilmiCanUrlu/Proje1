        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/main.js"></script>
<script>
// Tarih ve saat güncelleme
function updateDateTime() {
    const now = new Date();
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    };
    document.getElementById('currentDateTime').textContent = now.toLocaleDateString('tr-TR', options);
}

// Her saniye güncelle
setInterval(updateDateTime, 1000);
updateDateTime();

// Örnek aktivite verilerini yükle
function loadActivityLog() {
    const activities = [
        { user: 'Admin', action: 'Giriş yapıldı', date: '2024-03-21 14:30', status: 'success' },
        { user: 'Manager', action: 'Rapor oluşturuldu', date: '2024-03-21 14:25', status: 'info' },
        { user: 'User1', action: 'Profil güncellendi', date: '2024-03-21 14:20', status: 'warning' }
    ];

    const tbody = document.getElementById('activityLog');
    tbody.innerHTML = activities.map(activity => `
        <tr>
            <td>${activity.user}</td>
            <td>${activity.action}</td>
            <td>${activity.date}</td>
            <td><span class="badge bg-${activity.status}">${activity.status}</span></td>
        </tr>
    `).join('');
}

// Sayısal verileri güncelle
function updateDashboardStats() {
    document.getElementById('totalUsers').textContent = '150';
    document.getElementById('activeSessions').textContent = '45';
    document.getElementById('totalTransactions').textContent = '1,234';
}

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    loadActivityLog();
    updateDashboardStats();
});
</script>
</body>
</html> 