<?php
class FinanceManager {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function addTransaction($data) {
        try {
            $query = "INSERT INTO transactions (document_id, amount, type, 
                      description, transaction_date, created_by) 
                      VALUES (:document_id, :amount, :type, :description, 
                      :transaction_date, :created_by)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":document_id", $data['document_id']);
            $stmt->bindParam(":amount", $data['amount']);
            $stmt->bindParam(":type", $data['type']);
            $stmt->bindParam(":description", $data['description']);
            $stmt->bindParam(":transaction_date", $data['transaction_date']);
            $stmt->bindParam(":created_by", $_SESSION['user_id']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Transaction add error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getTransactionSummary($startDate = null, $endDate = null) {
        try {
            $query = "SELECT 
                      SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                      SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense,
                      COUNT(*) as total_transactions
                      FROM transactions 
                      WHERE 1";
            
            if($startDate && $endDate) {
                $query .= " AND transaction_date BETWEEN :start_date AND :end_date";
            }
            
            $stmt = $this->conn->prepare($query);
            
            if($startDate && $endDate) {
                $stmt->bindParam(":start_date", $startDate);
                $stmt->bindParam(":end_date", $endDate);
            }
            
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Transaction summary error: " . $e->getMessage());
            return null;
        }
    }
}
?> 