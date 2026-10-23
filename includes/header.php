<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Check if user is logged in and role
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && $_SESSION['role'] === 'admin';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tidyhome - Cleaning Service</title>
    <!-- Link to CSS (we will pass a relative path based on the page) -->
    <link rel="stylesheet" href="<?= $cssPath ?? '../assets/css/style.css' ?>">
</head>
<body>

<!-- User & Guest Navbar -->
<?php if (!$isAdmin): ?>
<nav class="navbar">
    <div class="container">
        <a href="<?= $isLoggedIn ? ($isAdmin ? '../admin/dashboard.php' : '../index.php') : '../index.php' ?>" class="navbar-brand">Tidyhome</a>
        
        <div class="navbar-links">
            <?php if ($isLoggedIn): ?>
                <a href="../pages/slots.php" class="<?= $currentPage === 'slots.php' ? 'active' : '' ?>">Slots</a>
                <a href="../pages/my-reservations.php" class="<?= $currentPage === 'my-reservations.php' ? 'active' : '' ?>">My Reservations</a>
                <a href="../pages/logout.php">Logout</a>
            <?php else: ?>
                <span class="text-muted">Home cleaning service</span>
            <?php endif; ?>
        </div>
    </div>
</nav>
<?php endif; ?>

<main class="container" style="margin-top: 40px; margin-bottom: 60px;">