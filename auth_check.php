<?php
session_start();

function authorize($allowed_roles = []) {
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], $allowed_roles)) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Access Denied</title>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You are not authorized to view this page.',
                confirmButtonText: 'Go to Login'
            }).then(() => {
                window.location.href = 'login.php';
            });
        </script>
        </body>
        </html>
        <?php
        exit();
    }
}
