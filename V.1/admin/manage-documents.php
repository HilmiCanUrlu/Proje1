<?php
class DocumentManager {
    private $conn;
    private $uploadDir;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->uploadDir = UPLOAD_PATH . 'documents/';
    }
    
    public function addDocument($data, $file) {
        try {
            $this->conn->beginTransaction();
            
            // Benzersiz belge numarası oluştur
            $documentNumber = 'DOC-' . date('Y') . '-' . 
                             str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Dosya yükleme
            $fileName = null;
            if($file['size'] > 0) {
                $fileName = time() . '_' . basename($file['name']);
                $targetPath = $this->uploadDir . $fileName;
                
                if(!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    throw new Exception("Dosya yükleme hatası");
                }
            }
            
            $query = "INSERT INTO documents (client_id, document_number, title, 
                      description, file_path, status, created_by) 
                      VALUES (:client_id, :document_number, :title, :description, 
                      :file_path, :status, :created_by)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":client_id", $data['client_id']);
            $stmt->bindParam(":document_number", $documentNumber);
            $stmt->bindParam(":title", $data['title']);
            $stmt->bindParam(":description", $data['description']);
            $stmt->bindParam(":file_path", $fileName);
            $stmt->bindParam(":status", $data['status']);
            $stmt->bindParam(":created_by", $_SESSION['user_id']);
            
            $stmt->execute();
            $this->conn->commit();
            
            return true;
        } catch(Exception $e) {
            $this->conn->rollBack();
            error_log("Document add error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getDocuments($filters = [], $page = 1) {
        try {
            $query = "SELECT d.*, c.name as client_name, u.username as created_by_name 
                      FROM documents d 
                      LEFT JOIN clients c ON d.client_id = c.id 
                      LEFT JOIN users u ON d.created_by = u.id 
                      WHERE 1";
            
            if(!empty($filters['client_id'])) {
                $query .= " AND d.client_id = :client_id";
            }
            if(!empty($filters['status'])) {
                $query .= " AND d.status = :status";
            }
            
            $query .= " ORDER BY d.created_at DESC LIMIT :offset, :limit";
            
            $stmt = $this->conn->prepare($query);
            
            if(!empty($filters['client_id'])) {
                $stmt->bindParam(":client_id", $filters['client_id']);
            }
            if(!empty($filters['status'])) {
                $stmt->bindParam(":status", $filters['status']);
            }
            
            $offset = ($page - 1) * ITEMS_PER_PAGE;
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindParam(":limit", ITEMS_PER_PAGE, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Document fetch error: " . $e->getMessage());
            return [];
        }
    }
}
?> 