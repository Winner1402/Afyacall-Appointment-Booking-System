<?php
session_start();
include 'config\db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])){
    echo json_encode(['status'=>'error','message'=>'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'doctor';

$patient_id = $_POST['patient_id'] ?? null;
$title = $_POST['title'] ?? '';
$details = $_POST['details'] ?? '';

if($user_role == 'patient'){
    echo json_encode(['status'=>'error','message'=>'Patients cannot add history']);
    exit();
}
if(!$patient_id || !$title || !$details){
    echo json_encode(['status'=>'error','message'=>'All fields required']);
    exit();
}

$attachment_name = null;
if(isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0){
    $target_dir = "uploads/";
    if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $attachment_name = time().'_'.basename($_FILES["attachment"]["name"]);
    move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_dir.$attachment_name);
}

$stmt = $conn->prepare("INSERT INTO medical_history (patient_id, doctor_id, title, details, attachment) VALUES (:patient_id, :doctor_id, :title, :details, :attachment)");
$stmt->bindParam(':patient_id', $patient_id);
$stmt->bindParam(':doctor_id', $user_id);
$stmt->bindParam(':title', $title);
$stmt->bindParam(':details', $details);
$stmt->bindParam(':attachment', $attachment_name);

if($stmt->execute()){
    echo json_encode(['status'=>'success','message'=>'Medical history added']);
} else {
    echo json_encode(['status'=>'error','message'=>'Failed to add history']);
}
?>
