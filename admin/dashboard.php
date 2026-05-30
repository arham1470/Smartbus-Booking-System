<?php
/**
 * SmartBus Booking System
 * Administrator Dashboard
 * Phase 6 - Full Admin Module
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_ADMIN);

$pageTitle = "Admin Dashboard";
$isDashboard = true;

try {
    $pdo = getDBConnection();

    // System-wide Statistics
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_operators = $pdo->query("SELECT COUNT(*) FROM operators")->fetchColumn();
    $total_buses = $pdo->query("SELECT COUNT(*) FROM buses WHERE status = 'active'")->fetchColumn();
    $total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

    $bookings_today = $pdo->query("
        SELECT COUNT(*) FROM bookings 
        WHERE DATE(booked_at) = CURDATE()
    ")->fetchColumn();

    $revenue_today = $pdo->query("
        SELECT COALESCE(SUM(total_amount), 0) FROM bookings 
        WHERE DATE(booked_at) = CURDATE() AND status != 'cancelled'
    ")->fetchColumn();

    $revenue_month = $pdo->query("
        SELECT COALESCE(SUM(total_amount), 0) FROM bookings 
        WHERE MONTH(booked_at) = MONTH(CURDATE()) AND YEAR(booked_at) = YEAR(CURDATE()) AND status != 'cancelled'
    ")->fetchColumn();

    // Recent activity
    $recent_users = $pdo->query("
        SELECT id, full_name, email, role, created_at 
        FROM users 
        ORDER BY created_at DESC 
        LIMIT 6
    ")->fetchAll();

    $recent_bookings = $pdo->query("
        SELECT b.booking_reference, b.total_amount, b.status, u.full_name, r.origin_city, r.destination_city
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        ORDER BY b.booked_at DESC 
        LIMIT 6
    ")->fetchAll();

} catch (Exception $e) {
    $total_users = $total_operators = $total_buses = $total_bookings = 0;
    $bookings_today = $revenue_today = $revenue_month = 0;
    $recent_users = $recent_bookings = [];
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 1200px;">

        <div style="margin-bottom: 2rem;">
            <h1 style="margin-bottom: 0.25rem;">Administrator Dashboard</h1>
            <p class="text-muted">Complete system overview and control center</p>
        </div>

        <?php display_flashes(); ?>

        <!-- Key Metrics -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <h3><i class="fas fa-users"></i> Total Users</h3>
                <div class="value"><?= number_format($total_users) ?></div>
                <small class="text-muted"><?= $total_operators ?> operators</small>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-bus"></i> Active Buses</h3>
                <div class="value"><?= number_format($total_buses) ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-ticket-alt"></i> Total Bookings</h3>
                <div class="value"><?= number_format($total_bookings) ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-dollar-sign"></i> Revenue (MTD)</h3>
                <div class="value">$<?= number_format($revenue_month, 0) ?></div>
            </div>
        </div>

        <!-- Today's Snapshot -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header"><strong>Today&#39;s Snapshot</strong></div>
            <div class="card-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div>
                    <div style="font-size: 0.9rem; color: var(--text-light);">Bookings Today</div>
                    <div style="font-size: 2.2rem; font-weight: 700; color: var(--primary-dark);"><?= $bookings_today ?></div>
                </div>
                <div>
                    <div style="font-size: 0.9rem; color: var(--text-light);">Revenue Today</div>
                    <div style="font-size: 2.2rem; font-weight: 700; color: var(--success);">$<?= number_format($revenue_today, 0) ?></div>
                </div>
            </div>
        </div>

        <!-- Quick Management -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header"><strong>Quick Management</strong></div>
            <div class="card-body" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="users.php" class="btn btn-primary"><i class="fas fa-users"></i> Manage Users</a>
                <a href="operators.php" class="btn btn-secondary"><i class="fas fa-building"></i> Manage Operators</a>
                <a href="bookings.php" class="btn btn-outline"><i class="fas fa-ticket-alt"></i> Manage Bookings</a>
                <a href="reports.php" class="btn btn-outline"><i class="fas fa-chart-bar"></i> View Reports</a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <!-- Recent Users -->
            <div class="card">
                <div class="card-header"><strong>Recent Users</strong></div>
                <div class="card-body" style="padding: 0;">
                    <table class="table">
                        <thead>
                            <tr><th>Name</th><th>Role</th><th>Joined</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                                    <td><span class="badge badge-info"><?= ucfirst($user['role']) ?></span></td>
                                    <td><small><?= date('M d', strtotime($user['created_at'])) ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="card">
                <div class="card-header"><strong>Recent Bookings</strong></div>
                <div class="card-body" style="padding: 0;">
                    <table class="table">
                        <thead>
                            <tr><th>Ref</th><th>Route</th><th>Amount</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_bookings as $b): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($b['booking_reference']) ?></strong></td>
                                    <td><small><?= htmlspecialchars($b['origin_city']) ?> → <?= htmlspecialchars($b['destination_city']) ?></small></td>
                                    <td>$<?= number_format($b['total_amount'], 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/script.js"></script>
</body>
</html>

