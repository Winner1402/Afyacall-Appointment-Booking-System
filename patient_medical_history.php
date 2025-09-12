<?php
include 'auth_check.php';
authorize(['patient']); // Only patients can access

include 'config/db.php';

$user_id = $_SESSION['user_id'];

// Fetch medical history added by doctors for this patient
$stmt = $conn->prepare("
    SELECT mh.id, mh.title, mh.details, mh.attachment, mh.created_at, u.name AS doctor_name
    FROM medical_history mh
    JOIN users u ON mh.doctor_id = u.id
    WHERE mh.patient_id = :patient_id
    ORDER BY mh.created_at DESC
");
$stmt->bindParam(':patient_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Medical History</title>

<!-- Optional: Your dashboard CSS -->
<link rel="stylesheet" href="assets/css/patient_dashboard.css">

<style>
 
/* Table */
.history-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    border-radius: 5px;
}

.history-table th, .history-table td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: left;
}

.history-table th {
    background-color: #127137;
    color: #fff;
    font-weight: bold;
}

.history-table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.history-table tr:hover {
    background-color: #e6f0ea;
}

/* Responsive table wrapper */
.table-wrapper {
    overflow-x: auto;
}
 
</style>
</head>
<body>

<header class="page-header">
    <img src="assets/images/logo.jpeg" alt="AfyaCall Logo" class="logo">
    <h1><?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
    <form action="logout.php" method="POST" style="margin:0;">
        <button type="submit" name="logout" class="logout-btn">Logout</button>
    </form>
</header>

<div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <ul>
            <li><a href="patient_dashboard.php">Home</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="patient_medical_history.php" class="active">Medical History</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <!-- Main content -->
    <main class="main-content">
        <h2>My Medical History</h2>

        <div class="table-wrapper">
        <?php if($history): ?>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Doctor</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Details / Attachment</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($history as $h): ?>
                <tr>
                    <td><?php echo htmlspecialchars($h['doctor_name']); ?></td>
                    <td><?php echo htmlspecialchars($h['title']); ?></td>
                    <td><?php echo date('d M Y H:i', strtotime($h['created_at'])); ?></td>
                    <td>
                        <?php echo nl2br(htmlspecialchars($h['details'])); ?>
                        <?php if($h['attachment']): ?>
                            <br><a href="uploads/<?php echo htmlspecialchars($h['attachment']); ?>" target="_blank">View File</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No medical history available yet.</p>
        <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>
