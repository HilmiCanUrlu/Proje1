<?php
class Logger {
    private $conn;
    private $table_name = "sistem_loglar";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Sistem loglarını kaydeder
     * 
     * @param int|null $personel_id Personel ID
     * @param string $islem_tipi İşlem türü (LOGIN, LOGOUT, CREATE, UPDATE, DELETE vb.)
     * @param string $islem_detay İşlem detayları
     * @return bool İşlem başarılı/başarısız
     */
    public function logKaydet($personel_id, $islem_tipi, $islem_detay) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                    (personel_id, islem_tipi, islem_detay, ip_adresi) 
                    VALUES 
                    (:personel_id, :islem_tipi, :islem_detay, :ip_adresi)";

            $stmt = $this->conn->prepare($query);

            // IP adresini al
            $ip_adresi = $this->getIPAdresi();

            // Parametreleri bağla
            $stmt->bindParam(":personel_id", $personel_id);
            $stmt->bindParam(":islem_tipi", $islem_tipi);
            $stmt->bindParam(":islem_detay", $islem_detay);
            $stmt->bindParam(":ip_adresi", $ip_adresi);

            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Log kayıt hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log kayıtlarını listeler
     * 
     * @param array $filtreler Filtreleme parametreleri
     * @param int $limit Kayıt limiti
     * @param int $offset Başlangıç noktası
     * @return array Log kayıtları
     */
    public function logListele($filtreler = [], $limit = 50, $offset = 0) {
        try {
            $where = "WHERE 1=1";
            $params = [];

            if (!empty($filtreler['personel_id'])) {
                $where .= " AND personel_id = :personel_id";
                $params[':personel_id'] = $filtreler['personel_id'];
            }

            if (!empty($filtreler['islem_tipi'])) {
                $where .= " AND islem_tipi = :islem_tipi";
                $params[':islem_tipi'] = $filtreler['islem_tipi'];
            }

            if (!empty($filtreler['baslangic_tarih'])) {
                $where .= " AND tarih >= :baslangic_tarih";
                $params[':baslangic_tarih'] = $filtreler['baslangic_tarih'];
            }

            if (!empty($filtreler['bitis_tarih'])) {
                $where .= " AND tarih <= :bitis_tarih";
                $params[':bitis_tarih'] = $filtreler['bitis_tarih'];
            }

            $query = "SELECT l.*, p.ad, p.soyad 
                     FROM " . $this->table_name . " l 
                     LEFT JOIN personel p ON l.personel_id = p.personel_id 
                     $where 
                     ORDER BY l.tarih DESC 
                     LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($query);

            // Limit ve offset parametrelerini bağla
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

            // Diğer parametreleri bağla
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            error_log("Log listeleme hatası: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Ziyaretçinin IP adresini alır
     * 
     * @return string IP adresi
     */
    private function getIPAdresi() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
} 