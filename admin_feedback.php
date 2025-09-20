<?php
session_start();
include 'config/db.php';

// Check admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch all feedbacks
$stmt = $conn->prepare("SELECT * FROM feedback ORDER BY created_at DESC");
$stmt->execute();
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Feedback Management</title>
<link rel="stylesheet" href="assets/css/style.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
<style>
body { font-family: Arial, sans-serif; margin:0; padding:0; background:#e8f5e9; }
.container { max-width:1200px; margin:20px auto; padding:20px; background:#fff; border-radius:10px; box-shadow:0 6px 15px rgba(0,0,0,0.1); }
h1 { text-align:center; margin-bottom:20px; color:#2e7d32; }
.back-dashboard { display:inline-block; margin-bottom:20px; background:#43a047; color:#fff; padding:10px 18px; border-radius:6px; text-decoration:none; font-weight:bold; transition:0.3s; }
.back-dashboard:hover { background:#2e7d32; }
table { width:100%; border-collapse:collapse; }
th, td { padding:14px 12px; text-align:left; border-bottom:1px solid #ddd; vertical-align:middle; }
th { background:#43a047; color:#fff; text-transform:uppercase; font-size:14px; }
tr:hover { background:#f1f1f1; }
.status { padding:6px 12px; border-radius:5px; color:#fff; font-weight:bold; text-transform:capitalize; display:inline-block; }
.approved { background:#2e7d32; }
.unapproved { background:#c62828; }
.pending { background:#f9a825; color:#000; }
.action-btn { padding:7px 15px; margin:2px 3px; border:none; border-radius:6px; cursor:pointer; color:#fff; font-weight:bold; transition:0.3s; font-size:13px; }
.action-btn:hover { opacity:0.85; }
.approve-btn { background:#2e7d32; }
.unapprove-btn { background:#c62828; }
.delete-btn { background:#e53935; }
/* Mobile responsive */
@media(max-width:768px){
    table, thead, tbody, th, td, tr { display:block; }
    th { position:absolute; top:-9999px; left:-9999px; }
    tr { margin-bottom:15px; border:1px solid #ddd; padding:12px; border-radius:10px; background:#fff; }
    td { border:none; position:relative; padding-left:50%; text-align:left; }
    td:before { position:absolute; top:12px; left:12px; width:45%; padding-right:10px; white-space:nowrap; font-weight:bold; color:#2e7d32; }
    td:nth-of-type(1):before { content:"ID"; }
    td:nth-of-type(2):before { content:"Name"; }
    td:nth-of-type(3):before { content:"Message"; }
    td:nth-of-type(4):before { content:"Rating"; }
    td:nth-of-type(5):before { content:"Status"; }
    td:nth-of-type(6):before { content:"Action"; }
    .action-btn { width:48%; margin:5px 1%; font-size:12px; padding:6px 0; }
}
</style>
</head>
<body>
<div class="container">
    <a href="admin_dashboard.php" class="back-dashboard">Back to Dashboard</a>
    <h1>Feedback Management</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Message</th>
                <th>Rating</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($feedbacks as $fb): ?>
            <tr id="row-<?= $fb['id'] ?>">
                <td><?= htmlspecialchars($fb['id']) ?></td>
                <td><?= htmlspecialchars($fb['name']) ?></td>
                <td><?= htmlspecialchars($fb['message']) ?></td>
                <td><?= str_repeat('★', $fb['rating']) . str_repeat('☆', 5 - $fb['rating']) ?></td>
                <td><span class="status <?= $fb['status'] ?>"><?= ucfirst($fb['status']) ?></span></td>
                <td>
                    <button class="action-btn <?= $fb['status']==='approved'?'unapprove-btn':'approve-btn' ?>" 
                        data-id="<?= $fb['id'] ?>" 
                        data-action="<?= $fb['status']==='approved'?'unapprove':'approve' ?>">
                        <?= $fb['status']==='approved'?'Unapprove':'Approve' ?>
                    </button>
                    <button class="action-btn delete-btn" data-id="<?= $fb['id'] ?>">Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Approve/Unapprove
    $('.action-btn').not('.delete-btn').click(function(){
        let id = $(this).data('id');
        let action = $(this).data('action');
        Swal.fire({
            title: `Are you sure you want to ${action} this feedback?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result)=>{
            if(result.isConfirmed){
                $.post('admin_feedback_action.php', {id, action}, function(res){
                    if(res.status==='success'){
                        Swal.fire({ icon:'success', title:res.message, timer:1500, showConfirmButton:false });
                        let row = $('#row-'+id);
                        if(action==='approve'){
                            row.find('.status').text('Approved').removeClass('unapproved pending').addClass('approved');
                            $(row).find('.action-btn').text('Unapprove').removeClass('approve-btn').addClass('unapprove-btn').data('action','unapprove');
                        } else {
                            row.find('.status').text('Unapproved').removeClass('approved pending').addClass('unapproved');
                            $(row).find('.action-btn').text('Approve').removeClass('unapprove-btn').addClass('approve-btn').data('action','approve');
                        }
                    } else Swal.fire({icon:'error', title:'Error', text:res.message});
                },'json').fail(()=>{ Swal.fire({icon:'error', title:'Error', text:'Server error.'}); });
            }
        });
    });

    // Delete
    $('.delete-btn').click(function(){
        let id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure you want to delete this feedback?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result)=>{
            if(result.isConfirmed){
                $.post('admin_feedback_action.php', {id, action:'delete'}, function(res){
                    if(res.status==='success'){
                        Swal.fire({ icon:'success', title:res.message, timer:1500, showConfirmButton:false });
                        $('#row-'+id).remove();
                    } else Swal.fire({icon:'error', title:'Error', text:res.message});
                },'json').fail(()=>{ Swal.fire({icon:'error', title:'Error', text:'Server error.'}); });
            }
        });
    });
});
</script>
</body>
</html>
