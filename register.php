<?php
session_start(); // start session to access messages
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | AfyaCall</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/login.css">  
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

  <div class="center-wrapper">
    <div class="login-container">
      <h2>Register at AfyaCall</h2>
      <form action="register_process.php" method="POST">
        <!-- Full Name -->
        <div class="input-group">
          <img src="assets/icons/user.png" alt="User Icon">
          <input type="text" name="name" placeholder="Full Name" required>
        </div>

        <!-- Email -->
        <div class="input-group">
          <img src="assets/icons/email.png" alt="Email Icon">
          <input type="email" name="email" placeholder="Email" required>
        </div>

        <!-- Password -->
        <div class="input-group">
          <img src="assets/icons/lock.png" alt="Password Icon">
          <input type="password" name="password" placeholder="Password" 
         required minlength="6" maxlength="20" 
         title="Password must be between 6 and 20 characters">
        </div>

        <!-- Confirm Password -->
        <div class="input-group">
          <img src="assets/icons/lock.png" alt="Confirm Password Icon">
          <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        </div>

        <!-- Register Button -->
        <button type="submit" class="btn-primary">Register</button>
      </form>

      <!-- Login Link -->
      <div class="register-link">
        <p>Already have an account? <a href="login.php">Login here</a></p>
      </div>

    </div>
  </div>
<script>
<?php
if(isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    echo "Swal.fire({icon: 'error', title: 'Oops...', text: '$error'});";
    unset($_SESSION['error']);
}

if(isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    echo "Swal.fire({icon: 'success', title: 'Success!', text: '$success'});";
    unset($_SESSION['success']);
}
?>
</script>
</body>
</html>
