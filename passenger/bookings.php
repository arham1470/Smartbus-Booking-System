<?php
/**
 * SmartBus Booking System
 * Passenger - My Bookings (History)
 * Phase 4
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_PASSENGER);

$currentUser = get_logged_in_user();
$pageTitle = "My Bookings";
$isDashboard = true;

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        SELECT 
            b.*,
            s.departure_time, s.arrival_time,
            r.origin_city, r.destination_city,
            bs.bus_number, bs.bus_type,
            o.company_name
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        JOIN buses bs ON s.bus_id = bs.id
        JOIN operators o ON bs.operator_id = o.id
        WHERE b.user_id = ?
        ORDER BY b.booked_at DESC
    ");
    $stmt->execute([$currentUser['id']]);
    $bookings = $stmt->fetchAll();

} catch (Exception $e) {
    error_log($e->getMessage());
    $bookings = [];
}

$success = get_flash('success');
$error = get_flash('error');

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 1100px;">

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
                <h1 style="margin-bottom: 0.25rem;">My Bookings</h1>
                <p class="text-muted">All your past and upcoming trips</p>
            </div>
            <a href="search.php" class="btn btn-primary">
                <i class="fas fa-search"></i> Book New Trip
            </a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if (empty($bookings)): ?>
            <div class="card">
                <div class="card-body text-center" style="padding: 3rem;">
                    <i class="fas fa-ticket-alt" style="font-size: 3rem; color: var(--text-muted);"></i>
                    <h3 style="margin-top: 1rem;">No bookings yet</h3>
                    <p class="text-muted">Start by searching for available buses.</p>
                    <a href="search.php" class="btn btn-primary">Search Buses</a>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Route</th>
                            <th>Departure</th>
                            <th>Seats</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <?php
                                $isUpcoming = strtotime($b['departure_time']) > time();
                                $statusClass = match($b['status']) {
                                    'confirmed' => 'badge-success',
                                    'cancelled' => 'badge-danger',
                                    'completed' => 'badge-info',
                                    default => 'badge-warning'
                                };
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($b['booking_reference']) ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($b['origin_city']) ?> → <?= htmlspecialchars($b['destination_city']) ?><br>
                                    <small style="color: var(--text-light);"><?= htmlspecialchars($b['company_name']) ?></small>
                                </td>
                                <td>
                                    <?= date('M d, Y', strtotime($b['departure_time'])) ?><br>
                                    <small><?= date('h:i A', strtotime($b['departure_time'])) ?></small>
                                </td>
                                <td><?= $b['number_of_seats'] ?></td>
                                <td><strong>$<?= number_format($b['total_amount'], 2) ?></strong></td>
                                <td><span class="badge <?= $statusClass ?>"><?= ucfirst($b['status']) ?></span></td>
                                <td style="text-align: right;">
                                    <?php if ($b['status'] === 'confirmed' && $isUpcoming): ?>
                                        <form method="POST" action="../actions/booking_action.php" style="display:inline;" 
                                              onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                            <input type="hidden" name="action" value="cancel_booking">
                                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="view_booking.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline">Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/script.js"></script>
</body>
</html>
