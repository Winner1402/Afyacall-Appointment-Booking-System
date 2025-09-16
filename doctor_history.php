<?php
session_start();
include 'config/db.php';

// ✅ Ensure doctor is logged in
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor'){
    exit("Unauthorized access");
}

$doctor_user_id = $_SESSION['user_id'];
$doctor_name = $_SESSION['user_name'];

// ✅ Get doctor_id
$stmt = $conn->prepare("SELECT id FROM doctors WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $doctor_user_id, PDO::PARAM_INT);
$stmt->execute();
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    exit("Doctor profile not found.");
}

$doctor_id = $doctor['id'];

// ✅ Fetch all past and rejected appointments
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
      AND (ds.slot_datetime < NOW() OR a.status = 'rejected')
    ORDER BY ds.slot_datetime DESC
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
<title>Appointment History | AfyaCall</title>
<link rel="stylesheet" href="assets/css/patient_dashboard.css">
<style>
    .status-accepted { color: green; font-weight: bold; }
    .status-rejected { color: red; font-weight: bold; }
    .status-pending { color: orange; font-weight: bold; }
</style>
</head>
<body>

<header class="page-header">
    <img src="assets/images/logo.jpeg" alt="AfyaCall Logo" class="logo">
    <h1>Welcome, <?php echo htmlspecialchars($doctor_name); ?></h1>
    <form action="logout.php" method="POST" style="margin:0;">
        <button type="submit" name="logout" class="logout-btn">Logout</button>
    </form>
</header>

<div class="container">
    <aside class="sidebar">
        <ul>
            <li><a href="doctor_dashboard.php">Dashboard</a></li>
            <li><a href="doctor_upcoming.php">Upcoming Appointments</a></li>
            <li><a href="doctor_history.php" class="active">Appointment History</a></li>
            <li><a href="doctor_profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <h2>Appointment History</h2>

        <?php if(count($appointments) > 0): ?>
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
                <?php foreach($appointments as $a): ?>
                <tr>
                    <td><?php echo htmlspecialchars($a['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($a['patient_email']); ?></td>
                    <td><?php echo htmlspecialchars($a['patient_phone']); ?></td>
                    <td><?php echo date('d M Y H:i', strtotime($a['slot_datetime'])); ?></td>
                    <td>
                        <?php
                        switch($a['status']){
                            case 'accepted':
                                echo '<span class="status-accepted">Accepted</span>';
                                break;
                            case 'rejected':
                                echo '<span class="status-rejected">Rejected</span>';
                                break;
                            case 'pending':
                                echo '<span class="status-pending">Pending</span>';
                                break;
                            default:
                                echo '<span>Unknown</span>';
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No past or rejected appointments found.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
