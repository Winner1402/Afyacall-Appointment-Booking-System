<?php
session_start();
include 'config/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='doctor'){
    exit("Unauthorized access");
}

$user_id = $_SESSION['user_id'];
$doctor_name = $_SESSION['user_name'];

// Fetch doctor_id from doctors table
$stmt = $conn->prepare("SELECT id FROM doctors WHERE user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$doctor){
    exit("Doctor record not found");
}
$doctor_id = $doctor['id'];

// Fetch existing slots
$stmt = $conn->prepare("
    SELECT id, slot_datetime, end_datetime, status
    FROM doctor_slots
    WHERE doctor_id = :doctor_id
    ORDER BY slot_datetime ASC
");
$stmt->execute([':doctor_id' => $doctor_id]);
$slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Slots | AfyaCall</title>
<link rel="stylesheet" href="assets/css/patient_dashboard.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
.container {
    max-width: 1000px;
    margin: 40px auto;
    padding: 25px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: fit-content;
}

h2 {
    text-align: center;
    color: #127137;
    margin-bottom: 20px;
}

.form-group { margin-bottom: 15px; }
label { display:block; font-weight:bold; margin-bottom:5px; }
input, select { width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; box-sizing:border-box; }
button { padding:10px 15px; border:none; border-radius:6px; background:#127137; color:#fff; font-weight:bold; cursor:pointer; }
button:hover { background:#0e5d2c; }
.table-wrapper { overflow-x:auto; margin-top:20px; }
table { width:100%; border-collapse:collapse; }
th, td { padding:10px; border:1px solid #ccc; text-align:left; }
th { background:#127137; color:#fff; }
tr:nth-child(even){ background:#f9f9f9; }
tr:hover { background:#e6f0ea; }
button.delete { background:#c0392b; }
button.delete:hover { background:#a83224; }
button.toggle { background:#f39c12; }
button.toggle:hover { background:#d78c0e; }
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

<div class="container">
    <h2>Manage Your Slots</h2>

    <!-- Add Slot Form -->
    <div class="add-slot">
        <h3>Add New Slot</h3>
        <form id="add-slot-form">
            <div class="form-group">
                <label for="slot_date">Date</label>
                <input type="date" id="slot_date" name="slot_date" min="<?= date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label for="start_time">Start Time</label>
                <input type="time" id="start_time" name="start_time" required>
            </div>
            <div class="form-group">
                <label for="end_time">End Time</label>
                <input type="time" id="end_time" name="end_time" required>
            </div>
            <button type="submit">Add Slot</button>
        </form>
    </div>

    <!-- Existing Slots Table -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="slots-table-body">
                <?php foreach($slots as $s): ?>
                <tr data-id="<?= $s['id'] ?>">
                    <td><?= date('d M Y', strtotime($s['slot_datetime'])) ?></td>
                    <td><?= date('H:i', strtotime($s['slot_datetime'])) ?></td>
                    <td><?= date('H:i', strtotime($s['end_datetime'])) ?></td>
                    <td><?= $s['status'] == 0 ? 'Available' : 'Unavailable' ?></td>
                    <td>
                        <button class="delete" data-id="<?= $s['id'] ?>">Delete</button>
                        <button class="toggle" data-id="<?= $s['id'] ?>">
                            <?= $s['status']==0 ? 'Mark Unavailable' : 'Mark Available' ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="doctor_dashboard.php" class="btn-back">Back</a>
    </div>
    
</div>

<script>
$(document).ready(function(){

    // Add Slot
    $('#add-slot-form').submit(function(e){
        e.preventDefault();
        $.ajax({
            url:'add_slot.php',
            type:'POST',
            data: $(this).serialize(),
            dataType:'json',
            success: function(resp){
                if(resp.status==='success'){
                    Swal.fire('Added', resp.message, 'success').then(()=> location.reload());
                } else {
                    Swal.fire('Error', resp.message, 'error');
                }
            }
        });
    });

    // Delete Slot
    $('.delete').click(function(){
        var slot_id = $(this).data('id');
        Swal.fire({
            title:'Are you sure?',
            text:'This will delete the slot permanently!',
            icon:'warning',
            showCancelButton:true,
            confirmButtonColor:'#c0392b',
            cancelButtonColor:'#3085d6',
            confirmButtonText:'Yes, delete!'
        }).then((result)=>{
            if(result.isConfirmed){
                $.post('delete_slot.php', {slot_id:slot_id}, function(resp){
                    if(resp.status==='success'){
                        Swal.fire('Deleted', resp.message, 'success').then(()=> location.reload());
                    } else {
                        Swal.fire('Error', resp.message, 'error');
                    }
                }, 'json');
            }
        });
    });

    // Toggle Slot Availability
    $('.toggle').click(function(){
        var slot_id = $(this).data('id');
        $.post('toggle_slot.php', {slot_id:slot_id}, function(resp){
            if(resp.status==='success'){
                Swal.fire('Updated', resp.message, 'success').then(()=> location.reload());
            } else {
                Swal.fire('Error', resp.message, 'error');
            }
        }, 'json');
    });

});
</script>

</body>
</html>
