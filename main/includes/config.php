<?php
// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session ayarları - session başlamadan önce ayarlanmalı
if (session_status() === PHP_SESSION_NONE) {
    // Session timeout ayarları
    ini_set('session.gc_maxlifetime', 1800); // 30 dakika
    session_set_cookie_params(1800);
    session_start();
}

// Veritabanı bağlantı bilgileri
define('DB_HOST', 'localhost');
define('DB_NAME', 'buro_otomasyon');
define('DB_USER', 'root');
define('DB_PASS', '');

// Zaman dilimi ayarı
date_default_timezone_set('Europe/Istanbul'); 