<?php
include 'auth_check.php';
authorize(['admin']);
include 'config/db.php';

$doctor_id = $_GET['id'] ?? 0;

// Fetch doctor info
$stmt = $conn->prepare("
    SELECT d.id AS doctor_id, u.id AS user_id, u.name, u.email, u.phone, d.specialty_id
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    WHERE d.id=:id
");
$stmt->execute([':id'=>$doctor_id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch specialties for dropdown
$specialties = $conn->query("SELECT * FROM specialties")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Doctor</title>
<link rel="stylesheet" href="assets\css\patient_dashboard.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
<style>
/* Form container */
 
.main-content {
    max-width: 600px;
    margin: 40px auto !important;
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Title */
.main-content h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #127137;
    font-size: 24px;
}

/* Form groups */
.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 6px;
    color: #333;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 15px;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #127137;
    outline: none;
}

/* Button styling */
button[type="submit"] {
    display: block;
    width: 100%;
    padding: 12px;
    background: #127137;
    color: #fff;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s;
}

button[type="submit"]:hover {
    background: #0e5d2c;
}
 
</style>
</head>
<body>

<header class="page-header">
    <img src="assets/images/logo.jpeg" alt="AfyaCall Logo" class="logo">
    <h1><?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
    
</header>

<div class="container">
    <main class="main-content">
        <h2>Edit Doctor</h2>
        <form id="edit-doctor-form">
            <input type="hidden" name="user_id" value="<?php echo $doctor['user_id']; ?>">
            <input type="hidden" name="doctor_id" value="<?php echo $doctor['doctor_id']; ?>">
            
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($doctor['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($doctor['phone']); ?>">
            </div>
            <div class="form-group">
                <label>Specialty</label>
                <select name="specialty_id" required>
                    <?php foreach($specialties as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php if($s['id']==$doctor['specialty_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($s['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Update Doctor</button>
        </form>
        
    </main>
</div>

<script>
$('#edit-doctor-form').on('submit', function(e){
    e.preventDefault();
    $.ajax({
        url:'edit_doctor_process.php',
        type:'POST',
        data: $(this).serialize(),
        dataType:'json',
        success: function(resp){
            if(resp.status==='success'){
                Swal.fire('Updated', resp.message, 'success').then(()=> window.location='manage_doctors.php');
            } else {
                Swal.fire('Error', resp.message,'error');
            }
        },
        error:function(xhr,status,error){
            Swal.fire('Error','Something went wrong: '+error,'error');
        }
    });
});
</script>
</body>
</html>
