 <?php
include 'config/db.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    die("Patient ID not provided.");
}

$patient_id = intval($_GET['id']);

// Fetch patient details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $patient_id, PDO::PARAM_INT);
$stmt->execute();
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("Patient not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Patient</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7f8;
            margin: 0;
            padding: 20px;
        }
        .form-container {
            max-width: 500px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #127137;
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }
        button {
            background-color: #127137;
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
        }
        button:hover {
            background-color: #0e5d2c;
        }
    </style>
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Patient</h2>
        <form method="post" action="edit_patient_process.php">
            <input type="hidden" name="id" value="<?php echo $patient['id']; ?>">
            
            <label>Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($patient['name']); ?>" required>
            
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($patient['email']); ?>" required>
            
            <label>Phone:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($patient['phone']); ?>" required>
            
            <label>Gender:</label>
            <select name="gender" required>
                <option value="Male" <?php echo ($patient['gender'] === "Male") ? "selected" : ""; ?>>Male</option>
                <option value="Female" <?php echo ($patient['gender'] === "Female") ? "selected" : ""; ?>>Female</option>
            </select>
            
            <button type="submit">Update Patient</button>
        </form>
    </div>
</body>
</html>
