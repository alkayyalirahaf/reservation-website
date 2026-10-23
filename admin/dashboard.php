<?php
require '../config.php';
require '../classes/User.php';
require '../classes/Auth.php';
require '../classes/Reservation.php';

$db = new Database();
$pdo = $db->getConnection();
$auth = new Auth($pdo);
$userModel = new User($pdo);
$reservationModel = new Reservation($pdo);

session_start();
$auth->requireAdmin(); 

$stats = $reservationModel->getStats();
$totalUsers = $userModel->countUsers();

$cssPath = '../assets/css/style.css';
require '../includes/admin-header.php';
?>

<div style="margin-bottom: 40px;">
    <p class="text-muted">Overview • <?= date('F j, Y') ?></p>
    <h1>Dashboard</h1>
    <p class="text-muted">An overview of your home cleaning reservations.</p>
</div>

<!-- Stat Cards -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px;">
    <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 20px;">
        <p class="text-muted" style="margin-bottom: 15px;">Total Reservations</p>
        <h2 style="font-size: 2rem; color: var(--text-dark);"><?= $stats['total'] ?></h2>
    </div>
    <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 20px;">
        <p class="text-muted" style="margin-bottom: 15px;">Pending</p>
        <h2 style="font-size: 2rem; color: var(--text-dark);"><?= $stats['pending'] ?></h2>
    </div>
    <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 20px;">
        <p class="text-muted" style="margin-bottom: 15px;">Confirmed</p>
        <h2 style="font-size: 2rem; color: var(--text-dark);"><?= $stats['confirmed'] ?></h2>
    </div>
    <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 20px;">
        <p class="text-muted" style="margin-bottom: 15px;">Registered Users</p>
        <h2 style="font-size: 2rem; color: var(--text-dark);"><?= $totalUsers ?></h2>
    </div>
</div>

<!-- Chart Section -->
<div class="chart-section" style="border: 1px solid var(--border-color); border-radius: 8px; padding: 30px; display: flex; gap: 50px; align-items: center;">
    
    <!-- Chart Canvas -->
    <div style="width: 300px; height: 300px;">
        <canvas id="statusChart"></canvas>
    </div>

    <!-- Custom Legend (Like Figma) -->
    <div style="flex: 1;">
        <h3 style="margin-bottom: 20px;">Reservations by Status</h3>
        <p class="text-muted" style="margin-bottom: 25px;">All <?= $stats['total'] ?> reservations</p>

        <div style="display: flex; flex-direction: column; gap: 15px;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                <span class="badge badge-pending">Pending</span>
                <span><strong><?= $stats['pending'] ?></strong> <span class="text-muted">(<?= $stats['total'] ? round(($stats['pending']/$stats['total'])*100) : 0 ?>%)</span></span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                <span class="badge badge-confirmed">Confirmed</span>
                <span><strong><?= $stats['confirmed'] ?></strong> <span class="text-muted">(<?= $stats['total'] ? round(($stats['confirmed']/$stats['total'])*100) : 0 ?>%)</span></span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                <span class="badge badge-rejected">Rejected</span>
                <span><strong><?= $stats['rejected'] ?></strong> <span class="text-muted">(<?= $stats['total'] ? round(($stats['rejected']/$stats['total'])*100) : 0 ?>%)</span></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span class="badge badge-cancelled">Cancelled</span>
                <span><strong><?= $stats['cancelled'] ?></strong> <span class="text-muted">(<?= $stats['total'] ? round(($stats['cancelled']/$stats['total'])*100) : 0 ?>%)</span></span>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Confirmed', 'Rejected', 'Cancelled'],
            datasets: [{
                data: [<?= $stats['pending'] ?>, <?= $stats['confirmed'] ?>, <?= $stats['rejected'] ?>, <?= $stats['cancelled'] ?>],
                backgroundColor: ['#FEF3C7', '#DCFCE7', '#FEE2E2', '#F3F4F6'],
                borderColor: ['#D97706', '#16A34A', '#DC2626', '#6B7280'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%', // Makes it a thin ring like Figma
            plugins: {
                legend: { display: false } // Hide default legend (we made a custom one)
            }
        }
    });
</script>

<?php require '../includes/footer.php'; ?>