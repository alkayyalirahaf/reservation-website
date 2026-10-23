<?php
require '../config.php';
require '../classes/User.php';
require '../classes/Auth.php';
require '../classes/TimeSlot.php';
require '../classes/Reservation.php';

$db = new Database();
$pdo = $db->getConnection();
$auth = new Auth($pdo);
$timeSlotModel = new TimeSlot($pdo);
$reservationModel = new Reservation($pdo);

session_start();
$auth->requireLogin();

$slotId = $_GET['id'] ?? null;
if (!$slotId) {
    header("Location: slots.php");
    exit;
}

$slot = $timeSlotModel->findById($slotId);
if (!$slot) {
    $_SESSION['flash_error'] = "Time slot not found.";
    header("Location: slots.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address']);
    $note = trim($_POST['note']);
    
    $errors = $reservationModel->create($_SESSION['user_id'], $slotId, $address, $note);

    if (empty($errors)) {
        $_SESSION['flash_success'] = "Reservation requested for " . date('F j', strtotime($slot['slot_date'])) . ". Awaiting confirmation.";
        header("Location: my-reservations.php");
        exit;
    }
}

$cssPath = '../assets/css/style.css';
require '../includes/header.php';
?>

<!-- Back Link -->
<a href="slots.php" style="display: inline-block; margin-bottom: 20px; color: var(--primary-color); font-weight: 500;">
    &lsaquo; Back to available slots
</a>

<div style="margin-bottom: 40px;">
    <h1>Book a reservation</h1>
    <p class="text-muted">Add your cleaning address and review your selected time.</p>
</div>

<!-- Show errors -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger" style="max-width: 800px;">
        ! <?= htmlspecialchars($errors[0]) ?>
    </div>
<?php endif; ?>

<!-- Split Layout: Card on left, Form on right -->
<div class="flex-responsive" style= "display: flex; gap: 40px; align-items: flex-start; max-width: 900px;">
    
    <!-- Selected Slot Card -->
    <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 25px; width: 350px; background: #fff;">
        <h3 style="margin-bottom: 20px;">Selected slot</h3>
        
        <div style="display: flex; margin-bottom: 15px;">
            <span class="text-muted" style="width: 100px;">Date</span>
            <span style="font-weight: 500;"><?= date('M j, Y', strtotime($slot['slot_date'])) ?></span>
        </div>
        <div style="display: flex; margin-bottom: 15px;">
            <span class="text-muted" style="width: 100px;">Time</span>
            <span style="font-weight: 500;"><?= date('H:i', strtotime($slot['start_time'])) ?>–<?= date('H:i', strtotime($slot['end_time'])) ?></span>
        </div>
        <div style="display: flex; margin-bottom: 25px;">
            <span class="text-muted" style="width: 100px;">Duration</span>
            <?php 
                $start = new DateTime($slot['start_time']);
                $end = new DateTime($slot['end_time']);
                $diff = $start->diff($end);
                $hours = $diff->h + ($diff->i / 60);
            ?>
            <span style="font-weight: 500;"><?= $hours ?> hours</span>
        </div>
        
        <p class="text-muted" style="font-size: 0.85rem;">Your reservation will be marked Pending until it is confirmed.</p>
    </div>

    <!-- Booking Form -->
    <div style="flex: 1;">
        <form method="POST" action="">
            <div class="form-group">
                <label>Cleaning address</label>
                <input type="text" name="address" class="form-control" required placeholder="e.g. 128 Maple Street, Apt 4B">
                <small class="text-muted" style="display: block; margin-top: 5px;">Include your apartment number and postal code.</small>
            </div>
            
            <div class="form-group">
                <label>Note (optional)</label>
                <textarea name="note" class="form-control" rows="4" placeholder="Any special instructions..."></textarea>
            </div>

            <p class="text-muted" style="margin-bottom: 20px; font-size: 0.9rem;">You can cancel an upcoming Pending or Confirmed reservation from My Reservations.</p>
            
            <button type="submit" class="btn btn-primary" style="padding: 12px 25px;">Confirm reservation</button>
        </form>
    </div>
</div>

<?php require '../includes/footer.php'; ?>