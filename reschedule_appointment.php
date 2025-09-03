<?php
session_start();
include 'config/db.php';
require_once 'includes/email_functions.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error','message'=>'Please login first.']);
    exit();
}

if (!isset($_POST['appointment_id'], $_POST['new_slot_id'])) {
    echo json_encode(['status'=>'error','message'=>'Incomplete data.']);
    exit();
}

$patient_id = $_SESSION['user_id'];
$appointment_id = (int) $_POST['appointment_id'];
$new_slot_id = (int) $_POST['new_slot_id'];

try {
    $conn->beginTransaction();

    // Lock appointment row and get old slot + doctor details including slot time
    $stmt = $conn->prepare("
        SELECT a.id, a.slot_id, a.doctor_id, a.status, ds.slot_datetime as old_slot_time 
        FROM appointments a 
        JOIN doctor_slots ds ON a.slot_id = ds.id 
        WHERE a.id = :id AND a.patient_id = :pid FOR UPDATE
    ");
    $stmt->bindParam(':id', $appointment_id, PDO::PARAM_INT);
    $stmt->bindParam(':pid', $patient_id, PDO::PARAM_INT);
    $stmt->execute();
    $appt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appt) {
        $conn->rollBack();
        echo json_encode(['status'=>'error','message'=>'Appointment not found.']);
        exit();
    }
    
    if ($appt['status'] != 'pending') {
        $conn->rollBack();
        echo json_encode(['status'=>'error','message'=>'Only pending appointments can be rescheduled.']);
        exit();
    }

    $old_slot_id = (int)$appt['slot_id'];
    $doctor_id = (int)$appt['doctor_id'];
    $old_slot_time = $appt['old_slot_time'];

    // Check 24-hour restriction for old appointment
    if (strtotime($old_slot_time) - time() < 24*60*60) {
        $conn->rollBack();
        echo json_encode(['status'=>'error','message'=>'Cannot reschedule within 24 hours of the appointment.']);
        exit();
    }

    // Lock and validate new slot: must be unbooked and belong to same doctor
    $stmt2 = $conn->prepare("
        SELECT id, is_booked, doctor_id, slot_datetime 
        FROM doctor_slots 
        WHERE id = :sid FOR UPDATE
    ");
    $stmt2->bindParam(':sid', $new_slot_id, PDO::PARAM_INT);
    $stmt2->execute();
    $newSlot = $stmt2->fetch(PDO::FETCH_ASSOC);

    if (!$newSlot) {
        $conn->rollBack();
        echo json_encode(['status'=>'error','message'=>'Selected slot not found.']);
        exit();
    }
    
    if ((int)$newSlot['doctor_id'] !== $doctor_id) { 
        $conn->rollBack();
        echo json_encode(['status'=>'error','message'=>'Selected slot does not belong to the same doctor.']);
        exit();
    }
    
    if ((int)$newSlot['is_booked'] === 1) {
        $conn->rollBack();
        echo json_encode(['status'=>'error','message'=>'Selected slot is already booked.']);
        exit();
    }

    // Check if new slot is within 24 hours from now
    $new_slot_time = $newSlot['slot_datetime'];
    if (strtotime($new_slot_time) - time() < 24*60*60) {
        $conn->rollBack();
        echo json_encode(['status'=>'error','message'=>'Cannot reschedule to a slot within 24 hours from now.']);
        exit();
    }

    // Free old slot
    $updOld = $conn->prepare("UPDATE doctor_slots SET is_booked = 0 WHERE id = :old_id");
    $updOld->bindParam(':old_id', $old_slot_id, PDO::PARAM_INT);
    $updOld->execute();

    // Book new slot
    $updNew = $conn->prepare("UPDATE doctor_slots SET is_booked = 1 WHERE id = :new_id");
    $updNew->bindParam(':new_id', $new_slot_id, PDO::PARAM_INT);
    $updNew->execute();

    // Update appointment
    $updAppt = $conn->prepare("
        UPDATE appointments 
        SET slot_id = :new_slot_id, updated_at = NOW() 
        WHERE id = :appt_id
    ");
    $updAppt->bindParam(':new_slot_id', $new_slot_id, PDO::PARAM_INT);
    $updAppt->bindParam(':appt_id', $appointment_id, PDO::PARAM_INT);
    $updAppt->execute();

    // Get patient and doctor details for email
    $stmtPatient = $conn->prepare("
        SELECT u.email, u.name 
        FROM users u 
        WHERE u.id = :patient_id
    ");
    $stmtPatient->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmtPatient->execute();
    $patient = $stmtPatient->fetch(PDO::FETCH_ASSOC);

    $stmtDoctor = $conn->prepare("
        SELECT u.name as doctor_name 
        FROM doctors d 
        JOIN users u ON d.user_id = u.id 
        WHERE d.id = :doctor_id
    ");
    $stmtDoctor->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
    $stmtDoctor->execute();
    $doctor = $stmtDoctor->fetch(PDO::FETCH_ASSOC);

    $conn->commit();

    // Send reschedule confirmation email
    $emailSent = sendRescheduleConfirmationEmail(
        $patient['email'],
        $patient['name'],
        $doctor['doctor_name'],
        $old_slot_time,
        $new_slot_time
    );

    if ($emailSent) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Appointment rescheduled successfully. Confirmation email sent.'
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'message' => 'Appointment rescheduled successfully. (Email notification failed)'
        ]);
    }

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Reschedule error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: Please try again later.'
    ]);
}
?>