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



// If already logged in, redirect to index
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}


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

$cssPath = '../assets/css/style.css';
require '../includes/header.php';
?>


<!-- Register Form (Centered) -->
<div style="max-width: 400px; margin: 60px auto;">
    <h1>Create your account</h1>
    <p class="text-muted" style="margin-bottom: 30px;">Reserve a home cleaning in a few simple steps.</p>

     <!-- Show errors -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            ! <?= htmlspecialchars($errors[0]) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Full name</label>
            <input type="text" name="name" class="form-control" required>
        </div>


        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
            <small class="text-muted" style="display: block; margin-top: 5px;">Use at least 6 characters.</small>
        </div>


        <div class="form-group">
            <label>Confirm password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 10px;">Create account</button>
    </form>

     <p class="text-muted" style="text-align: center; margin-top: 20px;">
        Already have an account? <a href="login.php" style="font-weight: 500;">Log in</a>
    </p>
</div>

<?php require '../includes/footer.php'; ?>