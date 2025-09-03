<?php
session_start();
include 'config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['doctor_id']) || !isset($_POST['appointment_date'])) {
    echo json_encode([]);
    exit();
}

$doctor_id = (int)$_POST['doctor_id'];
$date = $_POST['appointment_date'];

// Validate date is not in the past
if (strtotime($date) < strtotime(date('Y-m-d'))) {
    echo json_encode([]);
    exit();
}

try {
    // Get available slots for the selected doctor and date
    $stmt = $conn->prepare("
        SELECT id as slot_id, TIME_FORMAT(slot_datetime, '%H:%i') as time 
        FROM doctor_slots 
        WHERE doctor_id = :doctor_id 
        AND DATE(slot_datetime) = :appointment_date 
        AND is_booked = 0
        AND slot_datetime > NOW() -- Only future slots
        ORDER BY slot_datetime
    ");
    $stmt->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
    $stmt->bindParam(':appointment_date', $date);
    $stmt->execute();

    $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($slots);
    
} catch (Exception $e) {
    error_log("get_slots error: " . $e->getMessage());
    echo json_encode([]);
}