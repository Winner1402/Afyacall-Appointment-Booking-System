<?php
include 'auth_check.php';
authorize(['admin']);
include 'config/db.php';

// Handle date filter
$selected_date = $_GET['date'] ?? date('Y-m-d');

// Fetch filtered login activities
$stmt = $conn->prepare("
    SELECT u.name, u.email, u.role, l.action, l.ip_address, l.user_agent, l.login_time, l.logout_time
    FROM user_logs l
    JOIN users u ON l.user_id = u.id
    WHERE DATE(l.login_time) = :date
    ORDER BY l.login_time DESC
");
$stmt->execute(['date' => $selected_date]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Activity Log</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    background: linear-gradient(to bottom right, #e6f4ea, #ffffff);
}
nav {
    background-color: #127137;
    color: #fff;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}
nav a {
    color: #fff;
    text-decoration: none;
    margin-left: 15px;
    font-weight: bold;
    transition: opacity 0.3s ease;
}
nav a:hover { opacity: 0.8; }

.container {
    padding: 20px;
}
h2 {
    color: #127137;
    margin-bottom: 15px;
}
.controls {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 20px;
}
.controls .btn-back {
    background-color: #127137;
    color: #fff;
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    transition: all 0.3s ease;
}
.controls .btn-back:hover {
    background-color: #0e5d2c;
    transform: translateY(-2px);
}
.controls input[type="date"],
.controls input[type="text"] {
    padding: 10px 15px;
    border-radius: 6px;
    border: 1px solid #127137;
    outline: none;
    transition: all 0.3s ease;
    font-size: 14px;
}
.controls input:focus {
    border-color: #0e5d2c;
    box-shadow: 0 0 8px rgba(18,113,55,0.3);
}
.controls button {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    background-color: #127137;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}
.controls button:hover {
    background-color: #0e5d2c;
    transform: translateY(-2px);
}

table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
thead {
    background: linear-gradient(90deg, #127137, #38a169);
    color: #fff;
}
thead th {
    padding: 12px;
    text-align: left;
}
tbody td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    font-size: 14px;
}
tbody tr:nth-child(even) { background-color: #f9f9f9; }
tbody tr:hover { background-color: #e6f0ea; }

/* Fully responsive table without stacking */
@media (max-width: 768px) {
    table, thead, tbody, th, td, tr {
        display: block;
        width: 100%;
    }
    thead tr { display: none; }
    tbody td {
        padding: 10px 10px;
        border-bottom: 1px solid #ddd;
        position: relative;
        text-align: left;
        padding-left: 50%;
        white-space: normal;
    }
    tbody td::before {
        position: absolute;
        top: 10px;
        left: 15px;
        width: 45%;
        font-weight: bold;
        white-space: nowrap;
    }
    tbody td:nth-of-type(1)::before { content: "User"; }
    tbody td:nth-of-type(2)::before { content: "Email"; }
    tbody td:nth-of-type(3)::before { content: "Login Time"; }
    tbody td:nth-of-type(4)::before { content: "Logout Time"; }
    tbody td:nth-of-type(5)::before { content: "IP Address"; }
}
</style>
</head>
<body>
<nav>
    <div>AfyaCall Admin</div>
    <div>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
<h2>User Activity Log</h2>
<div class="controls">
     <form method="GET" style="display:flex; gap:10px;">
        <input type="date" name="date" value="<?php echo $selected_date; ?>">
        <button type="submit">Filter</button>
    </form>
    <input type="text" id="search" placeholder="Search users...">
</div>

<table id="logsTable">
    <thead>
        <tr>
            <th>User</th>
            <th>Email</th>
            <th>Login Time</th>
            <th>Logout Time</th>
            <th>IP Address</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($logs as $l): ?>
        <tr>
            <td><?php echo htmlspecialchars($l['name']); ?></td>
            <td><?php echo htmlspecialchars($l['email']); ?></td>
            <td><?php echo $l['login_time']; ?></td>
            <td><?php echo $l['logout_time']; ?></td>
            <td><?php echo $l['ip_address']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<script>
// Search function
const searchInput = document.getElementById('search');
searchInput.addEventListener('keyup', function() {
    const filter = searchInput.value.toLowerCase();
    const rows = document.querySelectorAll('#logsTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>
</body>
</html>
