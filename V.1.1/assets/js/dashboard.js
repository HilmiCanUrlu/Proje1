$(document).ready(function() {
    // Dashboard verilerini güncelle
    function updateDashboardStats() {
        $.ajax({
            url: 'includes/get_dashboard_stats.php',
            type: 'GET',
            success: function(response) {
                $('#totalUsers').text(response.total_users);
                $('#activeSessions').text(response.active_sessions);
                $('#totalTransactions').text(response.total_transactions);
                
                // Sistem durumu güncelleme
                if(response.system_status === 'active') {
                    $('#systemStatus').text('Aktif').addClass('text-success');
                } else {
                    $('#systemStatus').text('Pasif').addClass('text-danger');
                }
            }
        });
    }

    // Aktivite loglarını güncelle
    function updateActivityLog() {
        $.ajax({
            url: 'includes/get_activity_log.php',
            type: 'GET',
            success: function(response) {
                let html = '';
                response.forEach(function(activity) {
                    html += `
                        <tr>
                            <td>${activity.username}</td>
                            <td>${activity.action}</td>
                            <td>${activity.created_at}</td>
                            <td><span class="badge bg-${activity.status_class}">${activity.status}</span></td>
                        </tr>
                    `;
                });
                $('#activityLog').html(html);
            }
        });
    }

    // Hızlı işlem butonları için izin kontrolü
    window.checkPermission = function(action) {
        $.ajax({
            url: 'includes/check_permission.php',
            type: 'POST',
            data: { action: action },
            success: function(response) {
                if(response.allowed) {
                    switch(action) {
                        case 'users':
                            window.location.href = 'users.php';
                            break;
                        case 'reports':
                            window.location.href = 'reports.php';
                            break;
                        case 'settings':
                            window.location.href = 'settings.php';
                            break;
                    }
                } else {
                    alert('Bu işlem için yetkiniz bulunmamaktadır.');
                }
            }
        });
    }

    // Sayfa yüklendiğinde ve her 30 saniyede bir verileri güncelle
    updateDashboardStats();
    updateActivityLog();
    setInterval(updateDashboardStats, 30000);
    setInterval(updateActivityLog, 30000);
}); 