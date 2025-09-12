<?php
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get POST data
    $patient_id = intval($_POST['id']);
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];

    // Update query
    $update_sql = "UPDATE users SET name = :name, email = :email, phone = :phone, gender = :gender WHERE id = :id";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bindParam(':name', $name);
    $update_stmt->bindParam(':email', $email);
    $update_stmt->bindParam(':phone', $phone);
    $update_stmt->bindParam(':gender', $gender);
    $update_stmt->bindParam(':id', $patient_id, PDO::PARAM_INT);

    // Determine if update succeeded
    $success = $update_stmt->execute();
} else {
    die("Invalid request.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Patient</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
<?php if ($success): ?>
    Swal.fire({
        icon: 'success',
        title: 'Updated!',
        text: 'Patient "<?php echo addslashes($name); ?>" updated successfully.',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        window.location.href = 'admin_manage_patients.php';
    });
<?php else: ?>
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Error updating patient. Please try again!',
        confirmButtonText: 'OK'
    }).then(() => {
        window.history.back();
    });
<?php endif; ?>
</script>
</body>
</html>
