<?php
session_start();
include 'config/db.php';
header('Content-Type: application/json');

if(!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='doctor'){
    echo json_encode(['status'=>'error','message'=>'Unauthorized access']);
    exit;
}

$slot_id = $_POST['slot_id'] ?? '';
if(!$slot_id){
    echo json_encode(['status'=>'error','message'=>'Invalid slot']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Ensure doctor owns this slot
$stmt = $conn->prepare("
    SELECT ds.id FROM doctor_slots ds
    JOIN doctors d ON ds.doctor_id = d.id
    WHERE ds.id = :slot_id AND d.user_id = :user_id
");
$stmt->execute([':slot_id'=>$slot_id, ':user_id'=>$user_id]);
if(!$stmt->fetch(PDO::FETCH_ASSOC)){
    echo json_encode(['status'=>'error','message'=>'Slot not found or not yours']);
    exit;
}

try{
    $stmt = $conn->prepare("DELETE FROM doctor_slots WHERE id=:id");
    $stmt->execute([':id'=>$slot_id]);
    echo json_encode(['status'=>'success','message'=>'Slot deleted successfully']);
}catch(PDOException $e){
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
?>
