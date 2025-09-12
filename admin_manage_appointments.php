<?php
include 'auth_check.php';
authorize(['admin']);
include 'config/db.php';

$appointments = $conn->query("
    SELECT a.id as appointment_id, u.name AS patient_name, du.name AS doctor_name, ds.slot_datetime, a.status
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users du ON d.user_id = du.id
    JOIN doctor_slots ds ON a.slot_id = ds.id
    ORDER BY ds.slot_datetime ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Appointments</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f7f8;
    margin: 0;
    padding: 20px;
}

h2 {
    color: #127137;
    margin-bottom: 20px;
}

/* Table wrapper */
.table-wrapper {
    overflow-x: auto;
    margin-bottom: 20px;
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #ccc; /* Add this line */
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border: 1px solid #ccc; /* Make the border darker */
}

th {
    background-color: #127137;
    color: #fff;
    font-weight: 600;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

tr:hover {
    background-color: #e6f0ea;
}

/* Status badges */
.status {
    display: inline-flex;  
    width:  min-content;  
    padding: 3px 8px;           
    border-radius: 6px;       
    color: #fff;
    font-weight: 600;
    font-size: 0.85em;          
    text-align: center;
    vertical-align: middle;
    min-width: 70px;            
    transition: background-color 0.3s ease;
    white-space: nowrap;         
    margin-top: 10px;
    margin-left: 10px;
    justify-content: center;  
}

/* Status colors */
.status-pending {
    background-color: #f0ad4e;
}

.status-accepted {
    background-color: #127137;
}

.status-rejected {
    background-color: #d9534f;
}

/* Action buttons */
.update-btn {
    padding: 6px 12px;
    margin-right: 5px;
    border: none;
    border-radius: 6px;
    color: #fff;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.update-btn[data-status="accepted"] {
    background-color: #127137;
}

.update-btn[data-status="accepted"]:hover {
    background-color: #0e5d2c;
}

.update-btn[data-status="rejected"] {
    background-color: #d9534f;
    
}

.update-btn[data-status="rejected"]:hover {
    background-color: #c9302c;
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

<h2>All Appointments</h2>
<div class="table-wrapper">
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Slot</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($appointments as $a): ?>
        <tr id="appt-<?php echo $a['appointment_id']; ?>">
            <td><?php echo $a['appointment_id']; ?></td>
            <td><?php echo htmlspecialchars($a['patient_name']); ?></td>
            <td><?php echo htmlspecialchars($a['doctor_name']); ?></td>
            <td><?php echo $a['slot_datetime']; ?></td>
            <td class="status <?php echo 'status-'.$a['status']; ?>"><?php echo $a['status']; ?></td>
            <td>
                <?php if($a['status'] == 'pending'): ?>
                    <button class="update-btn" data-id="<?php echo $a['appointment_id']; ?>" data-status="accepted">Approve</button>
                    <button class="update-btn" data-id="<?php echo $a['appointment_id']; ?>" data-status="rejected">Cancel</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<a href="admin_dashboard.php" class="btn-back">Back</a>

</div>

<script>
$('.update-btn').click(function(){
    let id = $(this).data('id');
    let status = $(this).data('status');

    $.post('update_appointment.php', { id:id, status:status }, function(resp){
        if(resp.status === 'success'){
            $('#appt-'+id+' .status').text(status)
                .removeClass('status-pending status-accepted status-rejected')
                .addClass('status-'+status);
            Swal.fire('Updated!', resp.message, 'success');
        } else {
            Swal.fire('Error', resp.message, 'error');
        }
    }, 'json');
});
</script>

</body>
</html>
