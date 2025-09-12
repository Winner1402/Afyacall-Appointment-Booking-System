<?php
include 'auth_check.php';
authorize(['admin']);
include 'config/db.php';

// Fetch patients (role = 'patient')
$patients = $conn->query("
    SELECT id, name, email, phone
    FROM users
    WHERE role = 'patient'
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Patients</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f7f8;
    margin: 0;
    padding: 20px;
}

h2 {
    color: #127137;
    margin-bottom: 20px;
}

/* Table container */
.table-wrapper {
    overflow-x: auto;
    margin-bottom: 20px;
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #ccc;
}

th, td {
    padding: 12px 15px;
    text-align: center;
    border: 1px solid #ccc;
}

th {
    background-color: #127137;
    color: #fff;
    font-weight: 600;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

tr:hover {
    background-color: #e6f0ea;
}

/* Action buttons */
.action-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    color: #fff;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin: 2px;
    font-size: 0.85em;
}

.edit-btn {
    background-color: #0275d8;
}
.edit-btn:hover {
    background-color: #025aa5;
}

.delete-btn {
    background-color: #d9534f;
}
.delete-btn:hover {
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

<h2>Manage Patients</h2>
<div class="table-wrapper">
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Patient Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($patients as $p): ?>
        <tr id="patient-<?php echo $p['id']; ?>">
            <td><?php echo $p['id']; ?></td>
            <td><?php echo htmlspecialchars($p['name']); ?></td>
            <td><?php echo htmlspecialchars($p['email']); ?></td>
            <td><?php echo htmlspecialchars($p['phone']); ?></td>
            <td>
                <button class="action-btn edit-btn" data-id="<?php echo $p['id']; ?>">Edit</button>
                <button class="action-btn delete-btn" data-id="<?php echo $p['id']; ?>">Delete</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<a href="admin_dashboard.php" class="btn-back">Back</a>

</div>

<script>
// Handle Delete
$('.delete-btn').click(function(){
    let id = $(this).data('id');
    Swal.fire({
        title: "Are you sure?",
        text: "This patient will be deleted permanently.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d9534f",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, delete!"
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('delete_patient.php', { id:id }, function(resp){
                if(resp.status === 'success'){
                    $('#patient-'+id).remove();
                    Swal.fire("Deleted!", resp.message, "success");
                } else {
                    Swal.fire("Error", resp.message, "error");
                }
            }, 'json');
        }
    });
});

// Handle Edit
$('.edit-btn').click(function(){
    let id = $(this).data('id');
    window.location.href = 'edit_patient.php?id=' + id;
});
</script>

</body>
</html>
