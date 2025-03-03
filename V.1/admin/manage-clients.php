<?php
class ClientManager {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function addClient($data) {
        try {
            $query = "INSERT INTO clients (name, email, phone, address, tax_number, status) 
                      VALUES (:name, :email, :phone, :address, :tax_number, :status)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":name", $data['name']);
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":phone", $data['phone']);
            $stmt->bindParam(":address", $data['address']);
            $stmt->bindParam(":tax_number", $data['tax_number']);
            $stmt->bindParam(":status", $data['status']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Client add error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getClients($search = null, $page = 1) {
        try {
            $query = "SELECT * FROM clients WHERE 1";
            if($search) {
                $query .= " AND (name LIKE :search OR phone LIKE :search 
                           OR email LIKE :search OR tax_number LIKE :search)";
            }
            $query .= " ORDER BY created_at DESC LIMIT :offset, :limit";
            
            $offset = ($page - 1) * ITEMS_PER_PAGE;
            $stmt = $this->conn->prepare($query);
            
            if($search) {
                $searchTerm = "%{$search}%";
                $stmt->bindParam(":search", $searchTerm);
            }
            
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindParam(":limit", ITEMS_PER_PAGE, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Client fetch error: " . $e->getMessage());
            return [];
        }
    }
    
    public function updateClient($id, $data) {
        try {
            $query = "UPDATE clients SET 
                      name = :name,
                      email = :email,
                      phone = :phone,
                      address = :address,
                      tax_number = :tax_number,
                      status = :status
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":name", $data['name']);
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":phone", $data['phone']);
            $stmt->bindParam(":address", $data['address']);
            $stmt->bindParam(":tax_number", $data['tax_number']);
            $stmt->bindParam(":status", $data['status']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Client update error: " . $e->getMessage());
            return false;
        }
    }
}
?> 