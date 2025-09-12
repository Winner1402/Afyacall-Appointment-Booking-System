<?php 
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    exit("Unauthorized access");
}

$doctor_id = $_SESSION['user_id'];
$doctor_name = $_SESSION['user_name'];

// Fetch doctor's upcoming appointments
$stmt = $conn->prepare("
    SELECT a.id AS appointment_id, p.name AS patient_name,
           s.name AS specialty_name, ds.slot_datetime, a.status, a.slot_id
    FROM appointments a
    JOIN doctor_slots ds ON a.slot_id = ds.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users p ON a.patient_id = p.id
    JOIN specialties s ON d.specialty_id = s.id
    WHERE a.doctor_id = :doctor_id
      AND ds.slot_datetime >= NOW()
      AND a.status NOT IN ('cancelled','rejected')
    ORDER BY ds.slot_datetime ASC
");
$stmt->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>l
<html lang="en">
<head> 
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Doctor Dashboard | AfyaCall</title>
<link rel="stylesheet" href="assets/css/patient_dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/themify-icons@0.1.2/css/themify-icons.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- Header -->
<header class="page-header">
    <img src="assets/images/logo.jpeg" alt="AfyaCall Logo" class="logo">
    <h1><?php echo htmlspecialchars($doctor_name); ?></h1>
    <form action="logout.php" method="POST" style="margin:0;">
        <button type="submit" name="logout" class="logout-btn">Logout</button>
    </form>
</header>

<div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <ul>
            <li>
                <a href="manage_appointments.php" class="active">
                    <div class="item-media"><i class="ti-home"></i></div>
                    <div class="item-inner"><span class="title">Manage Appointments</span></div>
                </a>
            </li>
            <li>
                <a href="doctor_upcoming.php">
                    <div class="item-media"><i class="ti-calendar"></i></div>
                    <div class="item-inner"><span class="title">Upcoming Appointments</span></div>
                </a>
            </li>
          
            <li>
                <a href="doctor_manage_slots.php">
                    <div class="item-media"><i class="ti-user"></i></div>
                    <div class="item-inner"><span class="title">Manage Slots</span></div>
                </a>
            </li>
            <li>
                <a href="doctor_profile.php">
                    <div class="item-media"><i class="ti-id-badge"></i></div>
                    <div class="item-inner"><span class="title">Profile</span></div>
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <div class="item-media"><i class="ti-power-off"></i></div>
                    <div class="item-inner"><span class="title">Logout</span></div>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <section class="cards">
            <div class="card">
                <img src="assets/icons/appointment.png" alt="Upcoming Icon" class="card-icon">
                <h3>Upcoming Appointments</h3>
                <a href="doctor_upcoming.php">View Appointments</a>
            </div>

            <div class="card">
                <img src="assets/icons/history.png" alt="History Icon" class="card-icon">
                <h3>Appointment History</h3>
                <a href="doctor_history.php">View History</a>
            </div>

            <div class="card">
                <img src="assets/icons/manage.png" alt="Patients Icon" class="card-icon">
                <h3>Manage Patients</h3>
                <a href="medical_history.php">Manage Now</a>
            </div>

            
        </section>

        <h2>Upcoming Appointments</h2>
        <?php if(count($appointments) > 0): ?>
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Specialty</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($appointments as $a):
                        $slot_time = strtotime($a['slot_datetime']);
                        $now = time();
                        $cutoff = 24 * 60 * 60; // 24h
                        $can_modify = ($slot_time - $now) > $cutoff && $a['status'] == 'pending';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($a['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($a['specialty_name']); ?></td>
                        <td><?php echo date('d M Y H:i', strtotime($a['slot_datetime'])); ?></td>
                        <td><?php echo ucfirst($a['status']); ?></td>
                        <td>
                            <?php if($can_modify): ?>
                                <button class="cancel-btn" data-id="<?php echo $a['appointment_id']; ?>">Cancel</button>
                                <button class="reschedule-btn"
                                    data-id="<?php echo $a['appointment_id']; ?>"
                                    data-slot="<?php echo $a['slot_id']; ?>">
                                    Reschedule
                                </button>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No upcoming appointments.</p>
        <?php endif; ?>
    </main>
</div>

<!-- Reschedule Modal -->
<div id="rescheduleModal" style="display:none;">
  <div class="modal-content">
    <h3>Manage Appointment</h3>
    <form id="reschedule-form">
      <input type="hidden" name="appointment_id" id="reschedule-appointment-id" value="">
      <div class="form-group">
        <label for="new_date">New Date</label>
        <input type="date" id="new_date" name="new_date" required min="<?php echo date('Y-m-d'); ?>">
      </div>
      <div class="form-group">
        <label for="new_time">New Time</label>
        <select id="new_time" name="new_slot_id" required>
          <option value="">--Select Time--</option>
        </select>
      </div>
      <button type="submit">Confirm Booking</button>
      <button type="button" id="closeModal">Cancel</button>
    </form>
  </div>
</div>

<script>
$(document).ready(function(){

    // Cancel Appointment
    $('.cancel-btn').click(function(){
        var appointment_id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You can only cancel if more than 24 hours left!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel it!'
        }).then((result) => {
            if(result.isConfirmed){
                $.post('cancel_appointment.php', {appointment_id: appointment_id}, function(resp){
                    if(resp.status === 'success'){
                        Swal.fire('Cancelled!', resp.message, 'success').then(()=>location.reload());
                    } else {
                        Swal.fire('Error!', resp.message, 'error');
                    }
                }, 'json');
            }
        });
    });

    // Open Reschedule Modal
    $('.reschedule-btn').click(function(){
        var appointment_id = $(this).data('id');
        var old_slot_id = $(this).data('slot');
        $('#reschedule-appointment-id').val(appointment_id);
        $('#new_date').data('old-slot-id', old_slot_id);
        $('#new_date').val('');
        $('#new_time').empty().append('<option value="">--Select Time--</option>');
        $('#rescheduleModal').show();
    });

    // Close Modal
    $('#closeModal').click(function(){
        $('#rescheduleModal').hide();
    });

    // Load slots when date changes
    $('#new_date').change(function(){
        var date = $(this).val();
        var oldSlotId = $(this).data('old-slot-id');
        if(!date) return;
        $.post('get_slots.php', {appointment_date: date}, function(slots){
            var slotDropdown = $('#new_time');
            slotDropdown.empty();
            slotDropdown.append('<option value="">--Select Time--</option>');
            if(slots.length){
                slots.forEach(function(slot){
                    if(slot.slot_id == oldSlotId) return; // skip old
                    slotDropdown.append('<option value="'+slot.slot_id+'">'+slot.time+'</option>');
                });
            } else {
                slotDropdown.append('<option value="">No slots available</option>');
            }
        }, 'json');
    });

    // Submit reschedule
    $('#reschedule-form').submit(function(e){
        e.preventDefault();
        var appointment_id = $('#reschedule-appointment-id').val();
        var new_slot_id = $('#new_time').val();
        if(!new_slot_id){
            Swal.fire('Error','Please select a new time.','error');
            return;
        }
        $.post('reschedule_appointment.php', {appointment_id: appointment_id, new_slot_id: new_slot_id}, function(resp){
            if(resp.status==='success'){
                Swal.fire('Rescheduled!', resp.message, 'success').then(()=>location.reload());
            } else {
                Swal.fire('Error', resp.message, 'error');
            }
        }, 'json');
    });

});
</script>

</body>
</html>
