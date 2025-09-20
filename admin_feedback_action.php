<?php
session_start();
include 'config/db.php';
header('Content-Type: application/json');

// Check admin
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'){
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit();
}

// Validate input
if(!isset($_POST['id'], $_POST['action'])){
    echo json_encode(['status'=>'error','message'=>'Invalid parameters']);
    exit();
}

$id = intval($_POST['id']);
$action = $_POST['action'];

// Handle delete
if($action === 'delete'){
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id = :id");
    if($stmt->execute([':id'=>$id])){
        echo json_encode(['status'=>'success','message'=>'Feedback deleted successfully']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to delete feedback']);
    }
    exit;
}

// Handle approve/unapprove
if($action === 'approve' || $action === 'unapprove'){
    $status = $action === 'approve' ? 'approved' : 'unapproved';
    $stmt = $conn->prepare("UPDATE feedback SET status=:status WHERE id=:id");
    if($stmt->execute([':status'=>$status, ':id'=>$id])){
        echo json_encode(['status'=>'success','message'=>"Feedback marked as $status"]);
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to update status']);
    }
    exit;
}

// Invalid action
echo json_encode(['status'=>'error','message'=>'Unknown action']);
