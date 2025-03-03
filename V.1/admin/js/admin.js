// Gelir-Gider Grafiği
const createIncomeChart = (data) => {
    const ctx = document.getElementById('incomeChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Gelir',
                data: data.income,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }, {
                label: 'Gider',
                data: data.expense,
                borderColor: 'rgb(255, 99, 132)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Aylık Gelir-Gider Grafiği'
                }
            }
        }
    });
};

// Görev Durumu Grafiği
const createTaskChart = (data) => {
    const ctx = document.getElementById('taskChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Bekleyen', 'Devam Eden', 'Tamamlanan'],
            datasets: [{
                data: [data.pending, data.in_progress, data.completed],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Görev Durumu'
                }
            }
        }
    });
};

// AJAX ile verileri yükle
const loadDashboardData = () => {
    fetch('api/dashboard-data.php')
        .then(response => response.json())
        .then(data => {
            createIncomeChart(data.financial);
            createTaskChart(data.tasks);
        })
        .catch(error => console.error('Veri yükleme hatası:', error));
};

// Sayfa yüklendiğinde grafikleri oluştur
document.addEventListener('DOMContentLoaded', loadDashboardData); 