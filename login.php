<?php
session_start();  
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | AfyaCall</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/login.css">
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>

  <div class="center-wrapper">
    <div class="login-container">
      <h2>Login to AfyaCall</h2>
      <form action="login_process.php" method="POST">
        <!-- Email  -->
        <div class="input-group">
          <img src="assets/icons/user.png" alt="User Icon">
          <input type="text" name="email" placeholder="Email" required>
        </div>
        <!-- Password -->
        <div class="input-group">
          <img src="assets/icons/lock.png" alt="Password Icon">
           <input type="password" name="password" placeholder="Password" 
         required minlength="6" maxlength="20" 
         title="Password must be between 6 and 20 characters">
        </div>
        <!-- Login Button -->
        <button type="submit" class="btn-primary">Login</button>
      </form>
      <!-- Register Link -->
      <div class="register-link">
        <p>Don't have an account? <a href="register.php">Register here</a></p>
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
<?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            title: "Logged Out",
            text: "You have successfully logged out.",
            icon: "success",
            confirmButtonText: "OK"
        });
    </script>
<?php endif; ?>

</body>
</html>