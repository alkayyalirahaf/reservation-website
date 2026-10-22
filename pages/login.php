<?php
// 1. Require config and classes
require '../config.php';
require '../classes/User.php'; // Auth needs User class
require '../classes/Auth.php';

// 2. Create DB connection
$db = new Database();
$pdo = $db->getConnection();
$auth = new Auth($pdo);

// 3. Start session
session_start();

// If already logged in, redirect to index
if ($auth->isLoggedIn()) {
    header("Location: ../index.php");
    exit;
}

$errors = [];

// 4. Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "Please fill in both email and password.";
    } else {
        $errors = $auth->login($email, $password);
    }

    // 5. If success, redirect to index
    if (empty($errors)) {
        header("Location: ../index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Cleaning Service</title>
</head>
<body>
    <h2>Login to Your Account</h2>

    <!-- Show success message from register page -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div style="color: green;">
            <p><?= htmlspecialchars($_SESSION['flash_success']) ?></p>
        </div>
        <?php unset($_SESSION['flash_success']); // Clear message after showing ?>
    <?php endif; ?>

    <!-- Show errors if any -->
    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST" action="">
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
        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a></p>
</body>
</html>