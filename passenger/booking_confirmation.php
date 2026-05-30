<?php
/**
 * SmartBus Booking System
 * Booking Confirmation / Ticket View (Phase 7)
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_PASSENGER);

$currentUser = get_logged_in_user();
$pageTitle = "Booking Confirmed";
$isDashboard = true;

$booking_ref = $_GET['ref'] ?? '';

if (!$booking_ref) {
    header('Location: bookings.php');
    exit;
}

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        SELECT b.*, s.departure_time, s.arrival_time,
               r.origin_city, r.destination_city,
               bs.bus_number, o.company_name
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        JOIN buses bs ON s.bus_id = bs.id
        JOIN operators o ON bs.operator_id = o.id
        WHERE b.booking_reference = ? AND b.user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$booking_ref, $currentUser['id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        header('Location: bookings.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT seat_number, passenger_name FROM booking_seats WHERE booking_id = ?");
    $stmt->execute([$booking['id']]);
    $seats = $stmt->fetchAll();

} catch (Exception $e) {
    header('Location: bookings.php');
    exit;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 620px; text-align: center;">

        <div style="margin: 2rem 0;">
            <i class="fas fa-check-circle" style="font-size: 4rem; color: #22c55e;"></i>
            <h1 style="margin-top: 1rem; color: #15803d;">Booking Confirmed!</h1>
            <p style="font-size: 1.1rem;">Your ticket has been issued.</p>
        </div>

        <!-- Ticket -->
        <div class="card" style="text-align: left; border: 3px dashed #1565C0; padding: 1.5rem;">
            <div style="text-align:center; margin-bottom:1rem;">
                <strong style="font-size:1.4rem;"><?= htmlspecialchars($booking['booking_reference']) ?></strong>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div>
                    <strong>From</strong><br>
                    <?= htmlspecialchars($booking['origin_city']) ?>
                </div>
                <div>
                    <strong>To</strong><br>
                    <?= htmlspecialchars($booking['destination_city']) ?>
                </div>
            </div>

            <div style="margin-bottom:1rem;">
                <strong>Departure</strong><br>
                <?= date('D, M d, Y • h:i A', strtotime($booking['departure_time'])) ?>
            </div>

            <div style="margin-bottom:1rem;">
                <strong>Operator</strong><br>
                <?= htmlspecialchars($booking['company_name']) ?> • Bus <?= htmlspecialchars($booking['bus_number']) ?>
            </div>

            <div>
                <strong>Passengers</strong>
                <ul style="margin-top:0.5rem; padding-left:1.2rem;">
                    <?php foreach ($seats as $s): ?>
                        <li><?= htmlspecialchars($s['passenger_name']) ?> (<?= $s['seat_number'] ?>)</li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid #ddd; font-size:1.1rem;">
                <strong>Total Paid: $<?= number_format($booking['total_amount'], 2) ?></strong>
            </div>
        </div>

        <div style="margin-top: 1.5rem;">
            <a href="bookings.php" class="btn btn-primary">View All My Bookings</a>
            <a href="search.php" class="btn btn-outline">Book Another Trip</a>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
