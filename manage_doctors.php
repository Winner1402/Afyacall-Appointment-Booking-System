<?php
include 'auth_check.php';
authorize(['admin']);
include 'config/db.php';

// Fetch all doctors with specialty
$doctors = $conn->query("
    SELECT d.id, u.name AS doctor_name, u.email, u.phone, s.name AS specialty_name
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    JOIN specialties s ON d.specialty_id = s.id
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch specialties for dropdown
$specialties = $conn->query("SELECT * FROM specialties")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Doctors</title>
<link rel="stylesheet" href="assets/css/patient_dashboard.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
/* Table styling same as dashboard */
.table-wrapper { 
    overflow-x: auto; 
    margin-top: 20px;  
}

.history-table, 
.history-table th, 
.history-table td { 
    border: 1px solid #ccc; 
    border-collapse: collapse; 
    padding: 10px; 
    text-align: left; 
}

.history-table { 
    width: 100%; 
}

.history-table th { 
    background-color: #127137; 
    color: #fff; 
}

.history-table tr:nth-child(even) { 
    background-color: #f9f9f9; 
}

.history-table tr:hover { 
    background-color: #e6f0ea; 
}

/* Add form wrapper */
.add-form { 
    background: #fff; 
    padding: 20px; 
    border-radius: 10px; 
    margin-bottom: 20px;  
    box-shadow: 0 2px 8px rgba(0,0,0,0.1); 
    max-width: 600px;          /* prevent over-stretch */
    margin-left: auto;
    margin-right: auto;        /* center on page */
    box-sizing: border-box;    /* keep padding inside */
    overflow: hidden;          /* ensures children don't overflow */
}

.add-form h3 { 
    margin-top: 0; 
    margin-bottom: 15px; 
    font-size: 20px;
    color: #127137;
}

.add-form .form-group { 
    margin-bottom: 15px; 
}

.add-form label { 
    font-weight: bold; 
    display: block; 
    margin-bottom: 5px; 
}

.add-form input, 
.add-form select { 
    width: 100%; 
    padding: 10px; 
    border-radius: 6px; 
    border: 1px solid #ccc; 
    box-sizing: border-box;   /* fix overflow */
}

.add-form button { 
    padding: 10px 15px; 
    background: #127137; 
    color: #fff; 
    border: none; 
    border-radius: 6px; 
    cursor: pointer; 
    width: 100%;               /* full-width button */
    font-weight: bold;
}

.add-form button:hover { 
    background: #0e5d2c; 
}
/* Action buttons */
.action-btn {
    display: inline-block;
    padding: 6px 12px;
    margin: 2px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: bold;
    color: #fff;
    transition: background 0.2s ease;
}

.action-edit {
    background-color: #127137;
}

.action-edit:hover {
    background-color: #0e5d2c;
}

.action-delete {
    background-color: #d9534f;
}

.action-delete:hover {
    background-color: #c9302c;
}
.btn-back {
    display: inline-block;
    padding: 10px 20px;
    background-color: #127137;
    color: #fff;
    margin-top: 20px;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.btn-back:hover {
    background-color: #0e5d2c;
    transform: translateY(-2px);
}
</style>
</head>
<body>

<header class="page-header">
    <img src="assets/images/logo.jpeg" alt="AfyaCall Logo" class="logo">
    <h1><?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
    <form action="logout.php" method="POST" style="margin:0;">
        <button type="submit" name="logout" class="logout-btn">Logout</button>
    </form>
</header>

<div class="container">
    <aside class="sidebar">
        <ul>
            <li><a href="admin_dashboard.php">Overview</a></li>
            <li><a href="manage_doctors.php" class="active">Manage Doctors</a></li>
            <li><a href="manage_specialties.php">Manage Specialties</a></li>
            <li><a href="system_setup.php">System Setup</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h2>Manage Doctors</h2>

        <!-- Add Doctor Form -->
        <div class="add-form">
            <h3>Add New Doctor</h3>
<form id="add-doctor-form">
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" name="name" id="name" required>
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
    </div>

    <div class="form-group">
        <label for="phone">Phone</label>
        <input type="text" name="phone" id="phone">
    </div>

    <div class="form-group">
        <label for="specialty_id">Specialty</label>
        <select name="specialty_id" id="specialty_id" required>
            <option value="">--Select Specialty--</option>
            <?php foreach($specialties as $s): ?>
                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="password">Initial Password</label>
        <input type="text" name="password" id="password" placeholder="Set initial password" required>
        <small>Doctor will be forced to change this on first login.</small>
    </div>

    <button type="submit">Add Doctor</button>
</form>

        </div>

        <!-- Doctors Table -->
        <div class="table-wrapper">
        <table class="history-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Specialty</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($doctors as $doc): ?>
                <tr>
                    <td><?php echo htmlspecialchars($doc['doctor_name']); ?></td>
                    <td><?php echo htmlspecialchars($doc['email']); ?></td>
                    <td><?php echo htmlspecialchars($doc['phone']); ?></td>
                    <td><?php echo htmlspecialchars($doc['specialty_name']); ?></td>
                    <td>
                      <a href="edit_doctor.php?id=<?php echo $doc['id']; ?>" 
   class="action-btn action-edit">Edit</a> 

<a href="#" class="action-btn action-delete delete-doctor" 
   data-id="<?php echo $doc['id']; ?>">Delete</a>

                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="admin_dashboard.php" class="btn-back">Back</a>
        </div>
    </main>
</div>

<script>
// Add Doctor
$('#add-doctor-form').on('submit', function(e){
    e.preventDefault();
    $.ajax({
        url: 'add_doctor_process.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(resp){
            if(resp.status==='success'){
                Swal.fire('Success', resp.message, 'success').then(()=> location.reload());
            } else {
                Swal.fire('Error', resp.message, 'error');
            }
        },
        error: function(xhr,status,error){
            Swal.fire('Error','Something went wrong: '+error,'error');
        }
    });
});

// Delete Doctor
$('.delete-doctor').on('click', function(e){
    e.preventDefault();
    let id = $(this).data('id');
    Swal.fire({
        title: 'Are you sure?',
        text: 'This doctor will be deleted!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#127137',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result)=>{
        if(result.isConfirmed){
            $.post('delete_doctor.php', {id:id}, function(resp){
                Swal.fire('Deleted!', resp.message, 'success').then(()=> location.reload());
            }, 'json');
        }
    });
});
</script>

</body>
</html>
