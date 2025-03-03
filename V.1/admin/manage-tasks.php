<?php
class TaskManager {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function addTask($data) {
        try {
            $query = "INSERT INTO tasks (title, description, assigned_to, 
                      due_date, priority, status, created_by) 
                      VALUES (:title, :description, :assigned_to, :due_date, 
                      :priority, :status, :created_by)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":title", $data['title']);
            $stmt->bindParam(":description", $data['description']);
            $stmt->bindParam(":assigned_to", $data['assigned_to']);
            $stmt->bindParam(":due_date", $data['due_date']);
            $stmt->bindParam(":priority", $data['priority']);
            $stmt->bindParam(":status", $data['status']);
            $stmt->bindParam(":created_by", $_SESSION['user_id']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Task add error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getTasks($userId = null, $status = null) {
        try {
            $query = "SELECT t.*, u1.username as assigned_to_name, 
                      u2.username as created_by_name 
                      FROM tasks t 
                      LEFT JOIN users u1 ON t.assigned_to = u1.id 
                      LEFT JOIN users u2 ON t.created_by = u2.id 
                      WHERE 1";
            
            if($userId) {
                $query .= " AND (t.assigned_to = :user_id OR t.created_by = :user_id)";
            }
            if($status) {
                $query .= " AND t.status = :status";
            }
            
            $query .= " ORDER BY t.due_date ASC";
            
            $stmt = $this->conn->prepare($query);
            
            if($userId) {
                $stmt->bindParam(":user_id", $userId);
            }
            if($status) {
                $stmt->bindParam(":status", $status);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Task fetch error: " . $e->getMessage());
            return [];
        }
    }
}
?> 