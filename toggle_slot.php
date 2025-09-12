<?php
session_start();
include 'config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$slot_id = $_POST['slot_id'] ?? null;
$doctor_user_id = $_SESSION['user_id'];

if (!$slot_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid slot ID']);
    exit;
}

// Get doctor_id from doctors table
$doctor_user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id FROM doctors WHERE user_id = :user_id");
$stmt->execute([':user_id' => $doctor_user_id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$doctor){
    echo json_encode(['status'=>'error','message'=>'Doctor not found']);
    exit;
}
$doctor_id = $doctor['id'];

// Get the current slot
$stmt = $conn->prepare("SELECT * FROM doctor_slots WHERE id = :id AND doctor_id = :doctor_id");
$stmt->execute([':id' => $slot_id, ':doctor_id' => $doctor_id]);
$slot = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$slot) {
    echo json_encode(['status' => 'error', 'message' => 'Slot not found']);
    exit;
}

// Toggle status: 0 = available, 1 = unavailable/booked
$new_status = $slot['status'] == 0 ? 1 : 0;

$stmt = $conn->prepare("UPDATE doctor_slots SET status = :status WHERE id = :id");
$stmt->execute([
    ':status' => $new_status,
    ':id' => $slot_id
]);

echo json_encode([
    'status' => 'success',
    'message' => $new_status == 0 ? 'Slot marked as Available' : 'Slot marked as Unavailable',
    'new_status' => $new_status
]);
