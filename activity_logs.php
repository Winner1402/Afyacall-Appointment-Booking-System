<?php
include 'auth_check.php';
authorize(['admin']);
include 'config/db.php';
 
// Fetch login activities
$logs = $conn->query("
    SELECT u.name, u.email, u.role, l.action, l.ip_address, l.user_agent, l.login_time, l.logout_time
    FROM user_logs l
    JOIN users u ON l.user_id = u.id
    ORDER BY l.login_time DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Activity Log</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>  
body {
    font-family: Arial, sans-serif;
    background-color: #f5f7f6;
    margin: 0;
    padding: 20px;
}

h2 {
    color: #127137;
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

thead {
    background-color: #127137;
    color: #fff;
}

thead th {
    padding: 12px 15px;
    text-align: left;
}

tbody td {
    padding: 10px 15px;
    border-bottom: 1px solid #ddd;
}

tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

tbody tr:hover {
    background-color: #e6f0ea;
}
.btn-back {
    display: inline-block;
    padding: 10px 20px;
    background-color: #127137;
    color: #fff;
    margin-top: 20px;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.btn-back:hover {
    background-color: #0e5d2c;
    transform: translateY(-2px);
}
 </style>
</head>
<body>
<h2>User Activity Log</h2>
<table border="1" cellpadding="10">
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
<a href="admin_dashboard.php" class="btn-back">Back</a>
</body>
</html>
