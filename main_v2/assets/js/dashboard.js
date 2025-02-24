// Dashboard modülü
const Dashboard = {
    // Başlangıç ayarları
    init() {
        this.updateDateTime();
        this.loadStats();
        this.loadActivities();
        this.setupRefreshInterval();
        this.bindEvents();
    },

    updateDateTime() {
        const now = new Date();
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            weekday: 'long',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        };
        document.querySelector('.date-time').textContent = now.toLocaleDateString('tr-TR', options);
    },

    async loadStats() {
        try {
            const response = await fetch('stats.php');
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('totalUsers').textContent = data.data.total_users;
                document.getElementById('activeSessions').textContent = data.data.active_sessions;
                document.getElementById('totalTransactions').textContent = data.data.total_transactions;
                document.getElementById('systemStatus').textContent = data.data.system_status;
            }
        } catch (error) {
            console.error('Stats error:', error);
        }
    },

    async loadActivities() {
        try {
            const response = await fetch('activity.php');
            const data = await response.json();
            
            if (data.success) {
                const tbody = document.querySelector('.activity-table tbody');
                tbody.innerHTML = '';
                
                data.data.forEach(activity => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${activity.username}</td>
                            <td>${activity.action}</td>
                            <td>${activity.created_at}</td>
                            <td><span class="badge bg-${activity.status_class}">${activity.status}</span></td>
                        </tr>
                    `;
                });
            }
        } catch (error) {
            console.error('Activity error:', error);
        }
    },

    setupRefreshInterval() {
        // Her 30 saniyede bir güncelle
        setInterval(() => {
            this.loadStats();
            this.loadActivities();
        }, 30000);

        // Her saniye tarih/saat güncelle
        setInterval(() => this.updateDateTime(), 1000);
    },

    // Olay dinleyicileri
    bindEvents() {
        // Hızlı işlem butonları için yönlendirmeler
        document.querySelectorAll('.quick-actions button').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.dataset.action;
                switch(action) {
                    case 'newUser':
                        window.location.href = '../users/add.php';
                        break;
                    case 'createReport':
                        window.location.href = 'reports.php';
                        break;
                    case 'settings':
                        window.location.href = 'settings.php';
                        break;
                }
            });
        });
    },

    // Grafikleri başlat
    initCharts() {
        // Kullanıcı aktivitesi grafiği
        const userActivityCtx = document.getElementById('userActivityChart')?.getContext('2d');
        if (userActivityCtx) {
            new Chart(userActivityCtx, {
                type: 'line',
                data: {
                    labels: ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'],
                    datasets: [{
                        label: 'Kullanıcı Aktivitesi',
                        data: [65, 59, 80, 81, 56, 55, 40],
                        fill: false,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        // İşlem dağılımı grafiği
        const transactionDistCtx = document.getElementById('transactionDistChart')?.getContext('2d');
        if (transactionDistCtx) {
            new Chart(transactionDistCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Başarılı', 'Beklemede', 'Başarısız'],
                    datasets: [{
                        data: [70, 20, 10],
                        backgroundColor: [
                            'rgb(75, 192, 192)',
                            'rgb(255, 205, 86)',
                            'rgb(255, 99, 132)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    }
};

// Sayfa yüklendiğinde Dashboard'ı başlat
document.addEventListener('DOMContentLoaded', () => Dashboard.init()); 