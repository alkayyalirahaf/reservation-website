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
$auth->requireLogin();

// Filter logic (date only)
$filterDate = $_GET['date'] ?? null;
if ($filterDate && $filterDate < date('Y-m-d')) {
    $filterDate = date('Y-m-d');
}

$slots = $timeSlotModel->getAvailable($filterDate);

$cssPath = '../assets/css/style.css';
require '../includes/header.php';
?>

<div style="margin-top: 20px;">
    <p class="text-muted">Today • <?= date('F j, Y') ?></p>
    <h1>Available slots</h1>
    <p class="text-muted" style="margin-bottom: 30px;">Choose a cleaning time that works for you. All times are local.</p>

    <!-- Filter Form -->
    <div class="form-group" style="max-width: 300px; margin-bottom: 40px;">
        <label>Filter by date</label>
        <form method="GET" action="" style="display: flex; gap: 10px;">
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($filterDate ?? '') ?>" min="<?= date('Y-m-d') ?>">
            <button type="submit" class="btn btn-outline">Filter</button>
        </form>
    </div>

    <!-- Slots Grid -->
    <?php if (empty($slots)): ?>
        <p class="text-muted">No available slots found for this date.</p>
    <?php else: ?>
        <!-- Group slots by date to show each day's slots together -->
        <?php 
            $groupedSlots = [];
            foreach ($slots as $slot) {
                $groupedSlots[$slot['slot_date']][] = $slot;
            }
        ?>

        <div style="display: flex; flex-direction: column; gap: 40px;">
            <?php foreach ($groupedSlots as $date => $daySlots): ?>
                <div>
                    <!-- Date Header -->
                    <h3 style="margin-bottom: 15px; font-size: 1.2rem; display: flex; justify-content: space-between;">
                        <?= date('l, F j', strtotime($date)) ?> 
                        <span style="color: var(--text-gray); font-weight: 400; font-size: 0.9rem;"><?= date('Y', strtotime($date)) ?></span>
                    </h3>

                    <!-- Cards Container (flex-wrap keeps cards side-by-side) -->
                    <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                        <?php foreach ($daySlots as $slot): ?>
                            <!-- Slot Card -->
                            <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; width: 350px; display: flex; flex-direction: column; gap: 20px; background: #fff;">
                                
                                <div style="display: flex; justify-content: space-between;">
                                    <div>
                                        <p class="text-muted" style="font-size: 0.85rem;">Start time</p>
                                        <p style="font-size: 1.2rem; font-weight: 600;"><?= date('H:i', strtotime($slot['start_time'])) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-muted" style="font-size: 0.85rem;">End time</p>
                                        <p style="font-size: 1.2rem; font-weight: 600;"><?= date('H:i', strtotime($slot['end_time'])) ?></p>
                                    </div>
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span class="text-muted" style="font-size: 0.9rem;"><?= $slot['remaining_capacity'] ?> spots left</span>
                                    
                                    <?php if ($slot['remaining_capacity'] > 0): ?>
                                        <a href="book.php?id=<?= $slot['id'] ?>" class="btn btn-primary" style="padding: 8px 20px; font-size: 0.9rem;">Book</a>
                                    <?php else: ?>
                                        <button disabled class="btn btn-outline" style="padding: 8px 20px; font-size: 0.9rem; background: var(--bg-gray); color: var(--text-gray); cursor: not-allowed;">Full</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require '../includes/footer.php'; ?>


