<?php
session_start();
include 'config/db.php';
header('Content-Type: application/json');

$response = [];

if(!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='admin'){
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';

try{
    if($action==='add'){
        $name = trim($_POST['name']);
        $desc = trim($_POST['description']);

        // Check duplicate
        $stmtCheck = $conn->prepare("SELECT id FROM specialties WHERE name=:name");
        $stmtCheck->execute([':name'=>$name]);
        if($stmtCheck->rowCount()>0){
            echo json_encode(['status'=>'error','message'=>'Specialty already exists']);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO specialties (name, description) VALUES (:name,:description)");
        $stmt->execute([':name'=>$name, ':description'=>$desc]);
        echo json_encode(['status'=>'success','message'=>'Specialty added successfully']);

    } elseif($action==='edit'){
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        $desc = trim($_POST['description']);

        // Optional: Check duplicate name excluding current
        $stmtCheck = $conn->prepare("SELECT id FROM specialties WHERE name=:name AND id!=:id");
        $stmtCheck->execute([':name'=>$name, ':id'=>$id]);
        if($stmtCheck->rowCount()>0){
            echo json_encode(['status'=>'error','message'=>'Specialty name already exists']);
            exit();
        }

        $stmt = $conn->prepare("UPDATE specialties SET name=:name, description=:description WHERE id=:id");
        $stmt->execute([':name'=>$name, ':description'=>$desc, ':id'=>$id]);
        echo json_encode(['status'=>'success','message'=>'Specialty updated successfully']);

    } elseif($action==='delete'){
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM specialties WHERE id=:id");
        $stmt->execute([':id'=>$id]);
        echo json_encode(['status'=>'success','message'=>'Specialty deleted successfully']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Invalid action']);
    }
} catch(PDOException $e){
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
exit();
