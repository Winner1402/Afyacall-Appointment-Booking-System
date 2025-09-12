<?php
include 'auth_check.php';
authorize(['admin']); // Only admin can access
include 'config/db.php';

// Fetch system stats
$total_doctors = $conn->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$total_specialties = $conn->query("SELECT COUNT(*) FROM specialties")->fetchColumn();
$total_patients = $conn->query("SELECT COUNT(*) FROM users WHERE role='patient'")->fetchColumn();
$total_appointments = $conn->query("SELECT COUNT(*) FROM appointments")->fetchColumn();

// Fetch doctors for management table
$doctors = $conn->query("
    SELECT d.id, u.name AS doctor_name, u.email, u.phone, s.name AS specialty_name
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    JOIN specialties s ON d.specialty_id = s.id
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch patients for management table
$patients = $conn->query("
    SELECT id, name, email, phone
    FROM users
    WHERE role='patient'
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch specialties for management table
$specialties = $conn->query("SELECT * FROM specialties")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="assets/css/patient_dashboard.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.container { display: flex; gap: 20px; padding: 20px; }
.sidebar { width: 220px; background-color: #127137; color: #fff; border-radius: 10px; padding: 20px; }
.sidebar ul { list-style: none; padding: 0; }
.sidebar ul li { margin-bottom: 15px; }
.sidebar ul li a { color: #fff; text-decoration: none; display: block; padding: 8px 10px; border-radius: 6px; }
.sidebar ul li a.active, .sidebar ul li a:hover { background-color: #0e5d2c; }
.sidebar ul li ul.dropdown { display: none; list-style: none; padding-left: 15px; }
.sidebar ul li:hover ul.dropdown { display: block; }
.main-content { flex: 1; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }

/* Stats cards */
.stats-container { display: flex; gap: 20px; margin-bottom: 20px; }
.stats-card { flex: 1; background: #127137; color: #fff; padding: 20px; border-radius: 10px; text-align: center; }
.stats-card h3 { margin: 0; font-size: 1.5em; }
.stats-card p { margin: 5px 0 0; font-size: 1em; }

/* Table */
.table-wrapper { overflow-x:auto; margin-bottom: 30px; }
.history-table, .history-table th, .history-table td { border: 1px solid #ccc; border-collapse: collapse; padding: 10px; text-align: left; width: 100%; }
.history-table th { background-color: #127137; color: #fff; }
.history-table tr:nth-child(even) { background-color: #f9f9f9; }
.history-table tr:hover { background-color: #e6f0ea; }
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
    <aside class="sidebar">
        <ul>
            <li><a href="admin_dashboard.php" class="active">Overview</a></li>
            <li>
                <a href="#">Manage Users <span class="arrow">&#9662;</span></a>
                <ul class="dropdown">
                    <li><a href="manage_doctors.php">Manage Doctors</a></li>
                    <li><a href="admin_manage_patients.php">Manage Patients</a></li>
                </ul>
            </li>
            <li><a href="manage_specialties.php">Manage Specialties</a></li>
            <li><a href="admin_manage_appointments.php">Manage Appointments</a></li>
            <li><a href="activity_logs.php">Activity Logs</a></li>
            <li><a href="system_setup.php">System Setup</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h2>Admin Dashboard Overview</h2>

        <!-- Stats -->
        <div class="stats-container">
            <div class="stats-card">
                <h3><?php echo $total_doctors; ?></h3>
                <p>Doctors</p>
            </div>
            <div class="stats-card">
                <h3><?php echo $total_specialties; ?></h3>
                <p>Specialties</p>
            </div>
            <div class="stats-card">
                <h3><?php echo $total_patients; ?></h3>
                <p>Patients</p>
            </div>
            <div class="stats-card">
                <h3><?php echo $total_appointments; ?></h3>
                <p>Appointments</p>
            </div>
        </div>

        <!-- Charts -->
        <h3>Analytics</h3>
        <canvas id="doctorSpecialtyChart" width="400" height="150"></canvas>
        <canvas id="appointmentsChart" width="400" height="150" style="margin-top:20px;"></canvas>
    </main>
</div>

<script>
// Doctors per Specialty Chart
var ctx = document.getElementById('doctorSpecialtyChart').getContext('2d');
var doctorSpecialtyChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [<?php 
            $specialtyNames = array_column($specialties, 'name');
            echo "'" . implode("','", $specialtyNames) . "'";
        ?>],
        datasets: [{
            label: 'Doctors per Specialty',
            data: [<?php
                $counts = [];
                foreach($specialties as $s){
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM doctors WHERE specialty_id = :id");
                    $stmt->execute([':id'=>$s['id']]);
                    $counts[] = $stmt->fetchColumn();
                }
                echo implode(",", $counts);
            ?>],
            backgroundColor: '#127137'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } }
    }
});

// Appointments per Month Chart
var ctx2 = document.getElementById('appointmentsChart').getContext('2d');
var appointmentsChart = new Chart(ctx2, {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
            label: 'Appointments per Month',
            data: [
                <?php
                for($m=1;$m<=12;$m++){
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE MONTH(slot_datetime) = :m");
                    $stmt->execute([':m'=>$m]);
                    echo $stmt->fetchColumn() . ($m<12 ? ',' : '');
                }
                ?>
            ],
            backgroundColor: 'rgba(18,113,55,0.2)',
            borderColor: '#127137',
            borderWidth: 2,
            fill: true
        }]
    },
    options: { responsive: true }
});
</script>

</body>
</html>
