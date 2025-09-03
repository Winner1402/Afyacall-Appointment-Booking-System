<?php
session_start();
include 'config\db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];

// Fetch upcoming appointments
$stmt = $conn->prepare("
    SELECT a.id AS appointment_id, 
           a.doctor_id, 
           a.slot_id,
           u.name AS doctor_name, 
           s.name AS specialty_name,
           ds.slot_datetime, 
           a.status
    FROM appointments a
    JOIN doctor_slots ds ON a.slot_id = ds.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users u ON d.user_id = u.id
    JOIN specialties s ON d.specialty_id = s.id
    WHERE a.patient_id = :patient_id
      AND ds.slot_datetime >= NOW()
      AND a.status NOT IN ('cancelled', 'rejected')
    ORDER BY ds.slot_datetime ASC
");

$stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upcoming Appointments</title>
<link rel="stylesheet" href="assets\css\patient_dashboard.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<header class="page-header">
    <img src="assets/images/logo.jpeg" alt="AfyaCall Logo" class="logo">
    <h1><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Guest'; ?></h1>
    <form action="logout.php" method="POST" style="margin:0;">
        <button type="submit" name="logout" class="logout-btn">Logout</button>
    </form>
</header>
 
<div class="container">
    <aside class="sidebar">
        <ul>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="booking.php">Book Appointment</a></li>
            <li><a href="upcoming.php" class="active">Upcoming Appointments</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h2>Upcoming Appointments</h2>
        <?php if(count($appointments) > 0): ?>
            <table class="appointments-table">
             <thead>
                <tr>
                    <th>Doctor</th>
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
    $cutoff = 24 * 60 * 60; // 24 hours in seconds
    $can_modify = ($slot_time - $now) > $cutoff && $a['status'] == 'pending';
?>
<tr data-appointment-id="<?php echo $a['appointment_id']; ?>"
    data-doctor-id="<?php echo $a['doctor_id']; ?>"
    data-slot-id="<?php echo $a['slot_id']; ?>">
    <td><?php echo htmlspecialchars($a['doctor_name']); ?></td>
    <td><?php echo htmlspecialchars($a['specialty_name']); ?></td>
    <td><?php echo date('d M Y H:i', $slot_time); ?></td>
    <td><?php echo ucfirst($a['status']); ?></td>
    <td>
        <?php if($can_modify): ?>
            <button class="cancel-btn" data-id="<?php echo $a['appointment_id']; ?>">Cancel</button>
            <button class="reschedule-btn" 
                    data-appointment-id="<?php echo $a['appointment_id']; ?>"
                    data-doctor-id="<?php echo $a['doctor_id']; ?>"
                    data-slot-id="<?php echo $a['slot_id']; ?>"
                    data-appointment-date="<?php echo date('Y-m-d', $slot_time); ?>">
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

<!-- Reschedule modal -->
<div id="rescheduleModal" style="display:none;">
  <div class="modal-content">
    <h3>Reschedule Appointment</h3>
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
      <button type="submit">Confirm</button>
      <button type="button" id="closeModal">Cancel</button>
    </form>
  </div>
</div>

<script>
$(document).ready(function(){
    // Cancel Appointment
    $(document).on('click', '.cancel-btn', function(){
        var appointment_id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You can only cancel if more than 24 hours left!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel it!'
        }).then((result) => {
            if(result.isConfirmed){
                $.post('cancel_appointment.php', { appointment_id: appointment_id }, function(resp){
                    if(resp.status === 'success'){
                        Swal.fire('Cancelled!', resp.message, 'success').then(()=> location.reload());
                    } else {
                        Swal.fire('Error!', resp.message, 'error');
                    }
                }, 'json');
            }
        });
    });

    // Click handler
 });
 $(document).on('click', '.reschedule-btn', function() {
    const appointmentId = $(this).data('appointment-id');
    const doctorId = $(this).data('doctor-id');
    const currentSlotId = $(this).data('slot-id');
    const currentDate = $(this).data('appointment-date');

 
    openRescheduleModal(appointmentId, doctorId, currentSlotId, currentDate);
});

