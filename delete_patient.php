<?php
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = intval($_POST['id']);

    $sql = "DELETE FROM users WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $patient_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Patient deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error deleting patient"]);
    }
    exit;
}
?>
