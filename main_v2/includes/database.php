<?php
class Database {
    private static $instance = null;
    private $conn;
    private $statement;
    private $error;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'"
            ];

            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            logError("Database connection error: " . $this->error);
            throw new Exception("Veritabanı bağlantı hatası");
        }
    }

    // Singleton pattern
    public static function getInstance() {
        if (self::$instance === null) {
            $db = new self();
            self::$instance = $db->conn;
        }
        return self::$instance;
    }

    // Prepared statement hazırlama
    public function prepare($sql) {
        try {
            $this->statement = $this->conn->prepare($sql);
            return $this->statement;
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            logError("Query preparation error: " . $this->error);
            throw new Exception("Sorgu hazırlama hatası");
        }
    }

    // Transaction işlemleri
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    public function commit() {
        return $this->conn->commit();
    }

    public function rollback() {
        return $this->conn->rollBack();
    }

    // Son eklenen ID'yi alma
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }

    // Bağlantıyı kapatma
    public function close() {
        $this->conn = null;
    }

    // Hata mesajını alma
    public function getError() {
        return $this->error;
    }

    // Veritabanı durumunu kontrol etme
    public function checkConnection() {
        try {
            $this->conn->query('SELECT 1');
            return true;
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    // Tek satır çekme
    public function fetchOne($sql, $params = []) {
        try {
            $stmt = $this->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            logError("Query error: " . $this->error);
            throw new Exception("Veri çekme hatası");
        }
    }

    // Çoklu satır çekme
    public function fetchAll($sql, $params = []) {
        try {
            $stmt = $this->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            logError("Query error: " . $this->error);
            throw new Exception("Veri çekme hatası");
        }
    }
} 