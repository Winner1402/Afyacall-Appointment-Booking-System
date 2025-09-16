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
/* --- Container & Layout --- */
.container { 
    display: flex; 
    flex-wrap: wrap; 
    gap: 25px; /* slightly more gap for breathing space */
    padding: 20px; 
}
.main-content { 
    flex: 1; 
    background: #fff; 
    padding: 30px; /* more padding for content */
    border-radius: 12px; 
    box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
    min-width: 300px; 
}

/* --- Sidebar --- */
.sidebar { 
    width: 220px; 
    background-color: #127137; 
    color: #fff; 
    border-radius: 10px; 
    padding: 20px; 
}
.sidebar ul { list-style: none; padding: 0; }
.sidebar ul li { margin-bottom: 15px; }
.sidebar ul li a { 
    color: #fff; 
    text-decoration: none; 
    display: block; 
    padding: 10px 14px; /* slightly larger touch area */
    border-radius: 6px; 
    transition: background 0.3s; 
}
.sidebar ul li a.active, .sidebar ul li a:hover { background-color: #0e5d2c; }
.sidebar ul li ul.dropdown { display: none; list-style: none; padding-left: 20px; }
.sidebar ul li:hover ul.dropdown { display: block; }

/* --- Stats Cards --- */
.stats-container { 
    display: flex; 
    flex-wrap: wrap; 
    gap: 25px; /* increased gap between cards */
    margin-bottom: 30px; /* more space below */
}
.stats-card { 
    flex: 1 1 160px; 
    min-width: 140px; 
    background: #127137; 
    color: #fff; 
    padding: 25px 20px; 
    border-radius: 12px; 
    text-align: center; 
    cursor: pointer; 
    transition: transform 0.3s ease, box-shadow 0.3s ease; 
}
.stats-card:hover { 
    transform: translateY(-8px); 
    box-shadow: 0 10px 20px rgba(0,0,0,0.2); 
    background: linear-gradient(135deg, #32cd6a, #127137); /* gradient on hover */

}
.stats-card h3 { 
    margin: 0; 
    font-size: clamp(1.5em, 2.5vw, 1.8em); /* responsive font size */
    font-weight: 600; 
}
.stats-card p { 
    margin: 10px 0 0; /* slightly more spacing between number and label */
    font-size: clamp(0.95em, 1.2vw, 1em); 
    font-weight: 500; 
}

/* --- Tables --- */
.table-wrapper { overflow-x:auto; margin-bottom: 35px; }
.history-table, .history-table th, .history-table td { border: 1px solid #ccc; border-collapse: collapse; padding: 12px; text-align: left; width: 100%; transition: background 0.2s; }
.history-table th { background-color: #127137; color: #fff; font-weight: 600; }
.history-table tr:nth-child(even) { background-color: #f9f9f9; }
.history-table tr:hover { background-color: #e6f0ea; }

/* --- Charts --- */
canvas { 
    width: 100% !important; 
    height: auto !important; 
    margin-bottom: 30px; 
}

/* --- Header --- */
.page-header { 
    display: flex; 
    align-items: center; 
    justify-content: space-between; 
    padding: 15px 25px; 
    background: #fff; 
    box-shadow: 0 2px 8px rgba(0,0,0,0.1); 
    border-radius: 12px; 
    margin-bottom: 25px; /* more space below header */
}
.page-header .logo { height: 50px; border-radius: 8px; }

/* --- Headings --- */
h2, h3, h4 { 
    margin-top: 30px; 
    margin-bottom: 15px; 
    line-height: 1.3; 
}

/* --- Responsive --- */
@media(max-width: 900px){
    .container { flex-direction: column; }
    .sidebar { width: 100%; display: flex; justify-content: space-around; margin-bottom: 20px; }
    .stats-container { flex-direction: column; }
    .stats-card { width: 100%; margin-bottom: 15px; }
    h2, h3, h4 { text-align: center; } /* center headings on mobile for clarity */
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
            <li><a href="admin_manage_appointments.php">All Appointments</a></li>
            <li><a href="activity_logs.php">Activity Logs</a></li>
            <li><a href="system_setup.php">System Setup</a></li>
            <li>
                <a href="#">Analytics Overview <span class="arrow">&#9662;</span></a>
                <ul class="dropdown">
                    <li><a href="reports_analytics.php">View Analytics</a></li>
                </ul>
            </li>
            <li>
    <a href="change_password.php">
        <div class="item-media"><i class="ti-key"></i></div>
        <div class="item-inner"><span class="title">Change Password</span></div>
    </a>
</li>

            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

   <main class="main-content" style="background: linear-gradient(135deg, #f4f9f7, #ffffff);">

    <h2>Admin Dashboard Overview</h2>

    <!-- Stats Cards with Icons -->
    <div class="stats-container">
        <div class="stats-card">
            <i class="fas fa-user-md" style="font-size:28px; margin-bottom:8px;"></i>
            <h3><?php echo $total_doctors; ?></h3>
            <p>Doctors</p>
        </div>
        <div class="stats-card">
            <i class="fas fa-stethoscope" style="font-size:28px; margin-bottom:8px;"></i>
            <h3><?php echo $total_specialties; ?></h3>
            <p>Specialties</p>
        </div>
        <div class="stats-card">
            <i class="fas fa-users" style="font-size:28px; margin-bottom:8px;"></i>
            <h3><?php echo $total_patients; ?></h3>
            <p>Patients</p>
        </div>
        <div class="stats-card">
            <i class="fas fa-calendar-check" style="font-size:28px; margin-bottom:8px;"></i>
            <h3><?php echo $total_appointments; ?></h3>
            <p>Appointments</p>
        </div>
    </div>

    <!-- General Insights Section -->
    <h3>System Insights & Trends</h3>
    <div class="stats-container">
        <div class="stats-card" style="flex:1 1 45%; background: linear-gradient(135deg, #127137, #1ab65f);">
            <h4>Average Appointments per Doctor</h4>
            <p><?php 
                $avg = $total_doctors ? round($total_appointments / $total_doctors, 1) : 0;
                echo $avg;
            ?></p>
            <canvas id="avgAppointmentsTrend" height="40"></canvas>
        </div>
        <div class="stats-card" style="flex:1 1 45%; background: linear-gradient(135deg, #127137, #32cd6a);">
            <h4>Patient Growth This Year</h4>
            <p><?php 
                $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role='patient' AND YEAR(created_at)=YEAR(CURDATE())");
                echo $stmt->fetchColumn();
            ?></p>
            <canvas id="patientGrowthTrend" height="40"></canvas>
        </div>
    </div>

    <div class="stats-container">
        <div class="stats-card" style="flex:1 1 30%; background: linear-gradient(135deg, #127137, #28a745);">
            <h4>Active Doctors</h4>
            <p><?php 
                $stmt = $conn->query("SELECT COUNT(*) FROM doctors WHERE status='active'");
                echo $stmt->fetchColumn();
            ?></p>
            <canvas id="activeDoctorsTrend" height="40"></canvas>
        </div>
        <div class="stats-card" style="flex:1 1 30%; background: linear-gradient(135deg, #127137, #20c997);">
            <h4>Pending Appointments</h4>
            <p><?php 
                $stmt = $conn->query("SELECT COUNT(*) FROM appointments WHERE status='pending'");
                echo $stmt->fetchColumn();
            ?></p>
            <canvas id="pendingAppointmentsTrend" height="40"></canvas>
        </div>
        <div class="stats-card" style="flex:1 1 30%; background: linear-gradient(135deg, #127137, #17a2b8);">
            <h4>Completed Appointments</h4>
            <p><?php 
                $stmt = $conn->query("SELECT COUNT(*) FROM appointments WHERE status='completed'");
                echo $stmt->fetchColumn();
            ?></p>
            <canvas id="completedAppointmentsTrend" height="40"></canvas>
        </div>
    </div>
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
            backgroundColor: '#127137',
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true }
        }
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
            fill: true,
            tension: 0.3,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: { responsive: true }
});

 // Mini Trend Charts for Insights
function createTrendChart(canvasId, dataPoints, color){
    var ctx = document.getElementById(canvasId).getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dataPoints.map((_,i)=>i+1),
            datasets: [{
                data: dataPoints,
                borderColor: color,
                backgroundColor: color+'33',
                fill: true,
                tension: 0.3,
                pointRadius: 0
            }]
        },
        options: {
            responsive:true,
            plugins: { legend: { display:false } },
            scales: { x:{ display:false }, y:{ display:false } }
        }
    });
}

// Example trends (replace with actual monthly data if available)
createTrendChart('avgAppointmentsTrend', [2,3,4,5,4,6,5,7,6,8,7,9], '#ffffff');
createTrendChart('patientGrowthTrend', [10,12,15,18,20,22,25,28,30,32,35,40], '#ffffff');
createTrendChart('activeDoctorsTrend', [5,6,5,7,6,7,8,9,10,9,11,12], '#ffffff');
createTrendChart('pendingAppointmentsTrend', [3,2,4,5,6,5,4,5,6,5,4,3], '#ffffff');
createTrendChart('completedAppointmentsTrend', [8,9,10,12,11,13,14,15,16,18,19,20], '#ffffff');
 
</script>

</body>
</html>
