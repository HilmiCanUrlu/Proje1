<?php
// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session ayarları - session başlamadan önce ayarlanmalı
if (session_status() === PHP_SESSION_NONE) {
    // Session timeout ayarları
    ini_set('session.gc_maxlifetime', 1800); // 30 dakika
    session_set_cookie_params(1800);
    
    // Session güvenlik ayarları
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    
    session_start();
}

// Veritabanı bağlantı bilgileri
define('DB_HOST', 'localhost');  // MySQL sunucu adresi doğru olmalı
define('DB_NAME', 'buro_otomasyon');  // Veritabanı adı
define('DB_USER', 'root');  // MySQL kullanıcı adı
define('DB_PASS', '');  // MySQL şifre

// Uygulama sabitleri
define('APP_NAME', 'Büro Otomasyon');
define('APP_VERSION', '2.0.0');
define('APP_URL', 'http://localhost/main_v2');

// Zaman dilimi ayarı
date_default_timezone_set('Europe/Istanbul');

// Güvenlik ayarları
define('HASH_COST', 12); // Password hashing maliyeti
define('MAX_LOGIN_ATTEMPTS', 5); // Maximum login deneme sayısı
define('LOGIN_TIMEOUT', 300); // Login timeout süresi (saniye)

// Dosya yükleme ayarları
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Log ayarları
define('LOG_PATH', __DIR__ . '/../logs');

// Logs klasörünü oluştur
if (!file_exists(LOG_PATH)) {
    mkdir(LOG_PATH, 0777, true);
}

define('ERROR_LOG', LOG_PATH . '/error.log');
define('ACCESS_LOG', LOG_PATH . '/access.log');

// Log dosyalarını oluştur
if (!file_exists(ERROR_LOG)) {
    touch(ERROR_LOG);
    chmod(ERROR_LOG, 0666);
}
if (!file_exists(ACCESS_LOG)) {
    touch(ACCESS_LOG);
    chmod(ACCESS_LOG, 0666);
}

// Yardımcı fonksiyonlar
function logError($message) {
    try {
        error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, ERROR_LOG);
    } catch (Exception $e) {
        error_log("Log yazma hatası: " . $e->getMessage());
    }
}

function logAccess($message) {
    try {
        error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, ACCESS_LOG);
    } catch (Exception $e) {
        error_log("Log yazma hatası: " . $e->getMessage());
    }
}

// Genel yardımcı fonksiyonlar
function isSecure() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $_SERVER['SERVER_PORT'] == 443;
}

// CSRF token fonksiyonu
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
} 