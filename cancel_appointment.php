<?php
session_start();
include 'config/db.php';
require_once 'includes/email_functions.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'], $_POST['appointment_id'])){
    echo json_encode(['status'=>'error','message'=>'Invalid request']);
    exit();
}

$patient_id = $_SESSION['user_id'];
$appointment_id = (int)$_POST['appointment_id'];

try {
    $conn->beginTransaction();

    // Fetch appointment + slot details
    $stmt = $conn->prepare("
        SELECT 
            a.slot_id, 
            ds.slot_datetime, 
            a.status,
            a.doctor_id,
            u.email as patient_email,
            u.name as patient_name,
            doc_u.name as doctor_name
        FROM appointments a
        JOIN doctor_slots ds ON a.slot_id = ds.id
        JOIN users u ON a.patient_id = u.id
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users doc_u ON d.user_id = doc_u.id
        WHERE a.id = :appointment_id AND a.patient_id = :patient_id
        FOR UPDATE
    ");
    $stmt->execute([':appointment_id'=>$appointment_id, ':patient_id'=>$patient_id]);
    $appt = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$appt){
        $conn->rollBack();
        echo json_encode(['status'=>'error','message'=>'Appointment not found']);
        exit();
    }

    $now = time();
    $slot_time = strtotime($appt['slot_datetime']);
    $cutoff = 24*60*60;

    if($slot_time - $now < $cutoff){
        $conn->rollBack();
        echo json_encode(['status'=>'error','message'=>'Cannot cancel within 24 hours of appointment']);
        exit();
    }

    if($appt['status'] != 'pending'){
        $conn->rollBack();
        echo json_encode(['status'=>'error','message'=>'Only pending appointments can be cancelled']);
        exit();
    }

    // Update appointment status
    $conn->prepare("UPDATE appointments SET status='cancelled', updated_at=NOW() WHERE id=:appointment_id")
         ->execute([':appointment_id'=>$appointment_id]);

    // Free up the slot
    $conn->prepare("UPDATE doctor_slots SET status=0 WHERE id=:slot_id")
         ->execute([':slot_id'=>$appt['slot_id']]);

    $conn->commit();

    // Send cancellation email
    $emailSent = sendCancellationConfirmationEmail(
        $appt['patient_email'],
        $appt['patient_name'],
        $appt['doctor_name'],
        $appt['slot_datetime']
    );

    echo json_encode([
        'status'=>'success',
        'message'=>'Appointment cancelled successfully.' . ($emailSent ? ' Confirmation email sent.' : ' Email notification failed.')
    ]);

} catch (Exception $e){
    $conn->rollBack();
    error_log("Cancellation error: " . $e->getMessage());
    echo json_encode(['status'=>'error','message'=>'Server error. Please try again later.']);
}
?>
