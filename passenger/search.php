<?php
/**
 * SmartBus Booking System
 * Passenger - Search Buses & Schedules
 * Phase 4 - Passenger Module
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_PASSENGER);

$currentUser = get_logged_in_user();
$pageTitle = "Search Buses";
$isDashboard = true;

// Get search parameters
$origin = trim($_GET['origin'] ?? '');
$destination = trim($_GET['destination'] ?? '');
$travel_date = trim($_GET['date'] ?? date('Y-m-d', strtotime('+1 day')));
$max_price = (float)($_GET['max_price'] ?? 0);
$bus_type = trim($_GET['bus_type'] ?? '');

$schedules = [];
$search_performed = false;

if ($origin && $destination && $travel_date) {
    $search_performed = true;

    try {
        $pdo = getDBConnection();

        $sql = "
            SELECT 
                s.id as schedule_id,
                s.departure_time,
                s.arrival_time,
                s.price_per_seat,
                s.available_seats,
                s.status as schedule_status,
                r.origin_city,
                r.destination_city,
                r.distance_km,
                b.id as bus_id,
                b.bus_number,
                b.bus_type,
                b.total_seats,
                o.company_name,
                op.full_name as operator_name
            FROM schedules s
            JOIN routes r ON s.route_id = r.id
            JOIN buses b ON s.bus_id = b.id
            JOIN operators o ON b.operator_id = o.id
            JOIN users op ON o.user_id = op.id
            WHERE r.origin_city LIKE ?
              AND r.destination_city LIKE ?
              AND DATE(s.departure_time) = ?
              AND s.status = 'scheduled'
              AND s.available_seats > 0
        ";

        $params = ["%$origin%", "%$destination%", $travel_date];

        if ($max_price > 0) {
            $sql .= " AND s.price_per_seat <= ?";
            $params[] = $max_price;
        }
        if (!empty($bus_type)) {
            $sql .= " AND b.bus_type = ?";
            $params[] = $bus_type;
        }

        $sql .= " ORDER BY s.departure_time ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $schedules = $stmt->fetchAll();

    } catch (Exception $e) {
        error_log("Search error: " . $e->getMessage());
        set_flash('error', 'An error occurred while searching. Please try again.');
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 1100px;">

        <div style="margin-bottom: 1.5rem;">
            <h1 style="margin-bottom: 0.25rem;">Search Buses</h1>
            <p class="text-muted">Find the perfect trip for your journey</p>
        </div>

        <!-- Search Form -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <form method="GET" action="search.php" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)) auto; gap: 1rem; align-items: end;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">From (Origin)</label>
                        <input type="text" name="origin" class="form-control" placeholder="Chicago" required value="<?= htmlspecialchars($origin) ?>">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">To (Destination)</label>
                        <input type="text" name="destination" class="form-control" placeholder="New York" required value="<?= htmlspecialchars($destination) ?>">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Departure Date</label>
                        <input type="date" name="date" class="form-control" required value="<?= htmlspecialchars($travel_date) ?>">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Max Price ($)</label>
                        <input type="number" name="max_price" class="form-control" placeholder="100" value="<?= $max_price ?: '' ?>">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Bus Type</label>
                        <select name="bus_type" class="form-control">
                            <option value="">Any</option>
                            <option value="Standard" <?= $bus_type === 'Standard' ? 'selected' : '' ?>>Standard</option>
                            <option value="Deluxe" <?= $bus_type === 'Deluxe' ? 'selected' : '' ?>>Deluxe</option>
                            <option value="Sleeper" <?= $bus_type === 'Sleeper' ? 'selected' : '' ?>>Sleeper</option>
                            <option value="Semi-Sleeper" <?= $bus_type === 'Semi-Sleeper' ? 'selected' : '' ?>>Semi-Sleeper</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary btn-block" style="height: 46px;">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($search_performed): ?>
            <h3 style="margin-bottom: 1rem;">
                <?= count($schedules) ?> schedule(s) found for <?= htmlspecialchars($origin) ?> → <?= htmlspecialchars($destination) ?> on <?= date('M d, Y', strtotime($travel_date)) ?>
            </h3>

            <?php if (empty($schedules)): ?>
                <div class="card">
                    <div class="card-body text-center" style="padding: 3rem 1rem;">
                        <i class="fas fa-bus" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                        <h3>No buses found</h3>
                        <p class="text-muted">Try different dates or cities. We have routes between major cities.</p>
                        <a href="search.php" class="btn btn-outline">Clear Search</a>
                    </div>
                </div>
            <?php else: ?>
                <div style="display: grid; gap: 1.25rem;">
                    <?php foreach ($schedules as $sch): ?>
                        <div class="card">
                            <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                                <div style="flex: 1; min-width: 240px;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                        <strong style="font-size: 1.1rem;"><?= htmlspecialchars($sch['origin_city']) ?></strong>
                                        <i class="fas fa-arrow-right" style="color: var(--primary);"></i>
                                        <strong style="font-size: 1.1rem;"><?= htmlspecialchars($sch['destination_city']) ?></strong>
                                    </div>
                                    <div style="font-size: 0.9rem; color: var(--text-light);">
                                        <strong><?= date('h:i A', strtotime($sch['departure_time'])) ?></strong> → 
                                        <?= date('h:i A', strtotime($sch['arrival_time'])) ?> 
                                        (<?= htmlspecialchars($sch['bus_type']) ?>)
                                    </div>
                                    <div style="margin-top: 0.5rem; font-size: 0.85rem;">
                                        <span class="badge badge-info"><?= htmlspecialchars($sch['company_name']) ?></span>
                                        <span style="margin-left: 0.5rem; color: var(--text-light);">
                                            Bus #<?= htmlspecialchars($sch['bus_number']) ?>
                                        </span>
                                    </div>
                                </div>

                                <div style="text-align: right; min-width: 160px;">
                                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-dark);">
                                        $<?= number_format($sch['price_per_seat'], 2) ?>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--text-light);">per seat</div>
                                    
                                    <div style="margin: 0.5rem 0;">
                                        <span class="badge <?= $sch['available_seats'] > 10 ? 'badge-success' : 'badge-warning' ?>">
                                            <?= $sch['available_seats'] ?> seats left
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <a href="book.php?schedule_id=<?= $sch['schedule_id'] ?>" 
                                       class="btn btn-primary btn-lg">
                                        Book Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <h3 style="margin-bottom: 1rem;">Popular Routes</h3>
                    <p class="text-muted">Try searching for one of these popular routes:</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1rem;">
                        <a href="search.php?origin=Chicago&destination=New+York&date=<?= $travel_date ?>" class="btn btn-outline btn-sm">Chicago → New York</a>
                        <a href="search.php?origin=New+York&destination=Boston&date=<?= $travel_date ?>" class="btn btn-outline btn-sm">New York → Boston</a>
                        <a href="search.php?origin=Chicago&destination=Detroit&date=<?= $travel_date ?>" class="btn btn-outline btn-sm">Chicago → Detroit</a>
                        <a href="search.php?origin=Los+Angeles&destination=San+Francisco&date=<?= $travel_date ?>" class="btn btn-outline btn-sm">LA → San Francisco</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/script.js"></script>
</body>
</html>
