<?php
session_start();
include 'config\db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];

// Fetch appointment history
$stmt = $conn->prepare("
    SELECT a.id AS appointment_id, u.name AS doctor_name, s.name AS specialty_name,
           ds.slot_datetime, a.status
    FROM appointments a
    JOIN doctor_slots ds ON a.slot_id = ds.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users u ON d.user_id = u.id
    JOIN specialties s ON d.specialty_id = s.id
    WHERE a.patient_id = :patient_id
      AND (ds.slot_datetime < NOW() OR a.status IN ('cancelled','rejected'))
    ORDER BY ds.slot_datetime DESC
");
$stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Appointment History</title>
<link rel="stylesheet" href="assets/css/patient_dashboard.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>

<header class="page-header">
    <img src="assets/images/logo.jpeg" alt="AfyaCall Logo" class="logo">
    <h1><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Guest'; ?></h1>
    <form action="logout.php" method="POST" style="margin:0;">
        <button type="submit" name="logout" class="logout-btn">Logout</button>
    </form>
</header>

<div class="container">
    <aside class="sidebar">
        <ul>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="booking.php">Book Appointment</a></li>
            <li><a href="upcoming.php">Upcoming Appointments</a></li>
            <li><a href="appointments_history.php" class="active">Appointment History</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h2>Appointment History</h2>
        <?php if(count($appointments) > 0): ?>
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>Doctor</th>
                        <th>Specialty</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($appointments as $a): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($a['doctor_name']); ?></td>
                            <td><?php echo htmlspecialchars($a['specialty_name']); ?></td>
                            <td><?php echo date('d M Y H:i', strtotime($a['slot_datetime'])); ?></td>
                            <td><?php echo ucfirst($a['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No appointment history.</p>
        <?php endif; ?>
    </main>
</div>

</body>
</html>
