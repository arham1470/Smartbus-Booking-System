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
    <div style="max-width: 1200px; margin: 0 auto;">

        <?php display_flashes(); ?>

        <!-- Header -->
        <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="margin-bottom: 0.25rem; font-size: 1.85rem;">Operator Dashboard</h1>
                <p class="text-muted" style="margin:0;"><strong><?= htmlspecialchars($operator['company_name']) ?></strong></p>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="buses.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Bus</a>
                <a href="schedules.php" class="btn btn-secondary"><i class="fas fa-plus"></i> New Schedule</a>
            </div>
        </div>

        <!-- Stats -->
        <div class="dashboard-grid" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h3 style="font-size:0.85rem; margin-bottom:4px;">Active Buses</h3>
                        <div class="value" style="font-size:2rem;"><?= $active_buses ?></div>
                    </div>
                    <i class="fas fa-bus" style="font-size:2.4rem; color:#42A5F5; opacity:0.15;"></i>
                </div>
            </div>
            <div class="stat-card">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h3 style="font-size:0.85rem; margin-bottom:4px;">Upcoming Trips</h3>
                        <div class="value" style="font-size:2rem;"><?= $upcoming_schedules ?></div>
                    </div>
                    <i class="fas fa-calendar-alt" style="font-size:2.4rem; color:#42A5F5; opacity:0.15;"></i>
                </div>
            </div>
            <div class="stat-card">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h3 style="font-size:0.85rem; margin-bottom:4px;">Bookings Today</h3>
                        <div class="value" style="font-size:2rem;"><?= $bookings_today ?></div>
                    </div>
                    <i class="fas fa-ticket-alt" style="font-size:2.4rem; color:#42A5F5; opacity:0.15;"></i>
                </div>
            </div>
            <div class="stat-card">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h3 style="font-size:0.85rem; margin-bottom:4px;">Revenue (MTD)</h3>
                        <div class="value" style="font-size:1.7rem;">$<?= number_format($revenue_mtd, 0) ?></div>
                    </div>
                    <i class="fas fa-dollar-sign" style="font-size:2.4rem; color:#42A5F5; opacity:0.15;"></i>
                </div>
            </div>
        </div>

        <!-- Quick Actions + Recent -->
        <div style="display: grid; grid-template-columns: 1fr 1.6fr; gap: 1.5rem;">
            
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header"><strong>Quick Actions</strong></div>
                <div class="card-body" style="display: grid; gap: 10px;">
                    <a href="buses.php" class="btn btn-primary btn-block" style="justify-content: flex-start;">
                        <i class="fas fa-bus"></i> Manage Fleet
                    </a>
                    <a href="schedules.php" class="btn btn-secondary btn-block" style="justify-content: flex-start;">
                        <i class="fas fa-calendar-plus"></i> Create New Schedule
                    </a>
                    <a href="reservations.php" class="btn btn-outline btn-block" style="justify-content: flex-start;">
                        <i class="fas fa-users"></i> View Passenger Reservations
                    </a>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="card">
                <div class="card-header" style="display:flex; justify-content:space-between;">
                    <strong>Recent Bookings</strong>
                    <a href="reservations.php" class="btn btn-sm btn-outline">View All</a>
                </div>
                <div class="card-body" style="padding:0;">
                    <?php if (empty($recent_bookings)): ?>
                        <p class="text-muted" style="padding:1.25rem;">No recent bookings yet.</p>
                    <?php else: ?>
                        <table class="table" style="margin:0;">
                            <thead>
                                <tr>
                                    <th>Passenger</th>
                                    <th>Route</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recent_bookings, 0, 5) as $b): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($b['full_name']) ?></td>
                                        <td style="font-size:0.9rem;"><?= htmlspecialchars($b['origin_city']) ?> → <?= htmlspecialchars($b['destination_city']) ?></td>
                                        <td><strong>$<?= number_format($b['total_amount'], 0) ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

