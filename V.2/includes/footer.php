            </main>
        </div>
    </div>

    <!-- Profil Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileModalLabel">Profil Bilgileri</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="user-info">
                        <p><strong>Kullanıcı Adı:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <p><strong>Rol:</strong> <?php echo htmlspecialchars($_SESSION['role']); ?></p>
                        <p><strong>Son Giriş:</strong> <?php echo $_SESSION['last_login'] ?? 'Bilgi yok'; ?></p>
                    </div>
                    <hr>
                    <form id="changePasswordForm" method="POST" action="<?php echo APP_URL; ?>/app/users/change_password.php">
                        <h6>Şifre Değiştir</h6>
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Mevcut Şifre</label>
                            <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">Yeni Şifre</label>
                            <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Yeni Şifre (Tekrar)</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Şifre Değiştir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
    
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo APP_URL . $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

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

    // Şifre değiştirme formu kontrolü
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        const newPass = document.getElementById('newPassword').value;
        const confirmPass = document.getElementById('confirmPassword').value;
        
        if (newPass !== confirmPass) {
            e.preventDefault();
            alert('Yeni şifreler eşleşmiyor!');
        }
    });
    </script>
</body>
</html> 