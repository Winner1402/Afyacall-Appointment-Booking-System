<?php
include 'auth_check.php';
authorize(['admin']);
include 'config/db.php';

// --- Stats ---
$total_appointments = $conn->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$total_doctors = $conn->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$total_patients = $conn->query("SELECT COUNT(*) FROM users WHERE role='patient'")->fetchColumn();
$total_specialties = $conn->query("SELECT COUNT(*) FROM specialties")->fetchColumn();

// Appointments per doctor
$doctors = $conn->query("
    SELECT u.name AS doctor_name, COUNT(a.id) AS total_appointments
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    LEFT JOIN appointments a ON a.doctor_id = d.id
    GROUP BY d.id
")->fetchAll(PDO::FETCH_ASSOC);

// Appointments per specialty
$specialties = $conn->query("
    SELECT s.name AS specialty_name, COUNT(a.id) AS total_requests
    FROM specialties s
    LEFT JOIN doctors d ON d.specialty_id = s.id
    LEFT JOIN appointments a ON a.doctor_id = d.id
    GROUP BY s.id
")->fetchAll(PDO::FETCH_ASSOC);

// Daily bookings last 30 days
$bookings_daily = $conn->query("
    SELECT DATE(ds.slot_datetime) AS day, COUNT(a.id) AS total_bookings
    FROM appointments a
    JOIN doctor_slots ds ON a.slot_id = ds.id
    WHERE ds.slot_datetime >= CURDATE() - INTERVAL 30 DAY
    GROUP BY DATE(ds.slot_datetime)
")->fetchAll(PDO::FETCH_ASSOC);

// Weekly bookings last 12 weeks
$bookings_weekly = $conn->query("
    SELECT CONCAT('Week ', WEEK(ds.slot_datetime, 1)) AS week, COUNT(a.id) AS total_bookings
    FROM appointments a
    JOIN doctor_slots ds ON a.slot_id = ds.id
    WHERE ds.slot_datetime >= CURDATE() - INTERVAL 12 WEEK
    GROUP BY WEEK(ds.slot_datetime, 1)
")->fetchAll(PDO::FETCH_ASSOC);

// Monthly bookings last 12 months
$bookings_monthly = $conn->query("
    SELECT DATE_FORMAT(ds.slot_datetime,'%b %Y') AS month, COUNT(a.id) AS total_bookings
    FROM appointments a
    JOIN doctor_slots ds ON a.slot_id = ds.id
    WHERE ds.slot_datetime >= CURDATE() - INTERVAL 12 MONTH
    GROUP BY DATE_FORMAT(ds.slot_datetime,'%Y-%m')
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports & Analytics</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    background: linear-gradient(to bottom right, #f9fdf9, #ffffff);
}

header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(to right, #f0fff0, #ffffff);
    color: #127137;
    padding: 15px 20px;
    position: sticky;
    top: 0;
    z-index: 1000;
}
header img { height: 40px; }
header h1 { font-size: 1.2em; margin: 0; color: #127137; }
header form button { background: #127137; border: none; padding: 8px 12px; color: #fff; border-radius: 6px; cursor: pointer; transition: 0.3s; }
header form button:hover { background: #0e5d2c; }

/* Sidebar */
.sidebar {
    width: 220px;
    background: linear-gradient(to bottom, #f9fdf9, #e6f7e6);
    color: #127137;
    position: fixed;
    top: 60px;
    bottom: 0;
    left: 0;
    overflow-y: auto;
    padding: 20px;
    transition: transform 0.3s ease;
    z-index: 1000;
}
.sidebar.closed { transform: translateX(-100%); }
.sidebar ul { list-style: none; padding: 0; margin: 0; }
.sidebar ul li { margin-bottom: 10px; }
.sidebar ul li a { color: #127137; text-decoration: none; display: block; padding: 8px 10px; border-radius: 6px; transition: 0.3s; }
.sidebar ul li a.active, .sidebar ul li a:hover { background-color: rgba(18, 113, 55, 0.1); }
.sidebar ul li ul.dropdown { display: none; padding-left: 15px; }
.sidebar ul li:hover ul.dropdown { display: block; }

/* Toggle button for mobile */
#toggleSidebar {
    display: none;
    position: fixed;
    top: 15px;
    left: 15px;
    background: #127137;
    color: #fff;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    z-index: 1100;
}

/* Main content */
.main-content {
    margin-left: 240px;
    padding: 25px;
    transition: margin-left 0.3s ease;
}

/* Stats cards */
.stats-container { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 25px; }
.stats-card { flex: 1 1 150px; min-width: 120px; background: linear-gradient(to bottom right,#f0fff0,#ffffff); color: #127137; padding: 20px; border-radius: 12px; text-align: center; transition: transform 0.3s, box-shadow 0.3s; }
.stats-card:hover { transform: translateY(-5px); box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
.stats-card h3 { margin: 0; font-size: 1.6em; }
.stats-card p { margin: 8px 0 0; font-size: 1em; }

/* Charts */
.chart-container { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px; }
.chart-box { flex: 1 1 300px; background: linear-gradient(to bottom right,#f9fdf9,#ffffff); padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: transform 0.3s; }
.chart-box:hover { transform: translateY(-3px); }
.chart-box h3 { margin-bottom: 15px; font-size: 1.1em; text-align: center; }
canvas { width: 100% !important; height: 300px !important; }

/* Trend selector */
.trend-selector { margin-bottom: 25px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.trend-selector select { padding: 8px 12px; border-radius: 8px; border: 1px solid #ccc; font-size: 0.95em; transition: 0.3s; }
.trend-selector select:hover { border-color: #127137; }

/* Export form */
.report-export { background: linear-gradient(to bottom right,#f9fdf9,#ffffff); padding: 20px; border-radius: 12px; margin-top: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.report-export h3 { margin-top: 0; font-size: 1.2em; }
.report-export select, .report-export button { margin-top: 12px; width: 100%; padding: 10px; border-radius: 8px; font-size: 0.95em; border: 1px solid #ccc; }
.report-export button { background-color: #127137; color: #fff; border: none; cursor: pointer; transition: 0.3s; }
.report-export button:hover { background-color: #0e5d2c; }

/* Responsive adjustments */
@media (max-width: 1024px) { 
    .main-content {
        margin-left: 0;
        transition: margin-left 0.3s ease;
    }
    #toggleSidebar {
        display: block;
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1100;
    }
    .sidebar {
        top: 0;
        height: 100%;
        position: fixed;
        transform: translateX(-100%);
        width: 220px;
        padding: 20px;
        transition: transform 0.3s ease;
        box-shadow: 2px 0 8px rgba(0,0,0,0.1);
        z-index: 1050;
    }
    .sidebar.open {
        transform: translateX(0);
    }
    .sidebar.closed { transform: translateX(-100%); }
    body.sidebar-open { overflow: hidden; }
}

@media (max-width: 768px){
    .chart-box { height: auto; }
}

</style>
</head>
<body>

<header>
    <button id="toggleSidebar">&#9776;</button>
    <img src="assets/images/logo.png" alt="AfyaCall Logo">
    <h1><?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
    <form action="logout.php" method="POST" style="margin:0;">
        <button type="submit" name="logout">Logout</button>
    </form>
</header>

<aside class="sidebar">
    <ul>
        <li><a href="admin_dashboard.php">Overview</a></li>
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
        <li><a href="reports_analytics.php" class="active">Reports & Analytics</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</aside>

<main class="main-content">
    <h2>Reports & Analytics</h2>

    <div class="stats-container">
        <div class="stats-card"><h3><?php echo $total_doctors; ?></h3><p>Doctors</p></div>
        <div class="stats-card"><h3><?php echo $total_specialties; ?></h3><p>Specialties</p></div>
        <div class="stats-card"><h3><?php echo $total_patients; ?></h3><p>Patients</p></div>
        <div class="stats-card"><h3><?php echo $total_appointments; ?></h3><p>Appointments</p></div>
    </div>

    <div class="trend-selector">
        <label for="trend">Select Trend:</label>
        <select id="trend">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
        </select>
    </div>

    <div class="chart-container">
        <div class="chart-box">
            <h3>Appointments per Doctor</h3>
            <canvas id="appointmentsPerDoctor"></canvas>
        </div>
        <div class="chart-box">
            <h3>Appointments per Specialty</h3>
            <canvas id="appointmentsPerSpecialty"></canvas>
        </div>
        <div class="chart-box">
            <h3>Bookings Trend</h3>
            <canvas id="bookingsTrend"></canvas>
        </div>
        <div class="chart-box">
            <h3>Specialty Distribution</h3>
            <canvas id="specialtyPie"></canvas>
        </div>
    </div>

    <div class="report-export">
        <h3>Generate & Export Reports</h3>
        <form id="exportForm" method="POST" action="export_report.php">
            <label>Report Type:</label>
            <select name="report_type">
                <option value="appointments">Appointments</option>
                <option value="doctors">Doctors</option>
                <option value="patients">Patients</option>
            </select>

            <label>Timeframe:</label>
            <select name="timeframe">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
            </select>

            <label>Export As:</label>
            <select name="export_format">
                <option value="pdf">PDF</option>
                <option value="excel">Excel</option>
                <option value="word">Word</option>
            </select>

            <button type="submit">Generate Report</button>
        </form>
    </div>
</main>

<script>
// Sidebar toggle logic for both desktop & mobile
const sidebar = document.querySelector('.sidebar');
const toggleBtn = document.getElementById('toggleSidebar');

toggleBtn.addEventListener('click', () => {
    if(window.innerWidth <= 1024){
        // Mobile overlay behavior
        sidebar.classList.toggle('open');
        document.body.classList.toggle('sidebar-open');
    } else {
        // Desktop push behavior
        sidebar.classList.toggle('closed');
        document.querySelector('.main-content').style.marginLeft = sidebar.classList.contains('closed') ? '0' : '240px';
    }
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', (e) => {
    if(window.innerWidth <= 1024){
        if(!sidebar.contains(e.target) && !toggleBtn.contains(e.target) && sidebar.classList.contains('open')){
            sidebar.classList.remove('open');
            document.body.classList.remove('sidebar-open');
        }
    }
});


// Data for charts
const doctorsLabels = <?php echo json_encode(array_column($doctors,'doctor_name')); ?>;
const doctorsData = <?php echo json_encode(array_column($doctors,'total_appointments')); ?>;
const specialtiesLabels = <?php echo json_encode(array_column($specialties,'specialty_name')); ?>;
const specialtiesData = <?php echo json_encode(array_column($specialties,'total_requests')); ?>;

const dailyLabels = <?php echo json_encode(array_column($bookings_daily,'day')); ?>;
const dailyData = <?php echo json_encode(array_column($bookings_daily,'total_bookings')); ?>;
const weeklyLabels = <?php echo json_encode(array_column($bookings_weekly,'week')); ?>;
const weeklyData = <?php echo json_encode(array_column($bookings_weekly,'total_bookings')); ?>;
const monthlyLabels = <?php echo json_encode(array_column($bookings_monthly,'month')); ?>;
const monthlyData = <?php echo json_encode(array_column($bookings_monthly,'total_bookings')); ?>;

const chartOptions = {
    responsive: true,
    plugins: {
        legend: { display: true },
        datalabels: { color: '#127137', anchor: 'end', align: 'top', font: { weight: 'bold' } }
    },
    animation: { duration: 1000, easing: 'easeOutQuart' }
};

// Appointments per doctor
new Chart(document.getElementById('appointmentsPerDoctor'), {
    type: 'bar',
    data: { labels: doctorsLabels, datasets:[{ label:'Appointments', data: doctorsData, backgroundColor:'#127137' }]},
    options: chartOptions,
    plugins: [ChartDataLabels]
});

// Appointments per specialty
new Chart(document.getElementById('appointmentsPerSpecialty'), {
    type: 'bar',
    data: { labels: specialtiesLabels, datasets:[{ label:'Appointments', data: specialtiesData, backgroundColor:'#0e5d2c' }]},
    options: chartOptions,
    plugins: [ChartDataLabels]
});

// Specialty distribution pie
new Chart(document.getElementById('specialtyPie'), {
    type: 'pie',
    data: { labels: specialtiesLabels, datasets:[{ data: specialtiesData, backgroundColor: specialtiesLabels.map(()=>`hsl(${Math.random()*360},70%,50%)`) }]},
    options: { responsive:true, plugins:{ legend:{ position:'right' }, datalabels:{ color:'#fff', font:{ weight:'bold' } } } },
    plugins: [ChartDataLabels]
});

// Bookings Trend chart
let bookingsTrendChart = new Chart(document.getElementById('bookingsTrend'), {
    type: 'line',
    data: { labels: dailyLabels, datasets:[{ label:'Bookings', data: dailyData, backgroundColor:'rgba(18,113,55,0.2)', borderColor:'#127137', borderWidth:2, fill:true, tension:0.4 }]},
    options: chartOptions,
    plugins: [ChartDataLabels]
});

// Trend selector
document.getElementById('trend').addEventListener('change', function(){
    let trend = this.value;
    let labels=[], data=[];
    if(trend=='daily'){ labels=dailyLabels; data=dailyData; }
    else if(trend=='weekly'){ labels=weeklyLabels; data=weeklyData; }
    else if(trend=='monthly'){ labels=monthlyLabels; data=monthlyData; }
    bookingsTrendChart.data.labels = labels;
    bookingsTrendChart.data.datasets[0].data = data;
    bookingsTrendChart.update();
});
</script>

</body>
</html>
