<?php
session_start();
include 'config/db.php'; // Include database connection

if (isset($_SESSION['user_id'])) {
    try {
        // Update the latest login record with logout_time
        $stmtLog = $conn->prepare("
            UPDATE user_logs
            SET logout_time = NOW()
            WHERE user_id = :user_id
            AND logout_time IS NULL
            ORDER BY login_time DESC
            LIMIT 1
        ");
        $stmtLog->execute([
            ':user_id' => $_SESSION['user_id']
        ]);
    } catch (Exception $e) {
        error_log("Logout logging error: " . $e->getMessage());
    }
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login page with logout success message
header("Location: login.php?logout=success");
exit();
?>
