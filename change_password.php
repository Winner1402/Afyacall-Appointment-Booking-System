<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

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

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $new_password)) {
        echo json_encode([
            'status'=>'error',
            'message'=>'Password must be at least 8 characters long and include uppercase, lowercase, number, and special character'
        ]);
        exit();
    }

    $hashed_new = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password=:password, force_password_change=0 WHERE id=:id");
    $stmt->execute([':password'=>$hashed_new, ':id'=>$user_id]);

    echo json_encode(['status'=>'success','message'=>'Password changed successfully!']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body { background: #f5f7fa; font-family: Arial, sans-serif; }
.container { max-width: 450px; margin: 80px auto; padding: 25px; background: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
h2 { text-align: center; margin-bottom: 20px; color: #127137; }
.form-group { margin-bottom: 15px; position: relative; }
.form-group label { font-weight: bold; display: block; margin-bottom: 5px; color: #333; }
.form-group input { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; transition: border-color 0.3s; }
.form-group input:focus { border-color: #127137; outline: none; }
#password-strength { height: 8px; border-radius: 4px; margin-top: 5px; background: #ccc; transition: width 0.3s, background 0.3s; }
#password-strength-text { font-size: 12px; margin-top: 3px; font-weight: bold; }
.eye-icon { position: absolute; right: 10px; top: 38px; cursor: pointer; color: #666; }
button { width: 100%; padding: 10px; background: #127137; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; transition: background 0.3s; }
button:hover { background: #0e5d2c; }
.back-btn { display:block; margin: 15px auto 0; text-align:center; color:#127137; text-decoration:none; font-weight:bold; }
</style>
</head>
<body>
<div class="container">
    <h2>Change Password</h2>
    <form id="change-password-form">
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" name="current_password" id="current_password" required>
            <i class="fa fa-eye eye-icon" toggle="#current_password"></i>
        </div>
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" name="new_password" id="new_password" required minlength="8">
            <i class="fa fa-eye eye-icon" toggle="#new_password"></i>
            <div id="password-strength"></div>
            <div id="password-strength-text"></div>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
            <i class="fa fa-eye eye-icon" toggle="#confirm_password"></i>
        </div>
        <button type="submit">Change Password</button>
    </form>
    <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
</div>

<script>
$(document).ready(function(){
    // Eye toggle
    $('.eye-icon').on('click', function(){
        let input = $($(this).attr('toggle'));
        if(input.attr('type') === 'password'){
            input.attr('type','text');
            $(this).removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type','password');
            $(this).removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Password strength
    function checkStrength(password) {
        let strengthBar = $('#password-strength');
        let strengthText = $('#password-strength-text');
        let strength = 0;
        if(password.length >= 8) strength++;
        if(password.match(/[A-Z]/)) strength++;
        if(password.match(/[a-z]/)) strength++;
        if(password.match(/\d/)) strength++;
        if(password.match(/[\W_]/)) strength++;

        if(strength <= 2){
            strengthBar.css('background','red').css('width','33%');
            strengthText.text('Weak').css('color','red');
        } else if(strength === 3 || strength === 4){
            strengthBar.css('background','orange').css('width','66%');
            strengthText.text('Medium').css('color','orange');
        } else if(strength === 5){
            strengthBar.css('background','green').css('width','100%');
            strengthText.text('Strong').css('color','green');
        } else {
            strengthBar.css('background','#ccc').css('width','0%');
            strengthText.text('');
        }
    }

    $('#new_password').on('keyup', function(){
        checkStrength($(this).val());
    });

    // Form submission
    $('#change-password-form').on('submit', function(e){
        e.preventDefault();
        let newPass = $('#new_password').val();
        let confirmPass = $('#confirm_password').val();
        let strongRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

        if(!strongRegex.test(newPass)){
            Swal.fire('Error','Password must be at least 8 characters, include uppercase, lowercase, number, and special character','error');
            return;
        }
        if(newPass !== confirmPass){
            Swal.fire('Error','Passwords do not match','error');
            return;
        }

        $.ajax({
            url: 'change_password.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(resp){
                if(resp.status === 'success'){
                    Swal.fire({
                        icon: 'success',
                        title: 'Password Changed',
                        text: resp.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(()=> window.location.href='login.php');
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
