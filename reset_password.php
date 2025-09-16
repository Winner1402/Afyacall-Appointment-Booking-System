<?php
session_start();
include 'config/db.php';
require 'vendor/autoload.php'; // PHPMailer via Composer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

// Get token from URL
if (!isset($_GET['token'])) {
    header("Location: index.php");
    exit();
}
$token = $_GET['token'];

// Verify token
$stmt = $conn->prepare("SELECT pr.user_id, pr.expires_at, u.email, u.name FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = ?");
$stmt->execute([$token]);
$resetData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resetData || strtotime($resetData['expires_at']) < time()) {
    die("<p style='color:red; text-align:center;'>Invalid or expired token.</p>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $strongPass = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/";

    if ($password !== $confirm_password) {
        $message = "<p class='error'>Passwords do not match.</p>";
    } elseif (!preg_match($strongPass, $password)) {
        $message = "<p class='error'>Password must be at least 8 characters, include uppercase, lowercase, number, and special character.</p>";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->execute([$hashed, $resetData['user_id']]);

        $stmt = $conn->prepare("DELETE FROM password_resets WHERE token=?");
        $stmt->execute([$token]);

        // Send confirmation email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'winnermlay14@gmail.com';
            $mail->Password = 'qwaw zqzc umqa jyxl';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('noreply@afyacall.com', 'AfyaCall Health Services');
            $mail->addAddress($resetData['email'], $resetData['name']);
            $mail->isHTML(true);
            $mail->Subject = 'Password Changed Successfully';
            $mail->Body = "Hello {$resetData['name']},<br><br>Your password has been changed successfully.<br>If you did not perform this action, please contact support immediately.";

            $mail->send();
        } catch (Exception $e) {
            // silently fail
        }

        // SweetAlert confirmation
        $message = "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Password Changed!',
                text: 'Your password has been updated successfully.',
                confirmButtonText: 'Login'
            }).then(() => {
                window.location.href='login.php';
            });
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password</title>
<style>
body { 
    font-family: Arial, sans-serif; 
    background: linear-gradient(135deg, #4CAF50, #7854ed);
    display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0;
}
.form-container { 
    background: #fff; padding: 25px 30px; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.2); 
    width: 100%; max-width: 400px; text-align: center; 
}
h2 { margin-bottom: 20px; color: #2E7D32; }
.input-group { position: relative; margin: 12px 0; }
.input-group input[type="password"],
.input-group input[type="text"] {
    width: 100%;
    padding: 12px 40px 12px 12px; 
    border: 1px solid #ccc;
    border-radius: 10px;
    font-size: 14px;
    box-sizing: border-box;
}
.input-group input::placeholder { font-size: 14px; color: #aaa; }
.input-group .toggle-password {
    position: absolute;
    top: 50%;
    right: 12px;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 18px;
    color: #555;
    user-select: none;
}
.password-strength {
    height: 8px;
    border-radius: 5px;
    margin-bottom: 10px;
    background: #eee;
    overflow: hidden;
}
.password-strength-bar {
    height: 100%;
    width: 0%;
    transition: width 0.3s;
}
button { 
    background: linear-gradient(90deg, #4CAF50, #7854ed); color: white; border: none; 
    padding: 12px; margin-top: 15px; width: 100%; border-radius: 10px; font-size: 16px; cursor: pointer; transition: 0.3s;
}
button:hover { background: linear-gradient(90deg, #66BB6A, #8E24AA); transform: scale(1.03); }
.message { margin-top: 10px; font-size: 14px; }
.back-link { display: block; margin-top: 15px; font-size: 14px; color: #7854ed; text-decoration: none; }
.back-link:hover { text-decoration: underline; }

@media (max-width: 480px) {
    .form-container { padding: 20px; }
    h2 { font-size: 1.5rem; }
}
</style>
</head>
<body>
<div class="form-container">
    <h2>Reset Password</h2>
    <div class="message"><?= $message ?></div>
    <form method="POST" id="resetForm">
        <div class="input-group">
            <input type="password" name="password" id="password" placeholder="New Password" required>
            <span class="toggle-password" onclick="togglePassword('password')">&#128065;</span>
        </div>
        <div class="password-strength"><div id="strengthBar" class="password-strength-bar"></div></div>

        <div class="input-group">
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
            <span class="toggle-password" onclick="togglePassword('confirm_password')">&#128065;</span>
        </div>

        <button type="submit">Update Password</button>
    </form>
    <a href="index.php" class="back-link">← Back to Login</a>
</div>

<script>
// Toggle password visibility
function togglePassword(id){
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Password strength indicator
const passwordInput = document.getElementById('password');
const strengthBar = document.getElementById('strengthBar');
passwordInput.addEventListener('input', function(){
    const val = passwordInput.value;
    let strength = 0;
    if(val.length >= 8) strength += 25;
    if(val.match(/[a-z]/)) strength += 25;
    if(val.match(/[A-Z]/)) strength += 25;
    if(val.match(/\d/) || val.match(/[\W_]/)) strength += 25;
    strengthBar.style.width = strength + '%';
    if(strength < 50) strengthBar.style.background = 'red';
    else if(strength < 75) strengthBar.style.background = 'orange';
    else strengthBar.style.background = 'green';
});
</script>
</body>
</html>
