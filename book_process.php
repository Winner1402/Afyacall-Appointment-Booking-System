<?php
session_start();
include 'config/db.php';
require_once 'includes/email_functions.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error','message'=>'Please login first.']);
    exit();
}

if (!isset($_POST['doctor_id'], $_POST['appointment_date'], $_POST['slot_id'])) {
    echo json_encode(['status'=>'error','message'=>'Incomplete data.']);
    exit();
}

$patient_id = $_SESSION['user_id'];
$doctor_id = (int)$_POST['doctor_id'];
$slot_id = (int)$_POST['slot_id'];
$appointment_date = $_POST['appointment_date'];

try {
    $conn->beginTransaction();

    // === SLOT VALIDATION ===
    $stmt = $conn->prepare("
        SELECT id, slot_datetime, end_datetime 
        FROM doctor_slots 
        WHERE id = :slot_id AND doctor_id = :doctor_id 
          AND status = 0 AND slot_datetime >= NOW()
        LIMIT 1
    ");
    $stmt->execute([
        ':slot_id' => $slot_id,
        ':doctor_id' => $doctor_id
    ]);
    $slot = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$slot) {
        $conn->rollBack();
        echo json_encode(['status'=>'error','message'=>'Selected slot not found or unavailable.']);
        exit();
    }

    // Ensure slot is at least 24 hours in the future
    $slot_time = strtotime($slot['slot_datetime']);
    if ($slot_time - time() < 24*60*60) {
        $conn->rollBack();
        echo json_encode(['status'=>'error','message'=>'Appointment must be booked at least 24 hours in advance.']);
        exit();
    }

    // === FETCH PATIENT AND DOCTOR DETAILS ===
    $stmtPatient = $conn->prepare("SELECT email, name FROM users WHERE id = :patient_id");
    $stmtPatient->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmtPatient->execute();
    $patient = $stmtPatient->fetch(PDO::FETCH_ASSOC);

    $stmtDoctor = $conn->prepare("
        SELECT u.name as doctor_name, s.name as specialty 
        FROM doctors d 
        JOIN users u ON d.user_id = u.id 
        JOIN specialties s ON d.specialty_id = s.id 
        WHERE d.id = :doctor_id
    ");
    $stmtDoctor->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
    $stmtDoctor->execute();
    $doctor = $stmtDoctor->fetch(PDO::FETCH_ASSOC);

    // === BOOK THE SLOT ===
    $updateSlot = $conn->prepare("UPDATE doctor_slots SET status = 1 WHERE id = :slot_id");
    $updateSlot->bindParam(':slot_id', $slot_id, PDO::PARAM_INT);
    $updateSlot->execute();

    // Create appointment
    $insertAppt = $conn->prepare("
        INSERT INTO appointments (patient_id, doctor_id, slot_id, status, created_at)
        VALUES (:patient_id, :doctor_id, :slot_id, 'pending', NOW())
    ");
    $insertAppt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $insertAppt->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
    $insertAppt->bindParam(':slot_id', $slot_id, PDO::PARAM_INT);
    $insertAppt->execute();

    $appointment_id = $conn->lastInsertId();
    $conn->commit();

    // Send confirmation email
    $emailSent = sendAppointmentConfirmationEmail(
        $patient['email'],
        $patient['name'],
        $doctor['doctor_name'],
        $doctor['specialty'],
        $slot['slot_datetime'],
        $appointment_id
    );

    if ($emailSent) {
        echo json_encode(['status'=>'success','message'=>'Appointment booked successfully! Confirmation email sent.']);
    } else {
        echo json_encode(['status'=>'success','message'=>'Appointment booked successfully! (Email notification failed)']);
    }

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Booking error: " . $e->getMessage());
    echo json_encode(['status'=>'error','message'=>'Server error: Please try again later.']);
}
?>
