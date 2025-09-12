<?php
include 'auth_check.php';
authorize(['admin','doctor']);
include 'config/db.php';
header('Content-Type: application/json');

$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';

if (!$id || !in_array($status, ['pending','accepted','rejected'])){
    echo json_encode(['status'=>'error','message'=>'Invalid request']);
    exit();
}

$stmt = $conn->prepare("UPDATE appointments SET status=:status WHERE id=:id");
$stmt->execute([':status'=>$status, ':id'=>$id]);

if($stmt->rowCount()){
    echo json_encode(['status'=>'success','message'=>'Appointment status updated']);
} else {
    echo json_encode(['status'=>'error','message'=>'Appointment not found or already updated']);
}
