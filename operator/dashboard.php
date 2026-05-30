<?php
/**
 * SmartBus Booking System
 * Operator Dashboard
 * Phase 5 - Fully Functional
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_OPERATOR);

$operator = get_current_operator();
$pageTitle = "Operator Dashboard";
$isDashboard = true;

$success = get_flash('success');
$error = get_flash('error');

try {
    $pdo = getDBConnection();

    // KPIs
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM buses WHERE operator_id = ? AND status = 'active'");
    $stmt->execute([$operator['id']]);
    $active_buses = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM schedules s
        JOIN buses b ON s.bus_id = b.id
        WHERE b.operator_id = ? AND s.departure_time > NOW() AND s.status = 'scheduled'
    ");
    $stmt->execute([$operator['id']]);
    $upcoming_schedules = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        JOIN buses bs ON s.bus_id = bs.id
        WHERE bs.operator_id = ? AND DATE(b.booked_at) = CURDATE()
    ");
    $stmt->execute([$operator['id']]);
    $bookings_today = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(b.total_amount), 0) FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        JOIN buses bs ON s.bus_id = bs.id
        WHERE bs.operator_id = ? AND MONTH(b.booked_at) = MONTH(CURDATE()) AND YEAR(b.booked_at) = YEAR(CURDATE())
    ");
    $stmt->execute([$operator['id']]);
    $revenue_mtd = $stmt->fetchColumn();

    // Recent bookings
    $stmt = $pdo->prepare("
        SELECT b.booking_reference, b.total_amount, b.status, u.full_name, s.departure_time, r.origin_city, r.destination_city
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        JOIN buses bs ON s.bus_id = bs.id
        WHERE bs.operator_id = ?
        ORDER BY b.booked_at DESC
        LIMIT 5
    ");
    $stmt->execute([$operator['id']]);
    $recent_bookings = $stmt->fetchAll();

} catch (Exception $e) {
    $active_buses = $upcoming_schedules = $bookings_today = 0;
    $revenue_mtd = 0;
    $recent_bookings = [];
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 1100px;">

        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div style="margin-bottom: 2rem;">
            <h1 style="margin-bottom: 0.25rem;">Operator Dashboard</h1>
            <p class="text-muted">Welcome back, <strong><?= htmlspecialchars($operator['company_name']) ?></strong></p>
        </div>

        <div class="dashboard-grid">
            <div class="stat-card">
                <h3><i class="fas fa-bus"></i> Active Buses</h3>
                <div class="value"><?= $active_buses ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-calendar-alt"></i> Upcoming Trips</h3>
                <div class="value"><?= $upcoming_schedules ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-ticket-alt"></i> Bookings Today</h3>
                <div class="value"><?= $bookings_today ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-dollar-sign"></i> Revenue (MTD)</h3>
                <div class="value">$<?= number_format($revenue_mtd, 0) ?></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <strong>Quick Actions</strong>
            </div>
            <div class="card-body" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="buses.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Bus</a>
                <a href="schedules.php" class="btn btn-secondary"><i class="fas fa-route"></i> Create Schedule</a>
                <a href="reservations.php" class="btn btn-outline"><i class="fas fa-list"></i> View Reservations</a>
            </div>
        </div>

        <div class="card" style="margin-top: 1.5rem;">
            <div class="card-header"><strong>Recent Bookings</strong></div>
            <div class="card-body">
                <?php if (empty($recent_bookings)): ?>
                    <p class="text-muted">No recent bookings.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Passenger</th>
                                <th>Route</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_bookings as $b): ?>
                                <tr>
                                    <td><?= htmlspecialchars($b['booking_reference']) ?></td>
                                    <td><?= htmlspecialchars($b['full_name']) ?></td>
                                    <td><?= htmlspecialchars($b['origin_city']) ?> → <?= htmlspecialchars($b['destination_city']) ?></td>
                                    <td>$<?= number_format($b['total_amount'], 2) ?></td>
                                    <td><span class="badge badge-success"><?= ucfirst($b['status']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

