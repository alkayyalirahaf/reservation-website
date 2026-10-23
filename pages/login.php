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


// Set path for CSS (since we are in pages folder)
$cssPath = '../assets/css/style.css';
require '../includes/header.php';
?>

<!-- Login Form (Centered) -->
<div style="max-width: 400px; margin: 60px auto;">
    <h1>Welcome back</h1>
    <p class="text-muted" style="margin-bottom: 30px;">Log in to book and manage your cleanings.</p>

    <!-- Show success message from register page -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success">
            ✓ <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>


     <!-- Show errors -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            ! <?= htmlspecialchars($errors[0]) ?>
        </div>
    <?php endif; ?>



       <form method="POST" action="">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 10px;">Log in</button>
    </form>




    <p class="text-muted" style="text-align: center; margin-top: 20px;">
        New to Tidyhome? <a href="register.php" style="font-weight: 500;">Create an account</a>
    </p>
</div>

<?php require '../includes/footer.php'; ?>