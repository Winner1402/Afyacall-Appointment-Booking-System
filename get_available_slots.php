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
$exclude_slot = isset($_POST['exclude_slot']) ? (int)$_POST['exclude_slot'] : 0;

try {
    $stmt = $conn->prepare("
        SELECT id as slot_id, TIME_FORMAT(slot_datetime, '%H:%i') as time
        FROM doctor_slots
        WHERE doctor_id = :doctor_id
        AND DATE(slot_datetime) = :appointment_date
        AND (status = 0 OR id = :exclude_slot)
        AND slot_datetime > DATE_ADD(NOW(), INTERVAL 24 HOUR)
        ORDER BY slot_datetime
    ");
    $stmt->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
    $stmt->bindParam(':appointment_date', $date);
    $stmt->bindParam(':exclude_slot', $exclude_slot, PDO::PARAM_INT);
    $stmt->execute();

    $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($slots);

} catch (Exception $e) {
    error_log("get_available_slots error: " . $e->getMessage());
    echo json_encode([]);
}
?>