// Function to open reschedule modal with date picker + dynamic slots
function openRescheduleModal(appointmentId, doctorId, currentSlotId, currentDate) {
    Swal.fire({
        title: 'Reschedule Appointment',
        html: `
            <div style="text-align:left;">
                <label style="font-weight:bold;">Select new date:</label>
                <input type="date" id="new-date" class="swal2-input" style="width:100%; padding:10px; margin-bottom:10px;" value="${currentDate}">
                
                <label style="font-weight:bold;">Select available time slot:</label>
                <select id="available-slots" class="swal2-input" style="width:100%; padding:10px;">
                    <option value="">-- Select time slot --</option>
                </select>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Confirm Reschedule',
        cancelButtonText: 'Cancel',
        focusConfirm: false,
        didOpen: () => {
            // Load slots for initial date
            loadAvailableSlots(doctorId, currentSlotId, currentDate);

            // Reload slots when date changes
            $('#new-date').on('change', function() {
                const selectedDate = $(this).val();
                loadAvailableSlots(doctorId, currentSlotId, selectedDate);
            });
        },
        preConfirm: () => {
            const slotId = $('#available-slots').val();
            if (!slotId) {
                Swal.showValidationMessage('Please select a time slot');
                return false;
            }
            return { slotId };
        },
        customClass: {
            popup: 'swal2-popup-green',
            confirmButton: 'btn-green',
            cancelButton: 'btn-light'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            processReschedule(appointmentId, result.value.slotId);
        }
    });
}

// Function to load available slots via AJAX
function loadAvailableSlots(doctorId, excludeSlotId, date) {
    $('#available-slots').html('<option>Loading...</option>');
    $.ajax({
        url: 'get_available_slots.php', 
        type: 'POST',
        data: {
            doctor_id: doctorId,
            appointment_date: date,
            exclude_slot: excludeSlotId
        },
        dataType: 'json',
        success: function(slots) {
            let html = '<option value="">-- Select time slot --</option>';
            if (slots.length === 0) {
                html = '<option value="">No available slots</option>';
            } else {
                slots.forEach(slot => {
                    html += `<option value="${slot.slot_id}">${slot.time}</option>`;
                });
            }
            $('#available-slots').html(html);
        },
        error: function(xhr, status, error) {
            $('#available-slots').html('<option value="">Error loading slots</option>');
            console.error('Error loading slots:', error);
        }
    });
}

// Function to process reschedule
function processReschedule(appointmentId, newSlotId) {
    Swal.fire({
        title: 'Rescheduling...',
        text: 'Please wait while we process your request',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: 'reschedule_appointment.php',
        type: 'POST',
        data: {
            appointment_id: appointmentId,
            new_slot_id: newSlotId
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    html: `
                        <div style="text-align: left;">
                            <p>✅ ${response.message}</p>
                        </div>
                    `,
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    // Reload the page to show updated appointments
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message,
                    confirmButtonText: 'Okay',
                    confirmButtonColor: '#FF3860'
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Something went wrong. Please try again.',
                confirmButtonText: 'Okay',
                confirmButtonColor: '#FF3860'
            });
            console.error('Reschedule error:', error);
        }
    });
}

// Function to cancel appointment
function cancelAppointment(appointmentId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, cancel it!',
        cancelButtonText: 'No, keep it',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Cancelling...',
                text: 'Please wait while we process your cancellation',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send cancellation request
            $.ajax({
                url: 'cancel.php',
                type: 'POST',
                data: { appointment_id: appointmentId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Cancelled!',
                            text: response.message,
                            confirmButtonText: 'Okay',
                            confirmButtonColor: '#4CAF50'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message,
                            confirmButtonText: 'Okay',
                            confirmButtonColor: '#FF3860'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Something went wrong. Please try again.',
                        confirmButtonText: 'Okay',
                        confirmButtonColor: '#FF3860'
                    });
                }
            });
        }
    });
}
</script>
</body>
</html>
