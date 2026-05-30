<?php
/**
 * SmartBus Booking System
 * Passenger Dashboard
 * Phase 4 - Fully Functional
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_PASSENGER);

$currentUser = get_logged_in_user();
$pageTitle = "Passenger Dashboard";
$isDashboard = true;

try {
    $pdo = getDBConnection();

    // Stats
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'confirmed'");
    $stmt->execute([$currentUser['id']]);
    $active_bookings = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
    $stmt->execute([$currentUser['id']]);
    $total_trips = $stmt->fetchColumn();

    // Upcoming bookings
    $stmt = $pdo->prepare("
        SELECT b.*, r.origin_city, r.destination_city, s.departure_time
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        WHERE b.user_id = ? AND b.status = 'confirmed' AND s.departure_time > NOW()
        ORDER BY s.departure_time ASC
        LIMIT 3
    ");
    $stmt->execute([$currentUser['id']]);
    $upcoming = $stmt->fetchAll();

} catch (Exception $e) {
    $active_bookings = 0;
    $total_trips = 0;
    $upcoming = [];
}

$success = get_flash('success');
$error = get_flash('error');

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div style="max-width: 1200px; margin: 0 auto;">

        <?php display_flashes(); ?>

        <!-- Welcome Header -->
        <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="margin-bottom: 0.25rem; font-size: 1.85rem;">Welcome back, <?= htmlspecialchars($currentUser['name']) ?>!</h1>
                <p class="text-muted" style="margin: 0;">Here's what's happening with your trips</p>
            </div>
            <a href="search.php" class="btn btn-primary">
                <i class="fas fa-search"></i> Book New Trip
            </a>
        </div>

        <!-- Stats Row -->
        <div class="dashboard-grid" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="margin-bottom: 0.25rem; font-size: 0.9rem;">Active Bookings</h3>
                        <div class="value" style="font-size: 2.1rem; line-height: 1;"><?= $active_bookings ?></div>
                    </div>
                    <i class="fas fa-ticket-alt" style="font-size: 2.2rem; color: #42A5F5; opacity: 0.2;"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="margin-bottom: 0.25rem; font-size: 0.9rem;">Total Trips</h3>
                        <div class="value" style="font-size: 2.1rem; line-height: 1;"><?= $total_trips ?></div>
                    </div>
                    <i class="fas fa-history" style="font-size: 2.2rem; color: #42A5F5; opacity: 0.2;"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="margin-bottom: 0.25rem; font-size: 0.9rem;">Next Trip</h3>
                        <?php if (!empty($upcoming)): ?>
                            <div style="font-size: 1rem; font-weight: 600; color: var(--primary-dark);">
                                <?= date('M d', strtotime($upcoming[0]['departure_time'])) ?>
                            </div>
                        <?php else: ?>
                            <div style="font-size: 0.95rem; color: var(--text-light);">No upcoming trips</div>
                        <?php endif; ?>
                    </div>
                    <i class="fas fa-calendar-check" style="font-size: 2.2rem; color: #42A5F5; opacity: 0.2;"></i>
                </div>
            </div>
        </div>

        <!-- Upcoming Trips -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong>Upcoming Trips</strong>
                    <?php if (!empty($upcoming)): ?>
                        <span class="badge badge-success" style="margin-left: 8px;"><?= count($upcoming) ?></span>
                    <?php endif; ?>
                </div>
                <a href="bookings.php" class="btn btn-sm btn-outline">View All Bookings</a>
            </div>
            <div class="card-body">
                <?php if (empty($upcoming)): ?>
                    <div style="text-align: center; padding: 1.5rem 0;">
                        <p class="text-muted" style="margin-bottom: 1rem;">You don't have any upcoming trips yet.</p>
                        <a href="search.php" class="btn btn-primary btn-sm">Find a Trip</a>
                    </div>
                <?php else: ?>
                    <div style="display: grid; gap: 12px;">
                        <?php foreach ($upcoming as $trip): ?>
                            <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; background: #fff;">
                                <div>
                                    <div style="font-weight: 600; font-size: 1.05rem;">
                                        <?= htmlspecialchars($trip['origin_city']) ?> → <?= htmlspecialchars($trip['destination_city']) ?>
                                    </div>
                                    <div style="font-size: 0.9rem; color: var(--text-light); margin-top: 2px;">
                                        <?= date('D, M d • h:i A', strtotime($trip['departure_time'])) ?>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span class="badge badge-success">Confirmed</span><br>
                                    <a href="view_booking.php?id=<?= $trip['id'] ?>" class="btn btn-sm btn-outline" style="margin-top: 6px; font-size: 0.8rem;">View Ticket</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <strong>Quick Actions</strong>
            </div>
            <div class="card-body" style="display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="search.php" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search Buses
                </a>
                <a href="bookings.php" class="btn btn-outline">
                    <i class="fas fa-list"></i> My Bookings
                </a>
                <a href="profile.php" class="btn btn-outline">
                    <i class="fas fa-user"></i> Edit Profile
                </a>
            </div>
        </div>

    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/script.js"></script>
</body>
</html>

