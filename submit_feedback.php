<?php
header('Content-Type: application/json');
include 'config/db.php';

try {
    // Required fields
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $message = trim($_POST['message']);
    $rating = intval($_POST['rating']); // Ensure rating is an integer

    if (!$name || !$email || !$phone || !$message || !$rating) {
        throw new Exception("All fields are required.");
    }

    // Handle optional profile picture
    $profile_pic = 'assets/img/profile-placeholder.png'; // default
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('profile_', true) . "." . $ext;
        $upload_dir = 'uploads/feedback/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $destination = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination)) {
            $profile_pic = $destination;
        }
    }
// Insert into database
$stmt = $conn->prepare("INSERT INTO feedback (name, email, phone, message, profile_pic, rating, status, created_at) 
                        VALUES (:name, :email, :phone, :message, :profile_pic, :rating, 'unapproved', NOW())");

$stmt->execute([
    ':name' => $name,
    ':email' => $email,
    ':phone' => $phone,
    ':message' => $message,
    ':profile_pic' => $profile_pic,
    ':rating' => $rating
]);


    echo json_encode(['status' => 'success', 'message' => 'Thank you for your feedback!']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
