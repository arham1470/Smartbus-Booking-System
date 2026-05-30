<?php
/**
 * SmartBus Booking System
 * Operator - View Passenger Reservations
 * Phase 5
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_OPERATOR);

$operator = get_current_operator();
if (!$operator) {
    die("Operator profile not found.");
}

$pageTitle = "Reservations";
$isDashboard = true;

$success = get_flash('success');
$error = get_flash('error');

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        SELECT 
            b.*,
            u.full_name as passenger_name,
            u.email as passenger_email,
            s.departure_time,
            r.origin_city, r.destination_city,
            bs.bus_number
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        JOIN buses bs ON s.bus_id = bs.id
        WHERE bs.operator_id = ?
        ORDER BY b.booked_at DESC
        LIMIT 100
    ");
    $stmt->execute([$operator['id']]);
    $reservations = $stmt->fetchAll();

} catch (Exception $e) {
    $reservations = [];
    $error = "Failed to load reservations.";
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 1200px;">

        <h1 style="margin-bottom: 1.5rem;">Passenger Reservations</h1>

        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="card">
            <?php if (empty($reservations)): ?>
                <div class="card-body text-center" style="padding: 3rem;">
                    <p class="text-muted">No reservations yet for your fleet.</p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Booking Ref</th>
                            <th>Passenger</th>
                            <th>Route</th>
                            <th>Departure</th>
                            <th>Seats</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $res): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($res['booking_reference']) ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($res['passenger_name']) ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($res['passenger_email']) ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($res['origin_city']) ?> → <?= htmlspecialchars($res['destination_city']) ?><br>
                                    <small><?= htmlspecialchars($res['bus_number']) ?></small>
                                </td>
                                <td><?= date('M d, Y • h:i A', strtotime($res['departure_time'])) ?></td>
                                <td><?= $res['number_of_seats'] ?></td>
                                <td><strong>$<?= number_format($res['total_amount'], 2) ?></strong></td>
                                <td>
                                    <span class="badge <?= $res['status'] === 'confirmed' ? 'badge-success' : 'badge-danger' ?>">
                                        <?= ucfirst($res['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
