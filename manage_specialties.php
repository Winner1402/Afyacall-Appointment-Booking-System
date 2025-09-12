<?php
session_start();
include 'config/db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='admin'){
    exit("Unauthorized access");
}

// Fetch all specialties
$stmt = $conn->query("SELECT * FROM specialties ORDER BY created_at DESC");
$specialties = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Specialties</title>
<link rel="stylesheet" href="assets/css/patient_dashboard.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
.container {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: visible; /* Ensures table scrolls if too wide */
    box-sizing: border-box;
    min-height: fit-content/* Full height to accommodate content */
}

h2 {
    text-align: center;
    color: #127137;
}

/* Container */
.container {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Page Title */
h2 {
    text-align: center;
    color: #127137;
    margin-bottom: 20px;
}

/* Table wrapper for horizontal scroll */
.table-wrapper {
    overflow-x: auto;
    width: 100%;
    margin-top: 20px;
}

/* Table Styling */
table {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
    table-layout: auto;
}

th, td {
    white-space: nowrap; /* Prevent text wrapping */
    overflow: hidden;
    text-overflow: ellipsis; /* Indicate overflow with ... */
    padding: 10px;
    border: 1px solid #ccc;
    text-align: left;
    word-break: break-word; /* Prevent content overflow */
}

th {
    background-color: #127137;
    color: #fff;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

tr:hover {
    background-color: #e6f0ea;
}

/* Buttons */
button {
    padding: 5px 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
}

button.edit {
    background-color: #0e5d2c;
    color: #fff;
}

button.delete {
    background-color: #c0392b;
    color: #fff;
}

button.submit {
    background-color: #127137;
    color: #fff;
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    font-weight: bold;
}

button.submit:hover {
    background-color: #0e5d2c;
}

/* Form Styling */
form.add-specialty {
    margin-top: 20px;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 15px;
}

label {
    font-weight: bold;
    display: block;
    margin-bottom: 5px;
}

input, textarea, select {
    width: 100%;
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #ccc;
    box-sizing: border-box; /* Include padding in width */
    font-size: 14px;
}
input:focus, textarea:focus, select:focus {
    border-color: #127137;
    outline: none;
}
</style>
</head>
<body>

<div class="container">
    <button type="button" onclick="window.history.back();" style="padding:10px 15px; background:#127137; color:#fff; border:none; border-radius:6px; cursor:pointer;">
    ← Back
</button>

<h2>Manage Specialties</h2>

<!-- Add Specialty Form -->
<div class="add-specialty">
    <h3>Add New Specialty</h3>
    <form id="add-specialty-form">
        <div class="form-group">
            <label for="name">Specialty Name</label>
            <input type="text" name="name" id="name" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="3"></textarea>
        </div>
        <button type="submit" class="submit">Add Specialty</button>
    </form>
</div>

<!-- Specialties Table -->
<div class="table-wrapper">
  <table class="specialty-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Description</th>
        <th>Created At</th>
        <th>Updated At</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($specialties as $s): ?>
      <tr data-id="<?php echo $s['id']; ?>">
        <td><?php echo $s['id']; ?></td>
        <td class="name"><?php echo htmlspecialchars($s['name']); ?></td>
        <td class="description"><?php echo htmlspecialchars($s['description']); ?></td>
        <td><?php echo date('d M Y', strtotime($s['created_at'])); ?></td>
        <td><?php echo isset($s['updated_at']) ? date('d M Y', strtotime($s['updated_at'])) : '-'; ?></td>
        <td>
          <button class="edit">Edit</button>
          <button class="delete">Delete</button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

</div>

<script>
$(document).ready(function(){
    // Add Specialty
    $('#add-specialty-form').on('submit', function(e){
        e.preventDefault();
        $.ajax({
            url:'specialty_process.php',
            type:'POST',
            data: $(this).serialize() + '&action=add',
            dataType:'json',
            success:function(resp){
                if(resp.status==='success'){
                    Swal.fire('Success', resp.message, 'success').then(()=> location.reload());
                } else {
                    Swal.fire('Error', resp.message, 'error');
                }
            }
        });
    });

    // Edit Specialty
    $('.edit').on('click', function(){
        let row = $(this).closest('tr');
        let id = row.data('id');
        let name = row.find('.name').text();
        let description = row.find('.description').text();

        Swal.fire({
            title:'Edit Specialty',
            html: `<input id="swal-name" class="swal2-input" placeholder="Name" value="${name}">
                   <textarea id="swal-desc" class="swal2-textarea" placeholder="Description">${description}</textarea>`,
            focusConfirm: false,
            preConfirm: () => {
                let newName = $('#swal-name').val();
                let newDesc = $('#swal-desc').val();
                return {name:newName, description:newDesc};
            }
        }).then((result)=>{
            if(result.isConfirmed){
                $.ajax({
                    url:'specialty_process.php',
                    type:'POST',
                    data:{action:'edit', id:id, name:result.value.name, description:result.value.description},
                    dataType:'json',
                    success:function(resp){
                        if(resp.status==='success'){
                            Swal.fire('Updated', resp.message,'success').then(()=> location.reload());
                        } else {
                            Swal.fire('Error', resp.message,'error');
                        }
                    }
                });
            }
        });
    });

    // Delete Specialty
    $('.delete').on('click', function(){
        let row = $(this).closest('tr');
        let id = row.data('id');
        Swal.fire({
            title:'Are you sure?',
            text:'This will delete the specialty permanently!',
            icon:'warning',
            showCancelButton:true,
            confirmButtonColor:'#c0392b',
            cancelButtonColor:'#3085d6',
            confirmButtonText:'Yes, delete it!'
        }).then((result)=>{
            if(result.isConfirmed){
                $.ajax({
                    url:'specialty_process.php',
                    type:'POST',
                    data:{action:'delete', id:id},
                    dataType:'json',
                    success:function(resp){
                        if(resp.status==='success'){
                            Swal.fire('Deleted', resp.message,'success').then(()=> location.reload());
                        } else {
                            Swal.fire('Error', resp.message,'error');
                        }
                    }
                });
            }
        });
    });
});
</script>
</body>
</html>
