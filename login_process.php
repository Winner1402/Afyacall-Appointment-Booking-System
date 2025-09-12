<?php
session_start();
include 'config/db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Fetch user by email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Verify password
        if (password_verify($password, $user['password'])) {

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // After successful login
$stmtLog = $conn->prepare("
    INSERT INTO user_logs (user_id, action, ip_address, user_agent, login_time, created_at)
    VALUES (:user_id, 'login', :ip_address, :user_agent, NOW(), NOW())
");
$stmtLog->execute([
    ':user_id' => $user['id'],
    ':ip_address' => $_SERVER['REMOTE_ADDR'],
    ':user_agent' => $_SERVER['HTTP_USER_AGENT']
]);

            // Check if user must change password
            if (!empty($user['force_password_change']) && $user['force_password_change'] == 1) {
                header('Location: change_password.php');
                exit();
            }

            // Normal role-based redirect
            switch ($user['role']) {
                case 'admin':
                    header('Location: admin_dashboard.php');
                    break;
                case 'doctor':
                    header('Location: doctor_dashboard.php');
                    break;
                case 'patient':
                default:
                    header('Location: patient_dashboard.php');
            }
            exit();

        } else {
            // Incorrect password
            $_SESSION['error'] = "Incorrect password!";
            header('Location: login.php');
            exit();
        }
    } else {
        // User not found
        $_SESSION['error'] = "User not found!";
        header('Location: login.php');
        exit();
    }
} else {
    // If not POST request, redirect to login page
    header('Location: login.php');
    exit();
}
?>
