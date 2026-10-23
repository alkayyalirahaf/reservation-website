<?php
require '../config.php';
require '../classes/User.php';
require '../classes/Auth.php';

$db = new Database();
$pdo = $db->getConnection();
$auth = new Auth($pdo);
$userModel = new User($pdo);

session_start();
$auth->requireAdmin(); 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['action'])) {
    $userId = $_POST['user_id'];
    $isActive = ($_POST['action'] === 'activate') ? 1 : 0;
    
    if ($userModel->setActive($userId, $isActive)) {
        $_SESSION['flash_success'] = "User status updated successfully.";
    } else {
        $_SESSION['flash_error'] = "Failed to update user.";
    }
    header("Location: users.php");
    exit;
}

$users = $userModel->getAll();
$activeCount = count(array_filter($users, fn($u) => $u['is_active'] == 1));
$inactiveCount = count($users) - $activeCount;

$cssPath = '../assets/css/style.css';
require '../includes/admin-header.php';
?>

<div style="margin-bottom: 30px;">
    <h1>Manage users</h1>
    <p class="text-muted">Manage who can access the reservation system.</p>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success" style="max-width: 800px;">✓ <?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger" style="max-width: 800px;">! <?= htmlspecialchars($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<h3 style="font-size: 1rem; margin-bottom: 15px;"><?= count($users) ?> registered users • <?= $activeCount ?> active, <?= $inactiveCount ?> inactive</h3>

<?php if (empty($users)): ?>
    <p class="text-muted">No users found.</p>
<?php else: ?>
    <div class="table-container"  style="border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden;">
        <table class="table" style="margin-top: 0;">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td style="font-weight: 500;"><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <?php if ($u['is_active']): ?>
                                <span class="badge badge-confirmed">Active</span>
                            <?php else: ?>
                                <span class="badge badge-cancelled">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <?php if ($u['is_active']): ?>
                                    <input type="hidden" name="action" value="deactivate">
                                    <button type="submit" class="btn btn-outline" style="padding: 5px 15px; font-size: 0.85rem;" onclick="return confirm('Deactivate this user?')">Deactivate</button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="activate">
                                    <button type="submit" class="btn btn-primary" style="padding: 5px 15px; font-size: 0.85rem;">Activate</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>