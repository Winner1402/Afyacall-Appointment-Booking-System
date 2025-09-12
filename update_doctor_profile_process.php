<?php
session_start();
include 'config/db.php';

// ✅ Check if logged in AND is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_SESSION['user_id']; // doctor updates only own profile
    $name      = trim($_POST['name']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $address   = trim($_POST['address']);
    $gender    = trim($_POST['gender']);

    try {
        $stmt = $conn->prepare("UPDATE users 
                                SET name = :name, email = :email, phone = :phone, 
                                    address = :address, gender = :gender 
                                WHERE id = :id AND role = 'doctor'");

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':id', $doctor_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update profile.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
