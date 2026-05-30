<?php
/**
 * SmartBus Booking System
 * View Single Booking Details
 * Phase 4
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_PASSENGER);

$currentUser = get_logged_in_user();
$pageTitle = "Booking Details";
$isDashboard = true;

$booking_id = (int)($_GET['id'] ?? 0);

if (!$booking_id) {
    header('Location: bookings.php');
    exit;
}

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
        WHERE b.id = ? AND b.user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$booking_id, $currentUser['id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        set_flash('error', 'Booking not found.');
        header('Location: bookings.php');
        exit;
    }

    // Get seat details
    $stmt = $pdo->prepare("SELECT seat_number, passenger_name FROM booking_seats WHERE booking_id = ?");
    $stmt->execute([$booking_id]);
    $seats = $stmt->fetchAll();

} catch (Exception $e) {
    error_log($e->getMessage());
    set_flash('error', 'Unable to load booking details.');
    header('Location: bookings.php');
    exit;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 800px;">

        <div style="margin-bottom: 1.5rem;">
            <a href="bookings.php" style="color: var(--text-light);">← Back to My Bookings</a>
            <h1 style="margin-top: 0.5rem;">Booking <?= htmlspecialchars($booking['booking_reference']) ?></h1>
        </div>

        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <strong>Route</strong><br>
                        <?= htmlspecialchars($booking['origin_city']) ?> → <?= htmlspecialchars($booking['destination_city']) ?>
                    </div>
                    <div>
                        <strong>Status</strong><br>
                        <span class="badge <?= $booking['status'] === 'confirmed' ? 'badge-success' : 'badge-danger' ?>">
                            <?= ucfirst($booking['status']) ?>
                        </span>
                    </div>
                    <div>
                        <strong>Departure</strong><br>
                        <?= date('D, M d, Y • h:i A', strtotime($booking['departure_time'])) ?>
                    </div>
                    <div>
                        <strong>Arrival</strong><br>
                        <?= date('D, M d, Y • h:i A', strtotime($booking['arrival_time'])) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>Passengers &amp; Seats</strong></div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr><th>Seat</th><th>Passenger Name</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($seats as $seat): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($seat['seat_number']) ?></strong></td>
                                <td><?= htmlspecialchars($seat['passenger_name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong>Total Paid:</strong> $<?= number_format($booking['total_amount'], 2) ?>
                </div>
                <?php if ($booking['status'] === 'confirmed' && strtotime($booking['departure_time']) > time()): ?>
                    <form method="POST" action="../actions/booking_action.php" onsubmit="return confirm('Cancel this booking?');">
                        <input type="hidden" name="action" value="cancel_booking">
                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <button type="submit" class="btn btn-danger">Cancel Booking</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
