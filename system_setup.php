<?php
include 'auth_check.php';
authorize(['admin']);
include 'config/db.php';

// Fetch current settings
$stmt = $conn->query("SELECT * FROM system_settings ORDER BY id DESC LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>System Setup</title>
<link rel="stylesheet" href="assets/css/patient_dashboard.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
.container {
    max-width: 900px;       /* or whatever width you want */
    margin: 40px auto;
    padding: 25px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    min-height: 950px;       /* ensures it stretches with content */
    overflow: visible;       /* clears floated elements inside */
}
h2 { text-align:center; color:#127137; margin-bottom:20px;}
.form-group { margin-bottom:15px;}
label { display:block; font-weight:bold; margin-bottom:5px;}
input, select { width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; box-sizing:border-box; }
button { width:100%; padding:12px; background:#127137; color:#fff; border:none; border-radius:8px; font-weight:bold; cursor:pointer; transition:0.3s;}
button:hover { background:#0e5d2c; }
</style>
</head>
<body>

<header class="page-header">
    <img src="assets/images/logo.png" alt="Logo" class="logo">
    <h1><?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
     <button type="button" onclick="window.history.back();" style="padding:10px 15px; background:#127137; color:#fff; width: 100px; border:none; border-radius:6px; cursor:pointer;">
    Back
</button>

</header>

<div class="container">
    <h2>System Setup</h2>
    <form id="system-setup-form" enctype="multipart/form-data">
        <div class="form-group">
            <label>System Name</label>
            <input type="text" name="system_name" value="<?php echo htmlspecialchars($settings['system_name'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label>Logo</label>
            <input type="file" name="logo">
            <?php if(!empty($settings['logo'])): ?>
                <br><img src="uploads/<?php echo htmlspecialchars($settings['logo']); ?>" width="100">
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label>Contact Email</label>
            <input type="email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Contact Phone</label>
            <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Appointment Duration (minutes)</label>
            <input type="number" name="appointment_duration" value="<?php echo $settings['appointment_duration'] ?? 30; ?>" required>
        </div>
        <div class="form-group">
            <label>Working Hours</label>
            <input type="text" name="working_hours" value="<?php echo htmlspecialchars($settings['working_hours'] ?? '08:00-17:00'); ?>" required>
        </div>
        <div class="form-group">
            <label>Max Appointments Per Day</label>
            <input type="number" name="max_appointments_per_day" value="<?php echo $settings['max_appointments_per_day'] ?? 10; ?>" required>
        </div>
        <div class="form-group">
            <label>File Upload Limit (MB)</label>
            <input type="number" name="file_upload_limit_mb" value="<?php echo $settings['file_upload_limit_mb'] ?? 5; ?>" required>
        </div>
        <div class="form-group">
            <label>Theme Color</label>
            <input type="color" name="theme_color" value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#127137'); ?>">
        </div>
        <button type="submit">Save Settings</button>
    </form>
</div>

<script>
$('#system-setup-form').on('submit', function(e){
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        url:'system_setup_process.php',
        type:'POST',
        data: formData,
        processData:false,
        contentType:false,
        dataType:'json',
        success: function(resp){
            if(resp.status==='success'){
                Swal.fire('Saved', resp.message, 'success').then(()=> location.reload());
            } else {
                Swal.fire('Error', resp.message, 'error');
            }
        },
        error: function(xhr,status,error){
            Swal.fire('Error','Something went wrong: '+error,'error');
        }
    });
});
</script>

</body>
</html>
