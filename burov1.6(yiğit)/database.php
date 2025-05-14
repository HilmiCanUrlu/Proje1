<?php
class Database {
    private $host = "localhost";
    private $db_name = "personel_sistemi";
    private $username = "root";
    private $password = "";
    public $conn;

    // Veritabanı bağlantısını oluştur
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo "Bağlantı hatası: " . $e->getMessage();
        }
        return $this->conn;
    }

    // Config bilgilerini döndür
    public function getConfig() {
        return [
            'host'   => $this->host,
            'dbname' => $this->db_name,
            'user'   => $this->username,
            'pass'   => $this->password,
        ];
    }

}