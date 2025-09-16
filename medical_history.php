<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'patient';

// Fetch patients for doctor/admin to select when adding history
$patients = [];
if ($user_role == 'doctor' || $user_role == 'admin') {
    $stmt = $conn->query("SELECT id, name FROM users WHERE role='patient'");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch medical history
if ($user_role == 'patient') {
    // Patient sees only their own history
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

} elseif ($user_role == 'doctor') {
    // Doctor sees only records they added
    $stmt = $conn->prepare("
        SELECT mh.id, mh.title, mh.details, mh.attachment, mh.created_at, p.name AS patient_name
        FROM medical_history mh
        JOIN users p ON mh.patient_id = p.id
        WHERE mh.doctor_id = :doctor_id
        ORDER BY mh.created_at DESC
    ");
    $stmt->bindParam(':doctor_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    // Admin sees all
    $stmt = $conn->query("
        SELECT mh.id, mh.title, mh.details, mh.attachment, mh.created_at, u.name AS doctor_name, p.name AS patient_name
        FROM medical_history mh
        JOIN users u ON mh.doctor_id = u.id
        JOIN users p ON mh.patient_id = p.id
        ORDER BY mh.created_at DESC
    ");
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Medical History</title>
<link rel="stylesheet" href="assets/css/patient_dashboard.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .history-table { width: 80%; margin: 20px auto; border-collapse: collapse; }
    .history-table th, .history-table td { padding: 10px; border: 1px solid #ccc; text-align: left; }
    .history-table th { background-color: #127137; color: #fff; }
    .add-history { width: 80%; margin: 20px auto; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; }
    button { padding: 10px 15px; background: #127137; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
    button:hover { background: #0e5d2c; }
</style>
</head>
<body>

<header class="page-header">
    <img src="assets/images/logo.jpeg" alt="AfyaCall Logo" class="logo">
    <h1><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Guest'); ?></h1>
    <form action="logout.php" method="POST" style="margin:0;">
        <button type="submit" name="logout" class="logout-btn">Logout</button>
    </form>
</header>

<div class="container">
    <aside class="sidebar">
        <ul>
            <li><a href="doctor_profile.php">Profile</a></li>
            <li><a href="manage_appointments.php">Manage Appointment</a></li>
            <li><a href="doctor_upcoming.php">Upcoming Appointments</a></li>
            <li><a href="medical_history.php" class="active">Medical History</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h2>Medical History</h2>

        <?php if($user_role == 'doctor' || $user_role == 'admin'): ?>
        <div class="add-history">
            <h3>Add New History</h3>
            <form id="add-history-form" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="patient_id">Select Patient</label>
                    <select name="patient_id" id="patient_id" required>
                        <option value="">--Select Patient--</option>
                        <?php foreach($patients as $p): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" required>
                </div>
                <div class="form-group">
                    <label for="details">Details</label>
                    <textarea name="details" id="details" rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <label for="attachment">Attachment (optional)</label>
                    <input type="file" name="attachment" id="attachment">
                </div>
                <button type="submit">Add History</button>
            </form>
        </div>
        <?php endif; ?>

        <table class="history-table">
            <thead>
                <tr>
                    <?php if($user_role != 'patient'): ?><th>Patient</th><?php endif; ?>
                    <?php if($user_role != 'doctor'): ?><th>Doctor</th><?php endif; ?>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Details / Attachment</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($history as $h): ?>
                <tr>
                    <?php if($user_role != 'patient'): ?><td><?php echo htmlspecialchars($h['patient_name'] ?? ''); ?></td><?php endif; ?>
                    <?php if($user_role != 'doctor'): ?><td><?php echo htmlspecialchars($h['doctor_name'] ?? ''); ?></td><?php endif; ?>
                    <td><?php echo htmlspecialchars($h['title']); ?></td>
                    <td><?php echo date('d M Y H:i', strtotime($h['created_at'])); ?></td>
                    <td>
                        <?php echo nl2br(htmlspecialchars($h['details'])); ?>
                        <?php if(!empty($h['attachment'])): ?>
                            <br><a href="uploads/<?php echo htmlspecialchars($h['attachment']); ?>" target="_blank">View File</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </main>
</div>

<script>
$(document).ready(function(){
    $('#add-history-form').on('submit', function(e){
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: 'add_medical_history.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(resp){
                if(resp.status === 'success'){
                    Swal.fire('Success', resp.message, 'success').then(()=> location.reload());
                } else {
                    Swal.fire('Error', resp.message, 'error');
                }
            },
            error: function(xhr,status,error){
                Swal.fire('Error','Something went wrong: '+error,'error');
            }
        });
    });
});
</script>

</body>
</html>
