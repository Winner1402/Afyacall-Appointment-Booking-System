<?php
session_start();
include 'config/db.php';
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD']=='POST'){
    $user_id = $_POST['user_id'];
    $doctor_id = $_POST['doctor_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $specialty_id = $_POST['specialty_id'];

    // Update users table
    $stmt = $conn->prepare("UPDATE users SET name=:name, email=:email, phone=:phone WHERE id=:id");
    $stmt->execute([
        ':name'=>$name,
        ':email'=>$email,
        ':phone'=>$phone,
        ':id'=>$user_id
    ]);

    // Update doctors table
    $stmt = $conn->prepare("UPDATE doctors SET specialty_id=:specialty_id WHERE id=:id");
    $stmt->execute([
        ':specialty_id'=>$specialty_id,
        ':id'=>$doctor_id
    ]);

    echo json_encode(['status'=>'success','message'=>'Doctor updated successfully']);
}
?>
