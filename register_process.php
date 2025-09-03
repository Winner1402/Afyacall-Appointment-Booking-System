<?php
session_start();
include 'config/db.php'; // your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
        header('Location: register.php');
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Email already registered!";
        header('Location: register.php');
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into database (role: patient by default)
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$name, $email, $hashed_password, 'patient'])) {
        $_SESSION['success'] = "Account created successfully. Please login.";
        header('Location: login.php');
        exit();
    } else {
        $_SESSION['error'] = "Registration failed. Try again!";
        header('Location: register.php');
        exit();
    }
} else {
    // If not POST request, redirect to register page
    header('Location: register.php');
    exit();
}
?>
