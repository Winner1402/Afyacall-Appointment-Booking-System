<?php
session_start();
include 'config/db.php';
header('Content-Type: application/json');

if(!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='doctor'){
    echo json_encode(['status'=>'error','message'=>'Unauthorized access']);
    exit;
}

$user_id = $_SESSION['user_id'];
$slot_date = $_POST['slot_date'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';

if(!$slot_date || !$start_time || !$end_time){
    echo json_encode(['status'=>'error','message'=>'All fields are required']);
    exit;
}

// Prevent past date/time
if(strtotime($slot_date.' '.$start_time) < time()){
    echo json_encode(['status'=>'error','message'=>'Cannot select past date/time']);
    exit;
}

// Get doctor_id
$stmt = $conn->prepare("SELECT id FROM doctors WHERE user_id = :user_id");
$stmt->execute([':user_id'=>$user_id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$doctor){
    echo json_encode(['status'=>'error','message'=>'Doctor not found']);
    exit;
}
$doctor_id = $doctor['id'];

$slot_datetime = $slot_date.' '.$start_time;
$end_datetime = $slot_date.' '.$end_time;

if(strtotime($slot_datetime) >= strtotime($end_datetime)){
    echo json_encode(['status'=>'error','message'=>'End time must be after start time']);
    exit;
}

try{
$stmt = $conn->prepare("INSERT INTO doctor_slots (doctor_id, slot_datetime, end_datetime, status) 
                        VALUES (:doctor_id,:slot_datetime,:end_datetime,0)");
    $stmt->execute([
        ':doctor_id'=>$doctor_id,
        ':slot_datetime'=>$slot_datetime,
        ':end_datetime'=>$end_datetime
    ]);
    echo json_encode(['status'=>'success','message'=>'Slot added successfully']);
}catch(PDOException $e){
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
?>
