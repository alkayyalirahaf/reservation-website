<?php
// 1. Require config and classes
require '../config.php';
require '../classes/User.php';

// 2. Create DB connection
$db = new Database();
$pdo = $db->getConnection();
$userModel = new User($pdo);

// 3. Start session for flash messages
session_start();

$errors = [];

// 4. Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

     // Call the User class method 
    $errors = $userModel->register($name, $email, $password, $confirm_password);

    // 5. If success, set flash message and redirect
    if (empty($errors)) {
        $_SESSION['flash_success'] = "Registration successful! Please log in.";
        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Cleaning Service</title>
</head>
<body>
    <h2>Create an Account</h2>

    <!-- Show errors if any -->
    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Registration Form -->
    <form method="POST" action="">
        <div>
            <label>Full Name:</label><br>
            <input type="text" name="name" required>
        </div>
        <br>
        <div>
            <label>Email:</label><br>
            <input type="email" name="email" required>
        </div>
        <br>
        <div>
            <label>Password:</label><br>
            <input type="password" name="password" required>
        </div>
        <br>
        <div>
            <label>Confirm Password:</label><br>
            <input type="password" name="confirm_password" required>
        </div>
        <br>
        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>
</body>
</html>