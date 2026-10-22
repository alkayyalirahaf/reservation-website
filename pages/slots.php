<?php
// 1. Require config and classes
require '../config.php';
require '../classes/User.php';
require '../classes/Auth.php';
require '../classes/TimeSlot.php';

// 2. DB Connection & Instances
$db = new Database();
$pdo = $db->getConnection();
$auth = new Auth($pdo);
$timeSlotModel = new TimeSlot($pdo);

// 3. Start session & Guard
session_start();
$auth->requireLogin();

// 4. Fetch available slots (with optional date filter)
$filterDate = $_GET['date'] ?? null; // Get date from URL if exists

// Prevent filtering by past dates (security check)
if ($filterDate && $filterDate < date('Y-m-d')) {
    $filterDate = date('Y-m-d'); // Force it to today if a past date is sent via URL
}

$slots = $timeSlotModel->getAvailable($filterDate);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Slots</title>
</head>
<body>
    <h2>Available Cleaning Slots</h2>
    <div style="margin-bottom: 20px;">
        <!-- Date Filter Form -->
        <form method="GET" action="">
            <label>Filter by Date:</label>
            <!-- Added min attribute to prevent past dates -->
            <input type="date" name="date" value="<?= htmlspecialchars($filterDate ?? '') ?>" min="<?= date('Y-m-d') ?>">
            <button type="submit">Filter</button>
            
            <!-- Quick filter buttons -->
            <a href="slots.php?date=<?= date('Y-m-d') ?>" style="margin-left: 10px;">Today</a> | 
            <a href="slots.php?date=<?= date('Y-m-d', strtotime('+1 day')) ?>">Tomorrow</a> |
            <a href="slots.php">Clear</a>
        </form>
    </div>

    <hr>

    <!-- Display Slots -->
    <?php if (empty($slots)): ?>
        <p>No available slots found.</p>
    <?php else: ?>
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <?php foreach ($slots as $slot): ?>
                <div style="border: 1px solid #ccc; padding: 15px; width: 250px;">
                    <h3><?= htmlspecialchars($slot['slot_date']) ?></h3>
                    <p>Time: <?= htmlspecialchars($slot['start_time']) ?> - <?= htmlspecialchars($slot['end_time']) ?></p>
                    <p>Remaining Spots: <strong><?= $slot['remaining_capacity'] ?> / <?= $slot['capacity'] ?></strong></p>

                    <?php if ($slot['remaining_capacity'] > 0): ?>
                        <a href="book.php?id=<?= $slot['id'] ?>" style="display: inline-block; padding: 10px; background: green; color: white; text-decoration: none;">
                            Book Now
                        </a>
                    <?php else: ?>
                        <button disabled style="padding: 10px; background: gray; color: white; border: none;">Full</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <br>
    <a href="../index.php">Back to Home</a>
</body>
</html>