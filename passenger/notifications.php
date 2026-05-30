<?php
/**
 * SmartBus Booking System
 * Passenger Notifications (Phase 7)
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_PASSENGER);

$currentUser = get_logged_in_user();
$pageTitle = "Notifications";
$isDashboard = true;

try {
    $pdo = getDBConnection();

    // Mark all as read when visiting
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$currentUser['id']]);

    $notifications = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 30
    ");
    $notifications->execute([$currentUser['id']]);
    $notifications = $notifications->fetchAll();

} catch (Exception $e) {
    $notifications = [];
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 800px;">
        <h1 style="margin-bottom: 1.5rem;">Notifications</h1>

        <div class="card">
            <?php if (empty($notifications)): ?>
                <div class="card-body text-center" style="padding: 2.5rem;">
                    <p class="text-muted">You have no notifications yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $n): ?>
                    <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #eee;">
                        <div style="font-weight:600; margin-bottom:4px;"><?= htmlspecialchars($n['title']) ?></div>
                        <div style="font-size:0.95rem; color:#444;"><?= htmlspecialchars($n['message']) ?></div>
                        <div style="font-size:0.75rem; color:#888; margin-top:4px;">
                            <?= date('M d, Y • h:i A', strtotime($n['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/script.js"></script>
</body>
</html>
