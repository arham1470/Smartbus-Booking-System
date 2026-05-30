<?php
/**
 * SmartBus Booking System
 * Passenger Dashboard
 * Phase 4 - Fully Functional
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_PASSENGER);

$currentUser = get_current_user();
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
    <div class="container" style="max-width: 1100px;">

        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div style="margin-bottom: 2rem;">
            <h1 style="margin-bottom: 0.25rem;">Welcome back, <?= htmlspecialchars($currentUser['name']) ?>!</h1>
            <p class="text-muted">Here's an overview of your travel activity.</p>
        </div>

        <!-- Stats -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <h3><i class="fas fa-ticket-alt"></i> Active Bookings</h3>
                <div class="value"><?= $active_bookings ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-history"></i> Total Trips</h3>
                <div class="value"><?= $total_trips ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-search"></i> Quick Action</h3>
                <a href="search.php" class="btn btn-primary btn-sm" style="margin-top: 0.5rem;">Search Buses</a>
            </div>
        </div>

        <!-- Upcoming Trips -->
        <div class="card">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                <strong>Upcoming Trips</strong>
                <a href="bookings.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($upcoming)): ?>
                    <p class="text-muted">You have no upcoming trips. <a href="search.php">Book your next journey</a>.</p>
                <?php else: ?>
                    <div style="display: grid; gap: 1rem;">
                        <?php foreach ($upcoming as $trip): ?>
                            <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 1rem; display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <strong><?= htmlspecialchars($trip['origin_city']) ?> → <?= htmlspecialchars($trip['destination_city']) ?></strong><br>
                                    <small><?= date('M d, Y • h:i A', strtotime($trip['departure_time'])) ?></small>
                                </div>
                                <div>
                                    <span class="badge badge-success">Confirmed</span>
                                    <a href="view_booking.php?id=<?= $trip['id'] ?>" class="btn btn-sm btn-outline" style="margin-left:0.5rem;">Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="search.php" class="btn btn-primary">Search for New Trips</a>
                <a href="profile.php" class="btn btn-outline">Manage Profile</a>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

