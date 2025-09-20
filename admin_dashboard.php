<?php
// Auth & DB
include 'auth_check.php';
authorize(['admin']);
include 'config/db.php';

// Fetch system stats
$total_doctors = (int)$conn->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$total_specialties = (int)$conn->query("SELECT COUNT(*) FROM specialties")->fetchColumn();
$total_patients = (int)$conn->query("SELECT COUNT(*) FROM users WHERE role='patient'")->fetchColumn();
$total_appointments = (int)$conn->query("SELECT COUNT(*) FROM appointments")->fetchColumn();

// Fetch admin info
$admin_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, profile_pic FROM users WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Use uploaded image if exists, else default
$admin_profile_pic = 'assets/images/user_placeholder.png';
if (!empty($admin['profile_pic']) && file_exists($admin['profile_pic'])) {
    $admin_profile_pic = $admin['profile_pic'];
}

// Fetch other data
$doctors = $conn->query("
    SELECT d.id, u.name AS doctor_name, u.email, u.phone, s.name AS specialty_name
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    JOIN specialties s ON d.specialty_id = s.id
")->fetchAll(PDO::FETCH_ASSOC);

$patients = $conn->query("
    SELECT id, name, email, phone
    FROM users
    WHERE role='patient'
")->fetchAll(PDO::FETCH_ASSOC);

$specialties = $conn->query("SELECT * FROM specialties")->fetchAll(PDO::FETCH_ASSOC);

// Build datasets for charts
$specialtyNames = array_map(fn($s) => $s['name'], $specialties);
$specialtyCounts = [];
foreach ($specialties as $s) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM doctors WHERE specialty_id = :id");
    $stmt->execute([':id' => $s['id']]);
    $specialtyCounts[] = (int)$stmt->fetchColumn();
}

// Insights & stats calculations
$avgAppointmentsPerDoctor = $total_doctors ? round($total_appointments / $total_doctors, 1) : 0;
$patientGrowthThisYear = (int)$conn->query("SELECT COUNT(*) FROM users WHERE role='patient' AND YEAR(created_at)=YEAR(CURDATE())")->fetchColumn();
$activeDoctors = (int)$conn->query("SELECT COUNT(*) FROM doctors WHERE status='active'")->fetchColumn();
$pendingAppointments = (int)$conn->query("SELECT COUNT(*) FROM appointments WHERE status='pending'")->fetchColumn();
$completedAppointments = (int)$conn->query("SELECT COUNT(*) FROM appointments WHERE status='completed'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin Dashboard</title>
<link rel="icon" type="image/png" href="assets/images/logo.png">
<link rel="stylesheet" href="assets/css/admin_dashboard.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* Profile Dropdown Container */
.profile-dropdown {
    display: flex;
    align-items: center; /* vertically center avatar and name */
    gap: 10px; /* space between avatar and name */
    position: relative;
    cursor: pointer;
}

/* Avatar Image */
.profile-dropdown .avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #127137;
}

/* Username */
.profile-dropdown .user-name {
    font-weight: bold;
    white-space: nowrap;
    color: #127137;
}

/* Dropdown Menu */
.profile-dropdown .dropdown-menu {
    position: absolute;
    top: 100%; /* below the avatar/name */
    right: 0;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: none; /* hidden by default */
    flex-direction: column;
    min-width: 160px;
    z-index: 1000;
    overflow: hidden;
}

/* Show dropdown on hover */
.profile-dropdown:hover .dropdown-menu {
    display: flex;
}

/* Dropdown links */
.profile-dropdown .dropdown-menu a {
    padding: 10px 15px;
    text-decoration: none;
    color: #333;
    font-size: 14px;
    transition: background 0.3s, color 0.3s;
}

/* Hover effect for links */
.profile-dropdown .dropdown-menu a:hover {
    background-color: #127137;
    color: #fff;
}
</style>

</head>
<body>

<header class="page-header">
  <div class="header-left">
      <button id="sidebarToggle" class="menu-btn"><i class="fas fa-bars"></i></button>
      <img src="assets/images/logo.png" alt="Logo" class="logo"/>
  </div>
  <div class="header-right">
    <div class="profile-dropdown" id="profileDropdown">
        <img src="<?php echo htmlspecialchars($admin_profile_pic); ?>" alt="Admin Avatar" class="avatar">
        <span class="user-name"><?php echo htmlspecialchars($admin['name'] ?? 'Admin'); ?></span>
        <div class="dropdown-menu">
            <a href="admin_profile.php">Profile</a>
            <a href="system_setup.php">System Settings</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
  </div>
</header>

<aside class="sidebar" id="sidebar">
  <nav>
    <ul>
      <li><a href="admin_dashboard.php" class="active"><i class="fas fa-gauge"></i> Overview</a></li>
      <li class="has-dropdown">
        <a href="#"><i class="fas fa-users-gear"></i> Manage Users <span class="arrow">&#9662;</span></a>
        <ul class="dropdown">
          <li><a href="manage_doctors.php"><i class="fas fa-user-md"></i> Doctors</a></li>
          <li><a href="admin_manage_patients.php"><i class="fas fa-users"></i> Patients</a></li>
          <li><a href="admin_feedback.php"><i class="fas fa-comments"></i> Feedback</a></li>
        </ul>
      </li>
      <li><a href="manage_specialties.php"><i class="fas fa-stethoscope"></i> Specialties</a></li>
      <li><a href="admin_manage_appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a></li>
      <li><a href="activity_logs.php"><i class="fas fa-list-check"></i> Activity Logs</a></li>
      <li><a href="system_setup.php"><i class="fas fa-gears"></i> System Setup</a></li>
      <li class="has-dropdown">
        <a href="#"><i class="fas fa-chart-pie"></i> Analytics <span class="arrow">&#9662;</span></a>
        <ul class="dropdown">
          <li><a href="reports_analytics.php"><i class="fas fa-chart-line"></i> View Analytics</a></li>
        </ul>
      </li>
      <li><a href="change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
    </ul>
  </nav>
</aside>

<div class="overlay" id="overlay"></div>

<main class="container">
    <div class="overview-section">
        <h3>Welcome to the Admin Dashboard</h3>
        <p>
            Here you can monitor the hospital's activities, manage doctors and patients, track appointments, 
            and get key insights into specialties and bookings. Use the dashboard below to explore detailed statistics and trends.
        </p>
    </div>

  <section class="stats-container">
    <article class="stats-card"><i class="fas fa-user-md stat-icon"></i><h3><?= $total_doctors ?></h3><p>Doctors</p></article>
    <article class="stats-card"><i class="fas fa-stethoscope stat-icon"></i><h3><?= $total_specialties ?></h3><p>Specialties</p></article>
    <article class="stats-card"><i class="fas fa-users stat-icon"></i><h3><?= $total_patients ?></h3><p>Patients</p></article>
    <article class="stats-card"><i class="fas fa-calendar-check stat-icon"></i><h3><?= $total_appointments ?></h3><p>Appointments</p></article>
  </section>

  <section class="insights">
    <article class="insight-card"><h4>Average Appointments per Doctor</h4><p class="insight-value"><?= $avgAppointmentsPerDoctor ?></p><canvas id="avgAppointmentsTrend" height="40"></canvas></article>
    <article class="insight-card"><h4>Patient Growth This Year</h4><p class="insight-value"><?= $patientGrowthThisYear ?></p><canvas id="patientGrowthTrend" height="40"></canvas></article>
  </section>

  <section class="insights three-col">
    <article class="insight-card"><h4>Active Doctors</h4><p class="insight-value"><?= $activeDoctors ?></p><canvas id="activeDoctorsTrend" height="40"></canvas></article>
    <article class="insight-card"><h4>Pending Appointments</h4><p class="insight-value"><?= $pendingAppointments ?></p><canvas id="pendingAppointmentsTrend" height="40"></canvas></article>
    <article class="insight-card"><h4>Completed Appointments</h4><p class="insight-value"><?= $completedAppointments ?></p><canvas id="completedAppointmentsTrend" height="40"></canvas></article>
  </section>

  <section class="charts">
    <h3>Doctors per Specialty</h3><canvas id="doctorSpecialtyChart"></canvas>
    <h3>Appointments per Month</h3><canvas id="appointmentsChart"></canvas>
  </section>

  <section class="tables">
    <h3>Doctors</h3>
    <div class="table-wrapper"><table class="history-table"><thead><tr><th>ID</th><th>Doctor</th><th>Email</th><th>Phone</th><th>Specialty</th></tr></thead><tbody>
    <?php if($doctors): foreach($doctors as $d): ?>
    <tr><td><?= (int)$d['id'] ?></td><td><?= htmlspecialchars($d['doctor_name']) ?></td><td><?= htmlspecialchars($d['email']) ?></td><td><?= htmlspecialchars($d['phone']) ?></td><td><?= htmlspecialchars($d['specialty_name']) ?></td></tr>
    <?php endforeach; else: ?><tr><td colspan="5">No doctors found.</td></tr><?php endif; ?>
    </tbody></table></div>
  </section>

  <section class="tables">
    <h3>Patients</h3>
    <div class="table-wrapper"><table class="history-table"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th></tr></thead><tbody>
    <?php if($patients): foreach($patients as $p): ?>
    <tr><td><?= (int)$p['id'] ?></td><td><?= htmlspecialchars($p['name']) ?></td><td><?= htmlspecialchars($p['email']) ?></td><td><?= htmlspecialchars($p['phone']) ?></td></tr>
    <?php endforeach; else: ?><tr><td colspan="4">No patients found.</td></tr><?php endif; ?>
    </tbody></table></div>
  </section>

  <section class="tables">
    <h3>Specialties</h3>
    <div class="table-wrapper"><table class="history-table"><thead><tr><th>ID</th><th>Specialty</th><th>Description</th></tr></thead><tbody>
    <?php if($specialties): foreach($specialties as $s): ?>
    <tr><td><?= (int)$s['id'] ?></td><td><?= htmlspecialchars($s['name']) ?></td><td><?= htmlspecialchars($s['description'] ?? '') ?></td></tr>
    <?php endforeach; else: ?><tr><td colspan="3">No specialties found.</td></tr><?php endif; ?>
    </tbody></table></div>
  </section>
</main>

<script>
 // Toggle dropdown on click
document.addEventListener('DOMContentLoaded', () => {
    const dropdown = document.querySelector('.profile-dropdown');
    const menu = dropdown.querySelector('.dropdown-menu');

    dropdown.addEventListener('click', (e) => {
        e.stopPropagation(); // prevent closing immediately
        menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', () => {
        menu.style.display = 'none';
    });
});
// Dashboard chart data
window.dashboardData = {
  specialtyLabels: <?= json_encode(array_values($specialtyNames)) ?>,
  specialtyCounts: <?= json_encode(array_values($specialtyCounts)) ?>,
  monthlyAppointments: <?= json_encode(array_values($monthlyAppointments ?? [])) ?>,
  trends: {
    avgAppointmentsTrend: [2,3,4,5,4,6,5,7,6,8,7,9],
    patientGrowthTrend: [10,12,15,18,20,22,25,28,30,32,35,40],
    activeDoctorsTrend: [5,6,5,7,6,7,8,9,10,9,11,12],
    pendingAppointmentsTrend: [3,2,4,5,6,5,4,5,6,5,4,3],
    completedAppointmentsTrend: [8,9,10,12,11,13,14,15,16,18,19,20]
  }
};
</script>
<script src="assets/js/admin_dashboard.js"></script>
</body>
</html>
