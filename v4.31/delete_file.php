<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['personel_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if user is admin
if ($_SESSION['personel_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Only admin can delete files']);
    exit;
}

// Validate file_id
if (!isset($_POST['file_id']) || !is_numeric($_POST['file_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid file ID']);
    exit;
}

$file_id = intval($_POST['file_id']);

try {
    // Get file path from database
    $stmt = $conn->prepare("SELECT file_path FROM files WHERE file_id = ?");
    $stmt->execute([$file_id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$file) {
        echo json_encode(['success' => false, 'message' => 'File not found']);
        exit;
    }

    $file_path = $file['file_path'];

    // Delete physical file if it exists
    if (file_exists($file_path) && unlink($file_path)) {
        // Delete database record
        $stmt = $conn->prepare("DELETE FROM files WHERE file_id = ?");
        $stmt->execute([$file_id]);

        echo json_encode(['success' => true, 'message' => 'File deleted successfully']);
    } else {
        // If physical file doesn't exist, still delete database record
        $stmt = $conn->prepare("DELETE FROM files WHERE file_id = ?");
        $stmt->execute([$file_id]);

        echo json_encode(['success' => true, 'message' => 'Database record deleted successfully']);
    }

} catch (PDOException $e) {
    error_log("Database error in delete_file.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("Error in delete_file.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
} 