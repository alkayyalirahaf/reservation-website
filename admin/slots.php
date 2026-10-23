<?php
require '../config.php';
require '../classes/User.php';
require '../classes/Auth.php';
require '../classes/TimeSlot.php';

$db = new Database();
$pdo = $db->getConnection();
$auth = new Auth($pdo);
$timeSlotModel = new TimeSlot($pdo);

session_start();
$auth->requireAdmin();

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && ($_POST['action'] === 'add' || $_POST['action'] === 'edit')) {
    $id = $_POST['slot_id'] ?? null;
    $date = $_POST['slot_date'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $capacity = $_POST['capacity'];

    if ($_POST['action'] === 'add') {
        $errors = $timeSlotModel->create($date, $start, $end, $capacity);
        $msg = "Time slot created successfully.";
    } else {
        $errors = $timeSlotModel->update($id, $date, $start, $end, $capacity);
        $msg = "Time slot updated successfully.";
    }

    if (empty($errors)) {
        $_SESSION['flash_success'] = $msg;
    } else {
        $_SESSION['flash_error'] = implode(" ", $errors);
    }
    header("Location: slots.php");
    exit;
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['slot_id'];
    $errors = $timeSlotModel->delete($id);
    if (empty($errors)) {
        $_SESSION['flash_success'] = "Time slot deleted successfully.";
    } else {
        $_SESSION['flash_error'] = $errors[0];
    }
    header("Location: slots.php");
    exit;
}

$slots = $timeSlotModel->getAll();

$cssPath = '../assets/css/style.css';
require '../includes/admin-header.php';
?>

<div style="margin-bottom: 30px;">
    <h1>Manage time slots</h1>
    <p class="text-muted">Add cleaning times and set the capacity for each slot.</p>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success" style="max-width: 800px;">✓ <?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger" style="max-width: 800px;">! <?= htmlspecialchars($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Add/Edit Form -->
<div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; margin-bottom: 30px;">
    <?php 
        $editMode = false;
        $editSlot = null;
        if (isset($_GET['edit_id'])) {
            $editSlot = $timeSlotModel->findById($_GET['edit_id']);
            if ($editSlot) $editMode = true;
        }
    ?>
    <h3 style="margin-bottom: 20px; font-size: 1.1rem;"><?= $editMode ? 'Edit time slot' : 'Add a time slot' ?></h3>
    
    <form method="POST" action="" style="display: flex; gap: 15px; align-items: flex-end;">
        <input type="hidden" name="action" value="<?= $editMode ? 'edit' : 'add' ?>">
        <?php if ($editMode): ?>
            <input type="hidden" name="slot_id" value="<?= $editSlot['id'] ?>">
        <?php endif; ?>

        <div class="form-group" style="margin-bottom: 0; flex: 1;">
            <label>Date</label>
            <input type="date" name="slot_date" class="form-control" required min="<?= date('Y-m-d') ?>" value="<?= $editMode ? $editSlot['slot_date'] : '' ?>">
        </div>
        <div class="form-group" style="margin-bottom: 0; flex: 1;">
            <label>Start time</label>
            <input type="time" name="start_time" class="form-control" required value="<?= $editMode ? $editSlot['start_time'] : '' ?>">
        </div>
        <div class="form-group" style="margin-bottom: 0; flex: 1;">
            <label>End time</label>
            <input type="time" name="end_time" class="form-control" required value="<?= $editMode ? $editSlot['end_time'] : '' ?>">
        </div>
        <div class="form-group" style="margin-bottom: 0; width: 100px;">
            <label>Capacity</label>
            <input type="number" name="capacity" class="form-control" required min="1" value="<?= $editMode ? $editSlot['capacity'] : '1' ?>">
        </div>
        
        <button type="submit" class="btn btn-primary" style="padding: 10px 20px;"><?= $editMode ? 'Update' : 'Add' ?></button>
        <?php if ($editMode): ?>
            <a href="slots.php" class="btn btn-outline" style="padding: 10px 20px;">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<h3 style="font-size: 1rem; margin-bottom: 15px;"><?= count($slots) ?> time slots • All times are local</h3>

<?php if (empty($slots)): ?>
    <p class="text-muted">No time slots found.</p>
<?php else: ?>
    <div class="table-container" style="border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden;">
        <table class="table" style="margin-top: 0;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Start time</th>
                    <th>End time</th>
                    <th>Capacity</th>
                    <th>Remaining</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($slots as $slot): ?>
                    <tr>
                        <td style="font-weight: 500;"><?= date('M j, Y', strtotime($slot['slot_date'])) ?></td>
                        <td><?= date('H:i', strtotime($slot['start_time'])) ?></td>
                        <td><?= date('H:i', strtotime($slot['end_time'])) ?></td>
                        <td><?= $slot['capacity'] ?></td>
                        <td>
                            <?php $remaining = $slot['capacity'] - $slot['active_reservations']; ?>
                            <?= $remaining ?>
                        </td>
                        <td style="display: flex; gap: 8px;">
                            <a href="slots.php?edit_id=<?= $slot['id'] ?>" class="btn btn-outline" style="padding: 5px 12px; font-size: 0.85rem;">Edit</a>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="slot_id" value="<?= $slot['id'] ?>">
                                <button type="submit" class="btn btn-outline" style="padding: 5px 12px; font-size: 0.85rem; color: var(--danger); border-color: var(--danger);" onclick="return confirm('Delete this slot?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>