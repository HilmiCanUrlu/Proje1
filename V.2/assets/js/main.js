// Ana JavaScript modülü
const App = {
    init() {
        this.bindEvents();
        this.setupAjax();
        this.initTooltips();
    },

    // Olay dinleyicileri
    bindEvents() {
        // Genel form kontrolleri
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', this.handleFormSubmit.bind(this));
        });

        // Silme işlemleri için onay
        document.querySelectorAll('[data-confirm]').forEach(element => {
            element.addEventListener('click', this.handleConfirmAction.bind(this));
        });

        // Profil modal işlemleri
        const profileModal = document.getElementById('profileModal');
        if (profileModal) {
            profileModal.addEventListener('show.bs.modal', this.handleProfileModal.bind(this));
        }

        // Şifre değiştirme formu kontrolü
        document.getElementById('changePasswordForm')?.addEventListener('submit', function(e) {
            const newPass = document.getElementById('newPassword').value;
            const confirmPass = document.getElementById('confirmPassword').value;

            if (newPass !== confirmPass) {
                e.preventDefault();
                alert('Yeni şifreler eşleşmiyor!');
            }
        });
    },

    // AJAX ayarları
    setupAjax() {
        // AJAX istekleri için varsayılan ayarlar
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            }
        });
    },

    // Bootstrap tooltip'lerini başlat
    initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    },

    // Form gönderimi işleme
    handleFormSubmit(e) {
        const form = e.target;
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    },

    // Onay gerektiren işlemler
    handleConfirmAction(e) {
        const message = e.target.getAttribute('data-confirm');
        if (!confirm(message || 'Bu işlemi yapmak istediğinizden emin misiniz?')) {
            e.preventDefault();
        }
    },

    // Profil modal işlemleri
    handleProfileModal(e) {
        const modal = e.target;
        const form = modal.querySelector('#changePasswordForm');
        
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
    },

    // Toast bildirimi gösterme
    showToast(title, message, type = 'info') {
        const toastContainer = document.querySelector('.toast-container');
        const toast = `
            <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto">${title}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toast);
        const toastElement = new bootstrap.Toast(toastContainer.lastElementChild);
        toastElement.show();
    },

    // AJAX isteği gönderme
    async fetchData(url, options = {}) {
        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Fetch error:', error);
            this.showToast('Hata', 'Veri alınırken bir hata oluştu', 'error');
            throw error;
        }
    },

    // Yetki kontrolü
    async checkPermission(action) {
        try {
            const response = await this.fetchData(`/api/check-permission?action=${action}`);
            return response.hasPermission;
        } catch (error) {
            return false;
        }
    },

    // Tarih formatla
    formatDate(date) {
        return new Date(date).toLocaleDateString('tr-TR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
};

// Sayfa yüklendiğinde başlat
document.addEventListener('DOMContentLoaded', () => App.init()); 