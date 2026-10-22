<?php
// 1. Require config and classes
require '../config.php';
require '../classes/User.php';
require '../classes/Auth.php';
require '../classes/Reservation.php';

// 2. DB Connection & Instances
$db = new Database();
$pdo = $db->getConnection();
$auth = new Auth($pdo);
$userModel = new User($pdo);
$reservationModel = new Reservation($pdo);

// 3. Session & Guard
session_start();
$auth->requireAdmin(); 

// 4. Fetch Stats
$stats = $reservationModel->getStats();
$totalUsers = $userModel->countUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <!-- Include Chart.js from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Simple inline CSS for the dashboard layout */
        body { font-family: Arial, sans-serif; margin: 20px; }
        .nav { margin-bottom: 20px; background: #f4f4f4; padding: 10px; }
        .nav a { margin-right: 15px; text-decoration: none; color: #333; font-weight: bold; }
        .nav a:hover { color: teal; }
        
        .stats-grid {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .stat-card {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            width: 200px;
            text-align: center;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h3 { margin: 0; font-size: 1.1em; color: #555; }
        .stat-card p { margin: 10px 0 0; font-size: 2em; font-weight: bold; }
        
        .chart-container {
            width: 400px;
            height: 400px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation Menu -->
    <div class="nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="reservations.php">Manage Reservations</a>
        <a href="users.php">Manage Users</a>
        <a href="slots.php">Manage Time Slots</a>
        <a href="../pages/logout.php" style="color: red; float: right;">Logout</a>
    </div>

    <h2>Admin Dashboard</h2>
    
    <!-- Stat Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Reservations</h3>
            <p style="color: #333;"><?= $stats['total'] ?></p>
        </div>
        <div class="stat-card">
            <h3>Pending</h3>
            <p style="color: #ffc107;"><?= $stats['pending'] ?></p>
        </div>
        <div class="stat-card">
            <h3>Confirmed</h3>
            <p style="color: #28a745;"><?= $stats['confirmed'] ?></p>
        </div>
        <div class="stat-card">
            <h3>Registered Users</h3>
            <p style="color: #17a2b8;"><?= $totalUsers ?></p>
        </div>
    </div>

    <hr>

    <!-- Chart Section -->
    <h3>Reservations Status Overview</h3>
    <div class="chart-container">
        <canvas id="statusChart"></canvas>
    </div>

    <!-- JavaScript for Chart.js -->
    <script>
        const chartData = {
            labels: ['Pending', 'Confirmed', 'Rejected', 'Cancelled'],
            datasets: [{
                data: [
                    <?= $stats['pending'] ?>, 
                    <?= $stats['confirmed'] ?>, 
                    <?= $stats['rejected'] ?>, 
                    <?= $stats['cancelled'] ?>
                ],
                backgroundColor: [
                    '#ffc107', // Pending
                    '#28a745', // Confirmed
                    '#dc3545', // Rejected
                    '#6c757d'  // Cancelled
                ],
                borderWidth: 1
            }]
        };

        const config = {
            type: 'doughnut',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        };

        // Render the chart
        const ctx = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx, config);
    </script>
</body>
</html>