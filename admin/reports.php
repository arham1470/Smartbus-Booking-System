<?php
/**
 * SmartBus Booking System
 * Admin - Reports
 * Phase 6
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_ADMIN);

$pageTitle = "Reports";
$isDashboard = true;

try {
    $pdo = getDBConnection();

    // Booking Report - Last 30 days
    $booking_report = $pdo->query("
        SELECT DATE(booked_at) as date, COUNT(*) as total_bookings, SUM(total_amount) as revenue
        FROM bookings 
        WHERE booked_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(booked_at)
        ORDER BY date DESC
    ")->fetchAll();

    // Revenue by Operator (simplified)
    $revenue_by_operator = $pdo->query("
        SELECT o.company_name, SUM(b.total_amount) as total_revenue
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        JOIN buses bs ON s.bus_id = bs.id
        JOIN operators o ON bs.operator_id = o.id
        WHERE b.status != 'cancelled'
        GROUP BY o.id, o.company_name
        ORDER BY total_revenue DESC
        LIMIT 10
    ")->fetchAll();

    // Top Routes
    $top_routes = $pdo->query("
        SELECT r.origin_city, r.destination_city, COUNT(*) as bookings
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        GROUP BY r.id
        ORDER BY bookings DESC
        LIMIT 8
    ")->fetchAll();

} catch (Exception $e) {
    $booking_report = $revenue_by_operator = $top_routes = [];
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 1100px;">

        <h1 style="margin-bottom: 1.5rem;">Reports &amp; Analytics</h1>

        <!-- Revenue by Operator -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header"><strong>Revenue by Operator</strong></div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>Operator</th><th>Total Revenue</th></tr></thead>
                    <tbody>
                        <?php foreach ($revenue_by_operator as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['company_name']) ?></td>
                                <td><strong>$<?= number_format($row['total_revenue'], 2) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Routes -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header"><strong>Most Popular Routes</strong></div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>Route</th><th>Bookings</th></tr></thead>
                    <tbody>
                        <?php foreach ($top_routes as $route): ?>
                            <tr>
                                <td><?= htmlspecialchars($route['origin_city']) ?> → <?= htmlspecialchars($route['destination_city']) ?></td>
                                <td><?= $route['bookings'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Daily Booking Trend -->
        <div class="card">
            <div class="card-header"><strong>Daily Booking Trend (Last 30 Days)</strong></div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr><th>Date</th><th>Bookings</th><th>Revenue</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($booking_report as $day): ?>
                            <tr>
                                <td><?= $day['date'] ?></td>
                                <td><?= $day['total_bookings'] ?></td>
                                <td>$<?= number_format($day['revenue'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
