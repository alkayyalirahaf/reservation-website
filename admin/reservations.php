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
$auth->requireAdmin();

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

$search = $_GET['search'] ?? null;
$statusFilter = $_GET['status'] ?? null;
$reservations = $reservationModel->getAll($search, $statusFilter);

$cssPath = '../assets/css/style.css';
require '../includes/admin-header.php';
?>

<div style="margin-bottom: 30px;">
    <h1>Manage reservations</h1>
    <p class="text-muted">Review cleaning requests and update their status.</p>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success" style="max-width: 800px;">✓ <?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger" style="max-width: 800px;">! <?= htmlspecialchars($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Search & Filter -->
<div style="display: flex; gap: 20px; margin-bottom: 30px; max-width: 800px;">
    <div class="form-group" style="flex: 1; margin-bottom: 0;">
        <label>Search reservations</label>
        <form method="GET" action="" style="display: flex; gap: 10px;">
            <input type="text" name="search" class="form-control" placeholder="Search name or email" value="<?= htmlspecialchars($search ?? '') ?>">
            <select name="status" class="form-control" style="width: 180px;">
                <option value="">All statuses</option>
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed" <?= $statusFilter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <button type="submit" class="btn btn-outline">Filter</button>
        </form>
    </div>
</div>

<h3 style="font-size: 1rem; margin-bottom: 15px;"><?= count($reservations) ?> reservations</h3>

<!-- Table -->
<?php if (empty($reservations)): ?>
    <p class="text-muted">No reservations found matching your criteria.</p>
<?php else: ?>
    <div class="table-container"  style="border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden;">
        <table class="table" style="margin-top: 0;">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $res): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 500;"><?= htmlspecialchars($res['user_name']) ?></div>
                            <div class="text-muted" style="font-size: 0.8rem;">R-<?= 1000 + $res['id'] ?></div>
                        </td>
                        <td><?= htmlspecialchars($res['user_email']) ?></td>
                        <td><?= date('M j, Y', strtotime($res['slot_date'])) ?></td>
                        <td><?= date('H:i', strtotime($res['start_time'])) ?>–<?= date('H:i', strtotime($res['end_time'])) ?></td>
                        <td>
                            <span class="badge badge-<?= $res['status'] ?>"><?= ucfirst(htmlspecialchars($res['status'])) ?></span>
                        </td>
                        <td style="display: flex; gap: 8px;">
                            <?php if ($res['status'] === 'pending'): ?>
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="reservation_id" value="<?= $res['id'] ?>">
                                    <input type="hidden" name="new_status" value="confirmed">
                                    <button type="submit" class="btn btn-primary" style="padding: 5px 12px; font-size: 0.85rem;">Confirm</button>
                                </form>
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="reservation_id" value="<?= $res['id'] ?>">
                                    <input type="hidden" name="new_status" value="rejected">
                                    <button type="submit" class="btn btn-outline" style="padding: 5px 12px; font-size: 0.85rem;">Reject</button>
                                </form>
                            <?php elseif ($res['status'] === 'confirmed'): ?>
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="reservation_id" value="<?= $res['id'] ?>">
                                    <input type="hidden" name="new_status" value="cancelled">
                                    <button type="submit" class="btn btn-outline" style="padding: 5px 12px; font-size: 0.85rem;" onclick="return confirm('Cancel this reservation?')">Cancel</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted" style="font-size: 0.85rem;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>