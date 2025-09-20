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
$user_name = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Doctors</title>
<link rel="stylesheet" href="assets/css/admin_dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>/* ------------------------- Reset & Base ------------------------- */
* { box-sizing: border-box; margin:0; padding:0; font-family: Arial, sans-serif; }
body { background: #f4f6f8; color:#333; }
a { text-decoration:none; color: inherit; }
button { font-family: inherit; }

/* ------------------------- Header ------------------------- */
.page-header {
  display:flex; justify-content:space-between; align-items:center;
  padding:10px 20px; background: linear-gradient(90deg, #fff, #f9f9f9);
  box-shadow:0 2px 5px rgba(0,0,0,0.1); position:sticky; top:0; z-index:100;
}
.page-header .logo { height:40px; margin-right:10px; }
.page-header h1 { font-size:18px; color:#127137; flex:1; }
.logout-btn {
  background:#127137; color:#fff; border:none; border-radius:6px; padding:6px 12px; cursor:pointer;
}
.logout-btn:hover { background:#0e5d2c; }

/* ------------------------- Layout ------------------------- */
.container { display:flex; min-height:100vh; }
.sidebar {
  width:240px; background:#fff; box-shadow:2px 0 5px rgba(0,0,0,0.1);
  flex-shrink:0; transition: transform 0.3s ease; position:fixed; height:100%; overflow-y:auto;
}
.main-content { flex:1; margin-left:240px; padding:20px; transition: margin-left 0.3s ease; }
@media (max-width:900px){
  .sidebar { transform:translateX(-100%); z-index:200; }
  body.sidebar-open .sidebar { transform:translateX(0); }
  .main-content { margin-left:0; }
}

/* ------------------------- Sidebar ------------------------- */
.sidebar ul { list-style:none; padding:0; margin:0; }
.sidebar ul li a {
  display:flex; align-items:center; padding:12px 20px; color:#127137;
  transition:0.2s; border-radius:6px; position:relative;
}
.sidebar ul li a.active, .sidebar ul li a:hover { background:#e6f0ea; }
.sidebar ul li.has-dropdown > a .arrow { margin-left:auto; transition: transform 0.3s; }
.sidebar ul li .dropdown { display:none; flex-direction:column; padding-left:15px; margin-top:5px; }
.sidebar ul li .dropdown.dropdown-open { display:flex; }

/* ------------------------- Overlay ------------------------- */
.overlay {
  display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.3); z-index:150;
}
body.sidebar-open .overlay { display:block; }

/* ------------------------- Cards ------------------------- */
.add-form, .table-card {
  background: linear-gradient(120deg, #fff, #f9f9f9);
  padding:20px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:20px;
}
.add-form h3, .table-card h3 { color:#127137; margin-top:0; margin-bottom:15px; }

/* ------------------------- Form Elements ------------------------- */
.add-form input, .add-form select { 
  width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; margin-bottom:15px; 
}
.add-form button { 
  width:100%; padding:10px; border:none; border-radius:6px; background:#127137; color:#fff; font-weight:bold; cursor:pointer; 
}
.add-form button:hover { background:#0e5d2c; }

/* ------------------------- Table Container ------------------------- */
.table-wrapper {
    width: 100%;
    margin-bottom: 20px;
    overflow: hidden;           /* prevent accidental scroll */
}

/* ------------------------- Table ------------------------- */
.history-table {
    width: 100%;                /* fill container */
    border-collapse: collapse;
}

.history-table th,
.history-table td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: left;
    word-break: break-word;     /* wrap long content */
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

/* ------------------------- Responsive ------------------------- */
@media (max-width: 1024px) {
    .table-wrapper {
        padding-left: 10px;
        padding-right: 10px;
    }
}

@media (max-width: 768px) {
    .history-table th,
    .history-table td {
        padding: 8px;
        font-size: 14px;
    }
}

/* ------------------------- Flex Layout for Page ------------------------- */
.container {
    display: flex;
    flex-wrap: wrap;            /* allow content to wrap on small screens */
}

.sidebar {
    width: 250px;               /* fixed width for sidebar */
    flex-shrink: 0;             /* do not shrink */
}

.main-content {
    flex: 1;                    /* fill remaining space */
    padding: 20px;
    box-sizing: border-box;
    min-width: 0;               /* allows content to shrink properly */
}

/* Mobile: stack sidebar and main */
@media (max-width: 768px) {
    .container {
        flex-direction: column;
    }
    .sidebar {
        width: 100%;
    }
    .main-content {
        width: 100%;
        padding: 15px;
    }
}

/* ------------------------- Action Buttons ------------------------- */
.action-btn { display:inline-block; padding:6px 12px; margin:2px; border-radius:6px; font-weight:bold; color:#fff; transition:0.2s; }
.action-edit { background:#127137; } .action-edit:hover{ background:#0e5d2c; }
.action-delete { background:#d9534f; } .action-delete:hover{ background:#c9302c; }
.btn-back { display:inline-block; padding:10px 20px; background:#127137; color:#fff; border-radius:6px; margin-top:20px; text-decoration:none; font-weight:bold; }
.btn-back:hover { background:#0e5d2c; transform:translateY(-2px); transition:0.2s; }

/* ------------------------- Hover Effects ------------------------- */
.sidebar ul li a:hover, .sidebar ul li a.active { cursor:pointer; }
.add-form input:focus, .add-form select:focus { outline:none; border-color:#127137; box-shadow:0 0 5px rgba(18,113,55,0.3); }
button:hover, .action-btn:hover { transform:translateY(-1px); }

/* ------------------------- Misc ------------------------- */
h2 { color:#127137; margin-bottom:20px; }

.btn-back:hover { background:#0e5d2c; transform:translateY(-2px); transition:0.2s; }
</style>

</head>
<body>

<header class="page-header">
    <div style="display:flex; align-items:center;">
        <button id="sidebarToggle" style="margin-right:10px; font-size:18px;"><i class="fas fa-bars"></i></button>
        <img src="assets/images/logo.png" alt="Logo" class="logo">
        <h1><?php echo htmlspecialchars($user_name); ?></h1>
    </div>
    <form action="logout.php" method="POST" style="margin:0;">
        <button type="submit" name="logout" class="logout-btn">Logout</button>
    </form>
</header>

<div class="container">
    <aside class="sidebar" id="sidebar">
        <ul>
            <li><a href="admin_dashboard.php">Overview</a></li>
            <li><a href="manage_doctors.php" class="active">Manage Doctors</a></li>
            <li><a href="manage_specialties.php">Manage Specialties</a></li>
            <li><a href="system_setup.php">System Setup</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>
    <div class="overlay" id="overlay"></div>

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
                            <a href="edit_doctor.php?id=<?php echo $doc['id']; ?>" class="action-btn action-edit">Edit</a>
                            <a href="#" class="action-btn action-delete delete-doctor" data-id="<?php echo $doc['id']; ?>">Delete</a>
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
// Sidebar & Overlay
const sidebar = document.getElementById("sidebar");
const overlay = document.getElementById("overlay");
const sidebarToggle = document.getElementById("sidebarToggle");
const MOBILE_BREAKPOINT = 900;

sidebarToggle?.addEventListener("click", () => {
    document.body.classList.toggle("sidebar-open");
});

overlay?.addEventListener("click", () => {
    document.body.classList.remove("sidebar-open");
});

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
