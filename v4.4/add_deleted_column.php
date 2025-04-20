<?php
require_once 'db_connection.php';

try {
    $sql = "ALTER TABLE files ADD COLUMN is_deleted TINYINT(1) DEFAULT 0";
    $db->exec($sql);
    echo "is_deleted kolonu başarıyla eklendi.";
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?> 