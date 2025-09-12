<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch current hashed password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');

    if (!$user) {
        echo json_encode(['status'=>'error','message'=>'User not found']);
        exit();
    }

    if (!password_verify($current_password, $user['password'])) {
        echo json_encode(['status'=>'error','message'=>'Current password is incorrect']);
        exit();
    }

    if ($new_password !== $confirm_password) {
        echo json_encode(['status'=>'error','message'=>'New passwords do not match']);
        exit();
    }

    // Update password and remove force_password_change flag
    $hashed_new = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password=:password, force_password_change=0 WHERE id=:id");
    $stmt->execute([':password'=>$hashed_new, ':id'=>$user_id]);

    echo json_encode(['status'=>'success','message'=>'Password changed successfully!']);
    exit();
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password</title>
<link rel="stylesheet" href="assets/css/patient_dashboard.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body { background: #f7f7f7; font-family: Arial, sans-serif; }
.container { max-width: 400px; margin: 80px auto; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
h2 { text-align: center; margin-bottom: 20px; color: #127137; }
.form-group { margin-bottom: 15px; }
.form-group label { font-weight: bold; display: block; margin-bottom: 5px; }
.form-group input { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; }
button { width: 100%; padding: 10px; background: #127137; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; }
button:hover { background: #0e5d2c; }
</style>
</head>
<body>
<div class="container">
    <h2>Change Password</h2>
    <form id="change-password-form">
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" name="current_password" id="current_password" required>
        </div>
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" name="new_password" id="new_password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
        </div>
        <button type="submit">Change Password</button>
    </form>
</div>

<script>
$(document).ready(function(){
    $('#change-password-form').on('submit', function(e){
        e.preventDefault();
        $.ajax({
            url: 'change_password.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(resp){
                if(resp.status === 'success'){
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: resp.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(()=> window.location.href='login.php'); // Redirect to login
                } else {
                    Swal.fire('Error', resp.message, 'error');
                }
            },
            error: function(xhr,status,error){
                Swal.fire('Error','Something went wrong: '+error,'error');
            }
        });
    });
});
</script>
</body>
</html>

