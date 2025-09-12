<?php
session_start();
include 'config/db.php';

header('Content-Type: application/json'); // Important!

$response = [];

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin'){
    $response['status'] = 'error';
    $response['message'] = 'Unauthorized access';
    echo json_encode($response);
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $specialty_id = $_POST['specialty_id'];
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $conn->prepare("INSERT INTO users (name,email,phone,password,role,force_password_change)
                                VALUES (:name,:email,:phone,:password,'doctor',1)");
        $stmt->execute([
            ':name'=>$name,
            ':email'=>$email,
            ':phone'=>$phone,
            ':password'=>$hashed_password
        ]);

        $user_id = $conn->lastInsertId();

        $stmt2 = $conn->prepare("INSERT INTO doctors (user_id, specialty_id) VALUES (:user_id,:specialty_id)");
        $stmt2->execute([
            ':user_id'=>$user_id,
            ':specialty_id'=>$specialty_id
        ]);

        $response['status'] = 'success';
        $response['message'] = "Doctor added successfully! Initial password: {$password}";

    } catch(PDOException $e){
        $response['status'] = 'error';
        $response['message'] = $e->getMessage();
    }

} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
exit();
