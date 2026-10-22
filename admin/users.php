<?php
// 1. Require config and classes
require '../config.php';
require '../classes/User.php';
require '../classes/Auth.php';

// 2. DB Connection & Instances
$db = new Database();
$pdo = $db->getConnection();
$auth = new Auth($pdo);
$userModel = new User($pdo);

// 3. Session & Guard
session_start();
$auth->requireAdmin(); 

// 4. Handle Activate/Deactivate (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['action'])) {
    $userId = $_POST['user_id'];
    $isActive = ($_POST['action'] === 'activate') ? 1 : 0;
    
    if ($userModel->setActive($userId, $isActive)) {
        $_SESSION['flash_success'] = "User status updated successfully.";
    } else {
        $_SESSION['flash_error'] = "Failed to update user. They might be an admin.";
    }

    // PRG Pattern
    header("Location: users.php");
    exit;
}

// 5. Fetch all normal users
$users = $userModel->getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin</title>
    <style>
        /* Simple inline CSS for the admin pages */
        body { font-family: Arial, sans-serif; margin: 20px; }
        .nav { margin-bottom: 20px; background: #f4f4f4; padding: 10px; }
        .nav a { margin-right: 15px; text-decoration: none; color: #333; font-weight: bold; }
        .nav a:hover { color: teal; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { border: 1px solid #ddd; padding: 10px; }
        th { background: #f4f4f4; }
        .btn { padding: 5px 10px; color: white; border: none; cursor: pointer; text-decoration: none; border-radius: 4px;}
        .btn-danger { background: #dc3545; }
        .btn-success { background: #28a745; }
        .alert-success { color: green; border: 1px solid green; padding: 10px; background: #e8f5e9; margin-bottom: 15px; }
        .alert-error { color: red; border: 1px solid red; padding: 10px; background: #ffebee; margin-bottom: 15px; }
    </style>
</head>
<body>
    <!-- Navigation Menu -->
    <div class="nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="reservations.php">Manage Reservations</a>
        <a href="users.php">Manage Users</a>
        <a href="slots.php">Manage Time Slots</a>
        <a href="../pages/logout.php" style="color: red; float: right;">Logout</a>
    </div>

    <h2>Manage Users</h2>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert-error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Users Table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Registered At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr><td colspan="6">No users found.</td></tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <?php if ($u['is_active']): ?>
                                <span style="color: green; font-weight: bold;">Active</span>
                            <?php else: ?>
                                <span style="color: red; font-weight: bold;">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($u['created_at']) ?></td>
                        <td>
                            <!-- Activate / Deactivate Form -->
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <?php if ($u['is_active']): ?>
                                    <input type="hidden" name="action" value="deactivate">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to deactivate this user?')">Deactivate</button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="activate">
                                    <button type="submit" class="btn btn-success">Activate</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>