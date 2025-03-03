function checkPermission(module) {
    // Bu fonksiyon AJAX ile sunucudan yetki kontrolü yapabilir
    // Şimdilik basit bir kontrol yapalım
    fetch('check_permission.php?module=' + module)
        .then(response => response.json())
        .then(data => {
            if (!data.hasPermission) {
                showToast('Uyarı', 'Bu işlem için yetkiniz bulunmamaktadır.', 'warning');
            } else {
                window.location.href = module + '.php';
            }
        });
}

function showToast(title, message, type = 'info') {
    const toastContainer = document.querySelector('.toast-container');
    const toast = `
        <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-${type} text-white">
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.innerHTML = toast;
    const toastElement = new bootstrap.Toast(toastContainer.querySelector('.toast'));
    toastElement.show();
} 