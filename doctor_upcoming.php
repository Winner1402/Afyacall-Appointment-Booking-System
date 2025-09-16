<?php
session_start();
include 'config/db.php';

// ✅ Ensure doctor is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    exit("Unauthorized access");
}

$doctor_user_id = $_SESSION['user_id'];

// ✅ Get doctor_id
$stmt = $conn->prepare("SELECT id FROM doctors WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $doctor_user_id, PDO::PARAM_INT);
$stmt->execute();
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    exit("Doctor profile not found.");
}

$doctor_id = $doctor['id'];

// ✅ Fetch upcoming appointments (pending or accepted only)
$query = "
    SELECT a.id AS appointment_id,
           u.name AS patient_name,
           u.email AS patient_email,
           u.phone AS patient_phone,
           ds.slot_datetime,
           a.status
    FROM appointments a
    JOIN doctor_slots ds ON a.slot_id = ds.id
    JOIN users u ON a.patient_id = u.id
    WHERE a.doctor_id = :doctor_id
      AND ds.slot_datetime >= NOW()
      AND a.status IN ('pending','accepted') -- only pending & accepted
    ORDER BY ds.slot_datetime ASC
";

$stmt = $conn->prepare($query);
$stmt->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Upcoming Appointments</title>
    <link rel="stylesheet" href="assets/css/patient_dashboard.css">
</head>
<body>
<header class="page-header">
    <img src="assets/images/logo.jpeg" alt="AfyaCall Logo" class="logo">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
    <a href="doctor_dashboard.php" class="btn-primary">Back</a>
</header>

<div class="container">
    <aside class="sidebar">
        <ul>
            <li><a href="doctor_dashboard.php">Home</a></li>
            <li><a href="doctor_profile.php">My Profile</a></li>
            <li><a href="doctor_upcoming.php" class="active">Upcoming Appointments</a></li>
            <li><a href="doctor_history.php">Appointment History</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h2>Upcoming Appointments</h2>

        <?php if ($appointments): ?>
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($appointments as $appt): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($appt['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($appt['patient_email']); ?></td>
                        <td><?php echo htmlspecialchars($appt['patient_phone']); ?></td>
                        <td><?php echo date("d M Y H:i", strtotime($appt['slot_datetime'])); ?></td>
                        <td>
                            <?php
                            if ($appt['status'] === 'pending') {
                                echo "<span style='color:orange;'>Pending</span>";
                            } elseif ($appt['status'] === 'accepted') {
                                echo "<span style='color:green;'>Accepted</span>";
                            } else {
                                echo htmlspecialchars($appt['status']);
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No upcoming appointments found.</p>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
