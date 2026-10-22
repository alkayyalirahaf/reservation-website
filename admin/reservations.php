<?php
// 1. Require config and classes (note the ../ paths)
require '../config.php';
require '../classes/User.php';
require '../classes/Auth.php';
require '../classes/Reservation.php';

// 2. DB Connection & Instances
$db = new Database();
$pdo = $db->getConnection();
$auth = new Auth($pdo);
$reservationModel = new Reservation($pdo);

// 3. Session & Guard
session_start();
$auth->requireAdmin(); // Only admins can access this page

// 4. Handle Status Changes (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id']) && isset($_POST['new_status'])) {
    $resId = $_POST['reservation_id'];
    $newStatus = $_POST['new_status'];

    $errors = $reservationModel->updateStatus($resId, $newStatus);

    if (empty($errors)) {
        $_SESSION['flash_success'] = "Reservation status updated to " . ucfirst($newStatus);
    } else {
        $_SESSION['flash_error'] = $errors[0];
    }

    header("Location: reservations.php");
    exit;
}

// 5. Handle Search & Filter (GET)
$search = $_GET['search'] ?? null;
$statusFilter = $_GET['status'] ?? null;

$reservations = $reservationModel->getAll($search, $statusFilter);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Reservations - Admin</title>
</head>
<body>
    <h2>Manage Reservations</h2>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <p style="color: green; border: 1px solid green; padding: 10px; background: #e8f5e9;"><?= htmlspecialchars($_SESSION['flash_success']) ?></p>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <p style="color: red; border: 1px solid red; padding: 10px; background: #ffebee;"><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Search & Filter Form -->
    <form method="GET" action="" style="margin-bottom: 20px; display: flex; gap: 10px;">
        <input type="text" name="search" placeholder="Search by name or email..." value="<?= htmlspecialchars($search ?? '') ?>">
        
        <select name="status">
            <option value="">All Statuses</option>
            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="confirmed" <?= $statusFilter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
            <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
        
        <button type="submit">Search / Filter</button>
        <a href="reservations.php" style="padding: 5px;">Clear</a>
    </form>

    <!-- Reservations Table -->
    <?php if (empty($reservations)): ?>
        <p>No reservations found matching your criteria.</p>
    <?php else: ?>
        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="background: #f0f0f0;">
                    <th>ID</th>
                    <th>User</th>
                    <th>Date & Time</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $res): ?>
                    <tr>
                        <td><?= $res['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($res['user_name']) ?></strong><br>
                            <small><?= htmlspecialchars($res['user_email']) ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($res['slot_date']) ?><br>
                            <small><?= htmlspecialchars($res['start_time']) ?> - <?= htmlspecialchars($res['end_time']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($res['address']) ?></td>
                        <td>
                            <strong><?= ucfirst(htmlspecialchars($res['status'])) ?></strong>
                        </td>
                        <td>
                            <!-- Action Buttons (POST forms to change status) -->
                            <?php if ($res['status'] === 'pending'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="reservation_id" value="<?= $res['id'] ?>">
                                    <input type="hidden" name="new_status" value="confirmed">
                                    <button type="submit" style="background: green; color: white; border: none; padding: 5px 10px; cursor: pointer;">Confirm</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="reservation_id" value="<?= $res['id'] ?>">
                                    <input type="hidden" name="new_status" value="rejected">
                                    <button type="submit" style="background: red; color: white; border: none; padding: 5px 10px; cursor: pointer;">Reject</button>
                                </form>
                            <?php elseif ($res['status'] === 'confirmed'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="reservation_id" value="<?= $res['id'] ?>">
                                    <input type="hidden" name="new_status" value="cancelled">
                                    <button type="submit" style="background: gray; color: white; border: none; padding: 5px 10px; cursor: pointer;" onclick="return confirm('Cancel this confirmed reservation?')">Cancel</button>
                                </form>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <br>
    <a href="dashboard.php">Back to Dashboard</a> <!-- Dashboard is not created yet, but will be -->
</body>
</html>