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
$reservationModel = new Reservation($pdo);

// 3. Session & Guard
session_start();
$auth->requireLogin();

// 4. Handle Cancellation (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cancelId = $_POST['cancel_id'];
    $errors = $reservationModel->cancel($cancelId, $_SESSION['user_id']);
    
    if (empty($errors)) {
        $_SESSION['flash_success'] = "Reservation cancelled successfully.";
    } else {
        $_SESSION['flash_error'] = $errors[0];
    }
    
    // PRG Pattern (Post/Redirect/Get) to prevent form resubmission
    header("Location: my-reservations.php");
    exit;
}

// 5. Fetch user's reservations
$reservations = $reservationModel->getByUser($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Reservations</title>
</head>
<body>
    <h2>My Reservations</h2>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <p style="color: green; border: 1px solid green; padding: 10px; background: #e8f5e9;">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </p>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash_error'])): ?>
        <p style="color: red; border: 1px solid red; padding: 10px; background: #ffebee;">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </p>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <a href="slots.php" style="display:inline-block; margin-bottom: 20px; padding: 10px; background: teal; color: white; text-decoration: none;">+ Book a new slot</a>

    <?php if (empty($reservations)): ?>
        <p>You have no reservations yet.</p>
    <?php else: ?>
        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="background: #f0f0f0;">
                    <th>Date</th>
                    <th>Time</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $res): ?>
                    <tr>
                        <td><?= htmlspecialchars($res['slot_date']) ?></td>
                        <td><?= htmlspecialchars($res['start_time']) ?> - <?= htmlspecialchars($res['end_time']) ?></td>
                        <td><?= htmlspecialchars($res['address']) ?></td>
                        <td>
                            <!-- Status Badge with colors -->
                            <?php 
                                $statusColor = match($res['status']) {
                                    'pending' => '#ffc107',
                                    'confirmed' => '#28a745',
                                    'rejected' => '#dc3545',
                                    'cancelled' => '#6c757d',
                                    default => '#000'
                                };
                            ?>
                            <span style="color: white; background: <?= $statusColor ?>; padding: 3px 8px; border-radius: 4px; font-size: 0.9em;">
                                <?= ucfirst(htmlspecialchars($res['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            // Show Cancel button only if status is pending or confirmed AND slot is in the future
                            $slotDateTime = $res['slot_date'] . ' ' . $res['start_time'];
                            $isFuture = strtotime($slotDateTime) > time();
                            
                            if (in_array($res['status'], ['pending', 'confirmed']) && $isFuture): 
                            ?>
                                <form method="POST" action="" style="margin: 0;">
                                    <input type="hidden" name="cancel_id" value="<?= $res['id'] ?>">
                                    <button type="submit" onclick="return confirm('Are you sure you want to cancel this reservation?');" style="padding: 5px 10px; cursor: pointer;">Cancel</button>
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
    <a href="../index.php">Back to Home</a>
</body>
</html>