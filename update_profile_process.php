<?php
session_start();
include 'config\db.php'; // your PDO connection

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Sanitize and validate inputs
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$address = trim($_POST['address']);
$gender = trim($_POST['gender']);

// Basic validation
if(empty($name) || empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Name and Email are required']);
    exit();
}

// Check if email is valid
if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit();
}

try {
    // Update query
    $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email, phone = :phone, address = :address, gender = :gender WHERE id = :id");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

    if($stmt->execute()) {
        // Update session username if changed
        $_SESSION['user_name'] = $name;

        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update profile.']);
    }

} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: '.$e->getMessage()]);
}
?>
