<?php
/**
 * SmartBus Booking System
 * Admin - Booking Management
 * Phase 6
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_ADMIN);

$pageTitle = "Manage Bookings";
$isDashboard = true;

$success = get_flash('success');
$error = get_flash('error');

try {
    $pdo = getDBConnection();

    $bookings = $pdo->query("
        SELECT 
            b.*,
            u.full_name as passenger_name,
            r.origin_city, r.destination_city,
            s.departure_time
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        ORDER BY b.booked_at DESC
        LIMIT 80
    ")->fetchAll();

} catch (Exception $e) {
    $bookings = [];
    $error = "Failed to load bookings.";
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 1200px;">

        <h1 style="margin-bottom: 1.5rem;">Manage All Bookings</h1>

        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Passenger</th>
                        <th>Route</th>
                        <th>Departure</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($b['booking_reference']) ?></strong></td>
                            <td><?= htmlspecialchars($b['passenger_name']) ?></td>
                            <td><?= htmlspecialchars($b['origin_city']) ?> → <?= htmlspecialchars($b['destination_city']) ?></td>
                            <td><?= date('M d, Y', strtotime($b['departure_time'])) ?></td>
                            <td>$<?= number_format($b['total_amount'], 2) ?></td>
                            <td>
                                <span class="badge <?= $b['status'] === 'confirmed' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= ucfirst($b['status']) ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="../actions/admin_booking_action.php" style="display:inline;">
                                    <input type="hidden" name="action" value="update_booking_status">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <select name="status" onchange="this.form.submit()" style="padding:3px; font-size:0.8rem;">
                                        <option value="pending" <?= $b['status']==='pending'?'selected':'' ?>>Pending</option>
                                        <option value="confirmed" <?= $b['status']==='confirmed'?'selected':'' ?>>Confirmed</option>
                                        <option value="completed" <?= $b['status']==='completed'?'selected':'' ?>>Completed</option>
                                        <option value="cancelled" <?= $b['status']==='cancelled'?'selected':'' ?>>Cancelled</option>
                                    </select>
                                </form>

                                <?php if ($b['status'] !== 'cancelled'): ?>
                                <form method="POST" action="../actions/admin_booking_action.php" style="display:inline;" 
                                      onsubmit="return confirm('Cancel this booking?');">
                                    <input type="hidden" name="action" value="cancel_booking">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
