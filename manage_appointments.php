<?php
session_start();
include 'config/db.php';

// ✅ Ensure logged in & is a doctor
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

// ✅ Handle Accept / Reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['appointment_id'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $action = $_POST['action'] === 'accept' ? 'accepted' : 'rejected';

    $update = $conn->prepare("UPDATE appointments SET status = :status WHERE id = :id AND doctor_id = :doctor_id");
    $update->bindParam(':status', $action, PDO::PARAM_STR);
    $update->bindParam(':id', $appointment_id, PDO::PARAM_INT);
    $update->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
    $update->execute();

    header("Location: manage_appointments.php?msg=updated");
    exit();
}

// ✅ Fetch all upcoming appointments for this doctor
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
      AND a.status IN ('pending','accepted','rejected')
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
    <title>Manage Appointments</title>
    <link rel="stylesheet" href="assets/css/patient_dashboard.css">
    <style>
        .btn-accept, .btn-reject {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            color: #fff;
            transition: background 0.3s, transform 0.2s;
            margin-right: 5px;
        }
        .btn-accept { background-color: #28a745; }
        .btn-accept:hover { background-color: #218838; transform: scale(1.05); }
        .btn-reject { background-color: #dc3545; }
        .btn-reject:hover { background-color: #c82333; transform: scale(1.05); }
        .status-pending { color: orange; font-weight: 600; }
        .status-accepted { color: green; font-weight: 600; }
        .status-rejected { color: red; font-weight: 600; }
        .status-unknown { color: gray; font-style: italic; }
        form { display: inline-block; }
    </style>
</head>
<body>
<header class="page-header">
    <img src="assets/images/logo.jpeg" alt="AfyaCall Logo" class="logo">
    <h1>Welcome,  <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
    <a href="doctor_dashboard.php" class="btn-primary">Back</a>
</header>

<div class="container">
    <aside class="sidebar">
        <ul>
            <li><a href="doctor_dashboard.php">Home</a></li>
            <li><a href="doctor_upcoming.php">Upcoming Appointments</a></li>
            <li><a href="doctor_history.php">Appointment History</a></li>
            <li><a href="doctor_profile.php">My Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h2>Manage Upcoming Appointments</h2>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <p class="success-msg">Appointment status updated successfully.</p>
        <?php endif; ?>

        <?php if ($appointments): ?>
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Actions</th>
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
                            $status = strtolower(trim($appt['status'] ?? 'unknown'));
                            switch ($status) {
                                case 'pending': echo "<span class='status-pending'>Pending</span>"; break;
                                case 'accepted': echo "<span class='status-accepted'>Accepted</span>"; break;
                                case 'rejected': echo "<span class='status-rejected'>Rejected</span>"; break;
                                default: echo "<span class='status-unknown'>Not Updated</span>"; break;
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($status === 'pending'): ?>
                                <form method="post">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appt['appointment_id']; ?>">
                                    <button type="submit" name="action" value="accept" class="btn-accept">Accept</button>
                                </form>
                                <form method="post">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appt['appointment_id']; ?>">
                                    <button type="submit" name="action" value="reject" class="btn-reject">Reject</button>
                                </form>
                            <?php else: ?>
                                <em>No action available</em>
                            <?php endif; ?>
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
