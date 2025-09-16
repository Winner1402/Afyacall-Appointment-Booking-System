<?php
session_start();
include 'config/db.php'; 
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    // Validate email format first
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if email exists in the database
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email=?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "No account found with that email.";
        } else {
            // Email exists, generate token
            $token = bin2hex(random_bytes(16));
            $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));
            $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?,?,?)");
            $stmt->execute([$user['id'], $token, $expires_at]);

            $resetLink = "http://localhost/Afyacall/reset_password.php?token=$token";

            // Send email
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
                $mail->addAddress($email, $user['name']);
                $mail->isHTML(true);
                $mail->Subject = 'AfyaCall Password Reset';
                $mail->Body = "
                    <p>Hello {$user['name']},</p>
                    <p>Click the button below to reset your password:</p>
                    <p><a href='$resetLink' style='padding:10px 20px; background:#4CAF50; color:white; text-decoration:none; border-radius:5px;'>Reset Password</a></p>
                    <p>This link will expire in 1 hour.</p>
                    <p>If you did not request this, ignore this email.</p>
                ";
                $mail->send();
                $success = "Password reset link sent to your email.";
            } catch (Exception $e) {
                $error = "Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body { font-family: Arial; background: linear-gradient(135deg,#4CAF50,#7854ed); display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; }
.form-container { background:#fff; padding:25px 30px; border-radius:15px; box-shadow:0 8px 25px rgba(0,0,0,0.2); width:100%; max-width:400px; text-align:center; }
h2 { margin-bottom:20px; color:#2E7D32; }
input[type="email"] { width:100%; padding:12px; margin:12px 0; border:1px solid #ccc; border-radius:10px; font-size:14px; }
button { background:linear-gradient(90deg,#4CAF50,#7854ed); color:white; border:none; padding:12px; margin-top:15px; width:100%; border-radius:10px; font-size:16px; cursor:pointer; transition:0.3s; }
button:hover { background:linear-gradient(90deg,#66BB6A,#8E24AA); transform:scale(1.03); }
.back-link { display:block; margin-top:15px; font-size:14px; color:#7854ed; text-decoration:none; }
.back-link:hover { text-decoration:underline; }
</style>
</head>
<body>
<div class="form-container">
    <h2>Forgot Password</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Send Reset Link</button>
    </form>
    <a href="login.php" class="back-link">← Back to Login</a>
</div>

<?php if (isset($error)) { ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Oops!',
    text: '<?= $error ?>'
});
</script>
<?php } ?>

<?php if (isset($success)) { ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Sent!',
    text: '<?= $success ?>'
});
</script>
<?php } ?>

</body>
</html>
