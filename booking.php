<?php
session_start();
include 'config\db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch doctors with name and specialty
$stmt = $conn->prepare("
    SELECT d.id AS doctor_id, u.name AS doctor_name, s.name AS specialty_name
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    JOIN specialties s ON d.specialty_id = s.id
");
$stmt->execute();
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book Appointment</title>
<link rel="stylesheet" href="assets/css/patient_dashboard.css">
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
            <li><a href="booking.php" class="active">Book Appointment</a></li>
            <li><a href="upcoming.php">Upcoming Appointments</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <form id="booking-form" class="profile-form">
            <h2 class="form-title">Book Appointment</h2>

            <div class="form-group">
                <label for="doctor">Select Doctor</label>
                <select id="doctor" name="doctor_id" required>
                    <option value="">--Select Doctor--</option>
                    <?php foreach($doctors as $doc): ?>
                        <option value="<?php echo $doc['doctor_id']; ?>">
                            <?php echo htmlspecialchars($doc['doctor_name'] . " (" . $doc['specialty_name'] . ")"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="date">Appointment Date</label>
                <input type="date" id="date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label for="time">Appointment Time</label>
                <select id="time" name="slot_id" required>
                    <option value="">--Select Time--</option>
                </select>
            </div>

            <button type="submit">Book Now</button>
        </form>
    </main>
</div>

<script>
$(document).ready(function() {
    function loadSlots() {
        var doctor_id = $('#doctor').val();
        var date = $('#date').val();
        
        if(doctor_id && date){
            // Show loading indicator
            var slotDropdown = $('#time');
            slotDropdown.empty();
            slotDropdown.append('<option value="">Loading slots...</option>');
            
            $.ajax({
                url: 'get_slots.php',
                type: 'POST',
                data: {doctor_id: doctor_id, appointment_date: date},
                dataType: 'json',
                success: function(slots){
                    slotDropdown.empty();
                    slotDropdown.append('<option value="">--Select Time--</option>');
                    
                    if(slots.length > 0){
                        $.each(slots, function(i, slot){
                            slotDropdown.append('<option value="'+slot.slot_id+'">'+slot.time+'</option>');
                        });
                    } else {
                        slotDropdown.append('<option value="">No slots available</option>');
                    }
                },
                error: function(xhr, status, error) {
                    slotDropdown.empty();
                    slotDropdown.append('<option value="">Error loading slots</option>');
                    console.error('Error loading slots:', error);
                }
            });
        } else {
            // Reset time dropdown if doctor or date is not selected
            $('#time').empty().append('<option value="">--Select Time--</option>');
        }
    }

    $('#doctor, #date').on('change', loadSlots);

    // Set minimum date for date picker
    var today = new Date().toISOString().split('T')[0];
    $('#date').attr('min', today);

    $('#booking-form').on('submit', function(e) {
        e.preventDefault();
        
        // Basic validation
        if (!$('#doctor').val() || !$('#date').val() || !$('#time').val()) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Please fill all fields'
            });
            return;
        }

        // Show loading animation
        Swal.fire({
            title: 'Booking Appointment...',
            text: 'Please wait while we process your booking',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            type: 'POST',
            url: 'book_process.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    // Success message with different icons based on email status
                    if (response.message.includes('email sent')) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Booking Confirmed!',
                            html: `
                                <div style="text-align: left;">
                                    <p>✅ Your appointment has been booked successfully!</p>
                                    <p>📧 A confirmation email has been sent to your email address.</p>
                                    <p>📋 Please check your email for appointment details.</p>
                                </div>
                            `,
                            showConfirmButton: true,
                            confirmButtonText: 'Ok!',
                            confirmButtonColor: '#4CAF50',
                            timer: 5000
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Booking Confirmed!',
                            html: `
                                <div style="text-align: left;">
                                    <p>✅ Your appointment has been booked successfully!</p>
                                    <p>⚠️ Email notification could not be sent.</p>
                                    <p>📋 Please note your appointment details.</p>
                                </div>
                            `,
                            showConfirmButton: true,
                            confirmButtonText: 'Okay',
                            confirmButtonColor: '#FFA500'
                        });
                    }
                    
                    // Reset form
                    $('#booking-form')[0].reset();
                    $('#time').empty().append('<option value="">--Select Time--</option>');
                    
                } else {
                    // Error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Booking Failed!',
                        html: `
                            <div style="text-align: left;">
                                <p>❌ ${response.message}</p>
                                <p>Please try again or contact support if the issue persists.</p>
                            </div>
                        `,
                        showConfirmButton: true,
                        confirmButtonText: 'Try Again',
                        confirmButtonColor: '#FF3860'
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    html: `
                        <div style="text-align: left;">
                            <p>❌ Something went wrong with the server.</p>
                            <p>Please try again in a few moments.</p>
                            <p><small>Error: ${error}</small></p>
                        </div>
                    `,
                    showConfirmButton: true,
                    confirmButtonText: 'Retry',
                    confirmButtonColor: '#FF3860'
                });
                console.error('Booking error:', error);
            }
        });
    });
});
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
                        if (response.message.includes('email sent')) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Cancelled!',
                                html: `
                                    <div style="text-align: left;">
                                        <p>✅ Appointment cancelled successfully!</p>
                                        <p>📧 A cancellation confirmation has been sent to your email.</p>
                                    </div>
                                `,
                                confirmButtonText: 'Okay',
                                confirmButtonColor: '#4CAF50'
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Cancelled!',
                                html: `
                                    <div style="text-align: left;">
                                        <p>✅ Appointment cancelled successfully!</p>
                                        <p>⚠️ Email notification could not be sent.</p>
                                    </div>
                                `,
                                confirmButtonText: 'Okay',
                                confirmButtonColor: '#FFA500'
                            });
                        }
                        // Reload the page to update the appointments list
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
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
