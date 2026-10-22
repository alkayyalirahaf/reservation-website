<?php
// 1. Require config and classes
require '../config.php';
require '../classes/User.php';
require '../classes/Auth.php';
require '../classes/TimeSlot.php';
require '../classes/Reservation.php';

// 2. DB Connection & Instances
$db = new Database();
$pdo = $db->getConnection();
$auth = new Auth($pdo);
$timeSlotModel = new TimeSlot($pdo);
$reservationModel = new Reservation($pdo);

// 3. Session & Guard
session_start();
$auth->requireLogin();

// 4. Get the Slot ID from URL
$slotId = $_GET['id'] ?? null;
if (!$slotId) {
    header("Location: slots.php");
    exit;
}

// Fetch the slot details
$slot = $timeSlotModel->findById($slotId);
if (!$slot) {
    $_SESSION['flash_error'] = "Time slot not found.";
    header("Location: slots.php");
    exit;
}

$errors = [];

// 5. Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address']);
    $note = trim($_POST['note']);
    
    // Call the Reservation class (which checks capacity, duplicates, etc.)
    $errors = $reservationModel->create($_SESSION['user_id'], $slotId, $address, $note);

    if (empty($errors)) {
        $_SESSION['flash_success'] = "Reservation created successfully! Waiting for admin approval.";
        header("Location: my-reservations.php"); // We will create this page next
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Slot</title>
</head>
<body>
    <h2>Book Cleaning Service</h2>
    
    <!-- Show errors if any -->
    <?php if (!empty($errors)): ?>
        <div style="color: red; border: 1px solid red; padding: 10px; margin-bottom: 20px;">
            <?php foreach ($errors as $e): ?>
                <p><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Slot Summary -->
    <div style="background: #f0f0f0; padding: 15px; margin-bottom: 20px; border-left: 5px solid #008080;">
        <h3>Selected Slot:</h3>
        <p><strong>Date:</strong> <?= htmlspecialchars($slot['slot_date']) ?></p>
        <p><strong>Time:</strong> <?= htmlspecialchars($slot['start_time']) ?> - <?= htmlspecialchars($slot['end_time']) ?></p>
    </div>

    <!-- Booking Form -->
    <form method="POST" action="">
        <div>
            <label>Service Address:</label><br>
            <input type="text" name="address" required style="width: 300px; padding: 5px;">
        </div>
        <br>
        <div>
            <label>Optional Note:</label><br>
            <textarea name="note" rows="4" style="width: 300px; padding: 5px;"></textarea>
        </div>
        <br>
        <button type="submit" style="padding: 10px 20px; background: teal; color: white; border: none;">Confirm Reservation</button>
    </form>

    <br>
    <a href="slots.php">Cancel and go back</a>
</body>
</html>