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

    // Get current appointment
    $stmt = $conn->prepare("
        SELECT a.id, a.slot_id, a.doctor_id, a.status, ds.slot_datetime AS old_slot_time 
        FROM appointments a 
        JOIN doctor_slots ds ON a.slot_id = ds.id 
        WHERE a.id=:id AND a.patient_id=:pid FOR UPDATE
    ");
    $stmt->execute([':id'=>$appointment_id, ':pid'=>$patient_id]);
    $appt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appt) {
        $conn->rollBack();
        exit(json_encode(['status'=>'error','message'=>'Appointment not found.']));
    }

    if ($appt['status'] != 'pending') {
        $conn->rollBack();
        exit(json_encode(['status'=>'error','message'=>'Only pending appointments can be rescheduled.']));
    }

    $old_slot_id = (int)$appt['slot_id'];
    $doctor_id = (int)$appt['doctor_id'];
    $old_slot_time = $appt['old_slot_time'];

    // Restrict reschedule within 24 hours
    if (strtotime($old_slot_time) - time() < 24*60*60) {
        $conn->rollBack();
        exit(json_encode(['status'=>'error','message'=>'Cannot reschedule within 24 hours.']));
    }

    // Validate new slot (only future slots, not current slot)
    $stmt2 = $conn->prepare("
        SELECT id, status, doctor_id, slot_datetime 
        FROM doctor_slots 
        WHERE id=:sid 
          AND doctor_id=:did 
          AND status=0 
          AND slot_datetime > NOW() 
          AND id != :current_slot 
        FOR UPDATE
    ");
    $stmt2->execute([
        ':sid'=>$new_slot_id,
        ':did'=>$doctor_id,
        ':current_slot'=>$old_slot_id
    ]);
    $newSlot = $stmt2->fetch(PDO::FETCH_ASSOC);

    if (!$newSlot) {
        $conn->rollBack();
        exit(json_encode(['status'=>'error','message'=>'Invalid slot selected. Choose a future available slot.']));
    }

    // Free old slot
    $conn->prepare("UPDATE doctor_slots SET status=0 WHERE id=:old_id")
         ->execute([':old_id'=>$old_slot_id]);

    // Book new slot
    $conn->prepare("UPDATE doctor_slots SET status=1 WHERE id=:new_id")
         ->execute([':new_id'=>$new_slot_id]);

    // Update appointment
    $conn->prepare("UPDATE appointments SET slot_id=:new_slot_id, updated_at=NOW() WHERE id=:appt_id")
         ->execute([':new_slot_id'=>$new_slot_id, ':appt_id'=>$appointment_id]);

    // Get patient + doctor details
    $stmtPatient = $conn->prepare("SELECT email, name FROM users WHERE id=:pid");
    $stmtPatient->execute([':pid'=>$patient_id]);
    $patient = $stmtPatient->fetch(PDO::FETCH_ASSOC);

    $stmtDoctor = $conn->prepare("SELECT u.name as doctor_name FROM doctors d JOIN users u ON d.user_id=u.id WHERE d.id=:did");
    $stmtDoctor->execute([':did'=>$doctor_id]);
    $doctor = $stmtDoctor->fetch(PDO::FETCH_ASSOC);

    $conn->commit();

    // Send confirmation email
    $emailSent = sendRescheduleConfirmationEmail(
        $patient['email'],
        $patient['name'],
        $doctor['doctor_name'],
        $old_slot_time,
        $newSlot['slot_datetime']
    );

    echo json_encode([
        'status'=>'success',
        'message'=>'Appointment rescheduled successfully.' . ($emailSent ? ' Confirmation email sent.' : ' Email failed.')
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Reschedule error: ".$e->getMessage());
    echo json_encode(['status'=>'error','message'=>'Server error: Please try again later.']);
}
?>
