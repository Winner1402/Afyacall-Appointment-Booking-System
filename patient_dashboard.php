 <?php
 session_start();
include 'config/db.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Dashboard | AfyaCall</title>
    <link rel="stylesheet" href="assets\css\patient_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/themify-icons@0.1.2/css/themify-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

 <header><header class="page-header">
     <img src="assets\images\logo.jpeg" alt="AfyaCall Logo" class="logo">
    <h1>  <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Guest'; ?></h1>
    <a href="logout.php" class="logout-btn">Logout</a>
</header>
</header>
</head>
<body>
 <form action="logout.php" method="POST" style="margin:0;">
    <button type="submit" name="logout" class="logout-btn">Logout</button>
  </form>
    <!-- Sidebar -->
    <div class="sidebar">
         <ul>
            <li>
                <a href="index.php">
                    <div class="item-media"><i class="ti-home"></i></div>
                    <div class="item-inner"><span class="title">Back to Homepage </span></div>
                </a>
            </li>
            <li>
                <a href="booking.php">
                    <div class="item-media"><i class="ti-pencil-alt"></i></div>
                    <div class="item-inner"><span class="title"> Book Appointment </span></div>
                </a>
            </li>
            <li>
                <a href="appointments_history.php">
                    <div class="item-media"><i class="ti-list"></i></div>
                    <div class="item-inner"><span class="title"> Appointment History </span></div>
                </a>
            </li>
            <li>
                <a href="medical_history.php">
                    <div class="item-media"><i class="ti-medall-alt"></i></div>
                    <div class="item-inner"><span class="title"> Medical History </span></div>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main content -->
    <div class="main-content">
     <section class="cards">
    <div class="card">
        <img src="assets/icons/profile.png" alt="Profile Icon" class="card-icon">
        <h3>My Profile</h3>
        <a href="profile.php">Update Profile</a>
    </div>

    <div class="card">
        <img src="assets/icons/appointment.png" alt="Appointment Icon" class="card-icon">
        <h3>Next Appointment</h3>
        <a href="upcoming.php">View Upcoming  </a>
    </div>

    <div class="card">
        <img src="assets/icons/book.png" alt="Book Appointment Icon" class="card-icon">
        <h3>Book Appointment</h3>
        <a href="booking.php">Book Now</a>
    </div>
</section>
    </div>
</div>
<footer class="copyright-bar">
    &copy; <?php echo date("Y"); ?> AfyaCall. All Rights Reserved.
</footer>

 

<?php if (isset($_GET['logout']) && $_GET['logout'] == 'success'): ?>
<script>
  Swal.fire({
    icon: 'success',
    title: 'Logged out!',
    text: 'You have been logged out successfully.',
    timer: 2000,
    showConfirmButton: false
  });
</script>
<?php endif; ?>
</body>
</html>

