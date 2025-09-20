<?php
include 'auth_check.php';
authorize(['admin']);
include 'config/db.php';

$admin_id = $_SESSION['user_id'];

// Fetch current admin info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    die("Admin not found");
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Handle profile pic upload
    $profile_pic = $admin['profile_pic'] ?? 'assets/images/user_placeholder.png';
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['tmp_name'] != '') {
        $target_dir = "assets/uploads/profile_pics/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $file_ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('admin_') . '.' . $file_ext;
        $target_file = $target_dir . $file_name;
        if(move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)){
            $profile_pic = $target_file;
        }
    }

    // Prepare update query
    $params = [$name, $email, $phone, $profile_pic, $admin_id];
    $password_sql = '';
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $password_sql = ", password = ?";
        array_splice($params, 4, 0, [$hashed_password]);
    }

    $sql = "UPDATE users SET name=?, email=?, phone=?, profile_pic=? $password_sql WHERE id=?";
    $stmt = $conn->prepare($sql);

    try {
        $stmt->execute($params);
        $success = true;
        $_SESSION['user_name'] = $name; // Update session name

        // Re-fetch updated admin info to show new picture
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Profile</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body { font-family: Arial, sans-serif; background: #f8f9fa; margin:0; padding:0; }
.container { max-width: 700px; margin: 50px auto; background: linear-gradient(145deg,#ffffff,#e9f5ef); padding: 30px; border-radius: 12px; box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
h2 { text-align: center; color: #127137; margin-bottom: 25px; }
form label { display: block; margin-top: 15px; font-weight: bold; }
form input[type="text"], form input[type="email"], form input[type="password"], form input[type="file"] {
    width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; margin-top: 5px; box-sizing: border-box;
}
form button {
    margin-top: 20px; padding: 12px 20px; background-color: #127137; color: #fff; border:none; border-radius: 8px; cursor:pointer;
    transition: 0.3s;
}
form button:hover { background-color: #0e5d2c; }
.profile-pic { display:block; margin: 15px auto; width:120px; height:120px; object-fit:cover; border-radius:50%; border:2px solid #127137; }
</style>
</head>
<body>

<div class="container">
    <h2>Admin Profile</h2>
    <form method="POST" enctype="multipart/form-data" id="profileForm">
        <img src="<?php echo htmlspecialchars($admin['profile_pic'] ?? 'assets/images/user_placeholder.png'); ?>" alt="Profile Picture" class="profile-pic" id="profilePreview">
        <label>Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>

        <label>Phone</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($admin['phone']); ?>">

        <label>New Password <small>(Leave blank to keep current)</small></label>
        <input type="password" name="password">

        <label>Profile Picture</label>
        <input type="file" name="profile_pic" accept="image/*" onchange="previewImage(event)">

        <button type="submit">Update Profile</button>
    </form>
</div>

<script>
// Image preview
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function(){
        const output = document.getElementById('profilePreview');
        output.src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
}

// SweetAlert notifications
<?php if($success): ?>
Swal.fire({
    icon: 'success',
    title: 'Profile Updated',
    text: 'Your profile has been updated successfully!',
    confirmButtonColor: '#127137'
}).then(()=> {
    window.location.href = 'admin_dashboard.php';
});
<?php elseif($error): ?>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: '<?php echo addslashes($error); ?>',
    confirmButtonColor: '#127137'
});
<?php endif; ?>
</script>

</body>
</html>
