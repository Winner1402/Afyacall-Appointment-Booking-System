<?php
session_start();
include 'config/db.php';
header('Content-Type: application/json');

$id = $_POST['id'] ?? 0;

// Get user_id of the doctor
$stmt = $conn->prepare("SELECT user_id FROM doctors WHERE id=:id");
$stmt->execute([':id'=>$id]);
$user_id = $stmt->fetchColumn();

if($user_id){
    // Delete from doctors
    $stmt = $conn->prepare("DELETE FROM doctors WHERE id=:id");
    $stmt->execute([':id'=>$id]);

    // Delete from users
    $stmt = $conn->prepare("DELETE FROM users WHERE id=:user_id");
    $stmt->execute([':user_id'=>$user_id]);

    echo json_encode(['status'=>'success','message'=>'Doctor deleted successfully']);
} else {
    echo json_encode(['status'=>'error','message'=>'Doctor not found']);
}
?>
