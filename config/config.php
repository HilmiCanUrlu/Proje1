<?php
// Temel Ayarlar
define('BASE_URL', 'http://localhost/buro-otomasyon');
define('SITE_NAME', 'Büro Otomasyon Sistemi');

// Dosya Yükleme Ayarları
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png']);
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/buro-otomasyon/uploads/');

// Oturum Ayarları
define('SESSION_TIMEOUT', 1800); // 30 dakika

// Rol Tanımları
define('ROLE_ADMIN', 'admin');
define('ROLE_MANAGER', 'manager');
define('ROLE_USER', 'user');

// Tarih Formatı
define('DATE_FORMAT', 'd.m.Y');
define('DATETIME_FORMAT', 'd.m.Y H:i');

// Sayfalama
define('ITEMS_PER_PAGE', 20);
?> 