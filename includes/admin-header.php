<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
$adminName = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Workspace - Tidyhome</title>
    <link rel="stylesheet" href="<?= $cssPath ?? '../assets/css/style.css' ?>">
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">Tidyhome</div>
        <div class="sidebar-subtitle">Admin Workspace</div>

        <nav class="sidebar-nav">
            <a href="../admin/dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
            <a href="../admin/reservations.php" class="<?= $currentPage === 'reservations.php' ? 'active' : '' ?>">Reservations</a>
            <a href="../admin/users.php" class="<?= $currentPage === 'users.php' ? 'active' : '' ?>">Users</a>
            <a href="../admin/slots.php" class="<?= $currentPage === 'slots.php' ? 'active' : '' ?>">Time Slots</a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user"><?= htmlspecialchars($adminName) ?></div>
            <a href="../pages/logout.php">Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Topbar -->
        <div class="admin-topbar">
            <span>Home cleaning reservations</span>
            <span><?= date('l, F j, Y') ?></span>
        </div>