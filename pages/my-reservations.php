<?php
require '../config.php';
require '../classes/User.php';
require '../classes/Auth.php';
require '../classes/Reservation.php';

$db = new Database();
$pdo = $db->getConnection();
$auth = new Auth($pdo);
$reservationModel = new Reservation($pdo);

session_start();
$auth->requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cancelId = $_POST['cancel_id'];
    $errors = $reservationModel->cancel($cancelId, $_SESSION['user_id']);
    
    if (empty($errors)) {
        $_SESSION['flash_success'] = "Reservation cancelled successfully.";
    } else {
        $_SESSION['flash_error'] = $errors[0];
    }
    
    header("Location: my-reservations.php");
    exit;
}

$reservations = $reservationModel->getByUser($_SESSION['user_id']);
$cssPath = '../assets/css/style.css';
require '../includes/header.php';
?>

<div style="margin-bottom: 30px;">
    <p class="text-muted"><?= htmlspecialchars($_SESSION['user_name']) ?> • <?= date('F j, Y') ?></p>
    <h1>My reservations</h1>
    <p class="text-muted">View your cleaning schedule and reservation status.</p>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success" style="max-width: 800px;">
        ✓ <?= htmlspecialchars($_SESSION['flash_success']) ?>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger" style="max-width: 800px;">
        ! <?= htmlspecialchars($_SESSION['flash_error']) ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px;">
    <h3 style="font-size: 1rem; font-weight: 500;"><?= count($reservations) ?> reservations</h3>
    <a href="slots.php" class="btn btn-primary" style="padding: 8px 15px; font-size: 0.9rem;">+ Book a new slot</a>
</div>

<?php if (empty($reservations)): ?>
    <p class="text-muted">You have no reservations yet.</p>
<?php else: ?>
    <div style="border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden;">
        <table class="table" style="margin-top: 0;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $res): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 500;"><?= date('M j, Y', strtotime($res['slot_date'])) ?></div>
                            <div class="text-muted" style="font-size: 0.8rem;">R-<?= 1000 + $res['id'] ?></div>
                        </td>
                        <td><?= date('H:i', strtotime($res['start_time'])) ?>–<?= date('H:i', strtotime($res['end_time'])) ?></td>
                        <td>
                            <span class="badge badge-<?= $res['status'] ?>">
                                <?= ucfirst(htmlspecialchars($res['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            $slotDateTime = $res['slot_date'] . ' ' . $res['start_time'];
                            $isFuture = strtotime($slotDateTime) > time();
                            
                            if (in_array($res['status'], ['pending', 'confirmed']) && $isFuture): 
                            ?>
                                <form method="POST" action="" style="margin: 0;">
                                    <input type="hidden" name="cancel_id" value="<?= $res['id'] ?>">
                                    <button type="submit" class="btn btn-outline" style="padding: 5px 15px; font-size: 0.85rem;" onclick="return confirm('Are you sure you want to cancel this reservation?');">Cancel</button>
                                </form>
                            <?php elseif ($isFuture === false): ?>
                                <span class="text-muted" style="font-size: 0.85rem;">Past reservation</span>
                            <?php else: ?>
                                <span class="text-muted" style="font-size: 0.85rem;">No action available</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>