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

    // === SLOT VALIDATION BLOCK ===
    // Check if slot is available
    $stmt = $conn->prepare("SELECT id, is_booked, doctor_id, slot_datetime FROM doctor_slots WHERE id = :slot_id FOR UPDATE");
    $stmt->bindParam(':slot_id', $slot_id, PDO::PARAM_INT);
    $stmt->execute();
    $slot = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$slot) {
        $conn->rollBack();
        echo json_encode(['status'=>'error','message'=>'Selected slot not found.']);
        exit();
    }

    if ((int)$slot['is_booked'] === 1) {
        $conn->rollBack();
        echo json_encode(['status'=>'error','message'=>'Selected slot is already booked.']);
        exit();
    }

    if ((int)$slot['doctor_id'] !== $doctor_id) {
        $conn->rollBack();
        echo json_encode(['status'=>'error','message'=>'Invalid slot for selected doctor.']);
        exit();
    }

    // Check if appointment is at least 24 hours in the future
    $slot_time = strtotime($slot['slot_datetime']);
    if ($slot_time - time() < 24*60*60) {
        $conn->rollBack();
        echo json_encode(['status'=>'error','message'=>'Appointment must be booked at least 24 hours in advance.']);
        exit();
    }
    // === END SLOT VALIDATION BLOCK ===

    // Get patient email and name
    $stmtPatient = $conn->prepare("SELECT email, name FROM users WHERE id = :patient_id");
    $stmtPatient->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmtPatient->execute();
    $patient = $stmtPatient->fetch(PDO::FETCH_ASSOC);

    // Get doctor details
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

    // === SLOT BOOKING AND APPOINTMENT CREATION BLOCK ===
    // Book the slot
    $updateSlot = $conn->prepare("UPDATE doctor_slots SET is_booked = 1 WHERE id = :slot_id");
    $updateSlot->bindParam(':slot_id', $slot_id, PDO::PARAM_INT);
    $updateSlot->execute();

    // Create appointment
    $insertAppt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, slot_id, status, created_at) VALUES (:patient_id, :doctor_id, :slot_id, 'pending', NOW())");
    $insertAppt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $insertAppt->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
    $insertAppt->bindParam(':slot_id', $slot_id, PDO::PARAM_INT);
    $insertAppt->execute();

    $appointment_id = $conn->lastInsertId();
    // === END SLOT BOOKING BLOCK ===

    $conn->commit();

    // Send confirmation email using the separated function
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