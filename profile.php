<?php
session_start();
include 'config\db.php'; // your PDO DB connection

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user info using PDO
$stmt = $conn->prepare("SELECT name, email, phone, address, gender FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Profile</title>
    <link rel="stylesheet" href="assets/css/patient_dashboard.css">
</head>
<body>

<!-- Page Header -->
<header class="page-header">
    <img src="assets/images/logo.jpeg" alt="AfyaCall Logo" class="logo">
    <h1><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Guest'; ?></h1>
   
       <a href="patient_dashboard.php" class="btn-primary">Back</a>
    
</header>

<div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <ul>
            <li><a href="patient_dashboard.php" class="active">Home</a></li>
            <li><a href="booking.php">Book Appointment</a></li>
            <li><a href="upcoming.php">Upcoming Appointments</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <!-- Main content -->
    <main class="main-content">
        <form action="update_profile_process.php" method="POST" class="profile-form">
          
        <h2 class="form-title">My Profile</h2>

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
            </div>

            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="">--Select--</option>
                    <option value="Male" <?php if($user['gender'] == "Male") echo "selected"; ?>>Male</option>
                    <option value="Female" <?php if($user['gender'] == "Female") echo "selected"; ?>>Female</option>
                    <option value="Other" <?php if($user['gender'] == "Other") echo "selected"; ?>>Other</option>
                </select>
            </div>

            <button type="submit">Update Profile</button>
        </form>
    </main>
</div>
<!-- jQuery (for AJAX) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('.profile-form').on('submit', function(e) {
        e.preventDefault(); // prevent default form submission

        $.ajax({
            type: 'POST',
            url: 'update_profile_process.php',
            data: $(this).serialize(), // send form data
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: 'Something went wrong. Please try again.'
                });
            }
        });
    });
});
</script>

</body>
</html>
