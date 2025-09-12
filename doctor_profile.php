<?php
session_start();
include 'config/db.php';

// Ensure only doctors can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    exit("Unauthorized access");
}

$user_id = $_SESSION['user_id'];

// Fetch doctor info
$stmt = $conn->prepare("SELECT name, email, phone, gender, address FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Profile | AfyaCall</title>
    <link rel="stylesheet" href="assets\css\patient_dashboard.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- Page Header -->
<header class="page-header">
    <img src="assets/images/logo.jpeg" alt="AfyaCall Logo" class="logo">
    <h1><?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
    <a href="doctor_dashboard.php" class="btn-primary">Back</a>
</header>

<div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <ul>
            <li><a href="doctor_dashboard.php">Home</a></li>
            <li><a href="doctor_profile.php" class="active">Profile</a></li>
            <li><a href="doctor_upcoming.php">My Appointments</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <!-- Main content -->
    <main class="main-content">
        <form action="update_doctor_profile_process.php" method="POST" class="profile-form">
            <h2 class="form-title">My Profile</h2>

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($doctor['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($doctor['phone']); ?>">
            </div>

           <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($doctor['address']); ?>">
            </div>

            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="">--Select--</option>
                    <option value="Male" <?php if($doctor['gender'] == "Male") echo "selected"; ?>>Male</option>
                    <option value="Female" <?php if($doctor['gender'] == "Female") echo "selected"; ?>>Female</option>
                    <option value="Other" <?php if($doctor['gender'] == "Other") echo "selected"; ?>>Other</option>
                </select>
            </div>

            <button type="submit" class="btn-primary">Update Profile</button>
        </form>
    </main>
</div>

<script>
$(document).ready(function() {
    $('.profile-form').on('submit', function(e) {
        e.preventDefault(); // prevent default form submission

        $.ajax({
            type: 'POST',
            url: 'update_doctor_profile_process.php',
            data: $(this).serialize(),
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
