<?php
// Start the session
session_start();

// Log the logout action if needed
if(isset($_SESSION['personel_id'])) {
    // You can add logging functionality here if needed
    // For example, log to database that user has logged out
    $personel_id = $_SESSION['personel_id'];
    $log_message = "Kullanıcı oturumu kapatıldı. (ID: $personel_id)";
    
    // Uncomment and modify this if you want to log to a file
    // file_put_contents('logs/logout_log.txt', date('Y-m-d H:i:s') . ' - ' . $log_message . PHP_EOL, FILE_APPEND);
}

// Unset all of the session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?> 