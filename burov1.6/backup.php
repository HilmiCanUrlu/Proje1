<?php
// backup.php
session_start();
require_once "database.php";

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['personel_id']) || $_SESSION['personel_id'] != 1) {
    $_SESSION['error_message'] = "Veritabanını yedekleme yetkiniz yok.";
    header("Location: loglar.php");
    exit;
}

// Config bilgilerini al
$dbConfig = (new Database())->getConfig();
$host     = $dbConfig['host'];
$dbName   = $dbConfig['dbname'];
$user     = $dbConfig['user'];
$password = $dbConfig['pass'];

// Yedek klasörünü hazırla
$backupDir = __DIR__ . '/database/';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Dosya adı ve yolu
$timestamp = date('Ymd_His');
$filename  = "backup_{$timestamp}.sql";
$filepath  = $backupDir . $filename;

// Ubuntu'da mysqldump komutu
$command = sprintf(
    'mysqldump --host=%s --user=%s --password=%s %s > %s 2>&1',
    escapeshellarg($host),
    escapeshellarg($user),
    escapeshellarg($password),
    escapeshellarg($dbName),
    escapeshellarg($filepath)
);

// Komutu çalıştır ve hata varsa göster
exec($command, $output, $returnVar);
if ($returnVar !== 0) {
    echo "<h5 style='color:red;'>Yedekleme sırasında bir hata oluştu (code: $returnVar):</h5>";
    echo "<pre><strong>Komut:</strong> $command\n\n<strong>Çıktı:</strong>\n"
        . htmlspecialchars(implode("\n", $output))
        . "</pre>";
    exit;
}

// Dosyayı kullanıcıya indir
header('Content-Description: File Transfer');
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
