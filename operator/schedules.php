<?php
/**
 * SmartBus Booking System
 * Operator - Manage Schedules (Full CRUD)
 * Phase 5
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_OPERATOR);

$operator = get_current_operator();
if (!$operator) {
    die("Operator profile not found.");
}

$pageTitle = "Manage Schedules";
$isDashboard = true;

$success = get_flash('success');
$error = get_flash('error');

try {
    $pdo = getDBConnection();

    // Get operator's buses
    $stmt = $pdo->prepare("SELECT * FROM buses WHERE operator_id = ? AND status = 'active' ORDER BY bus_number");
    $stmt->execute([$operator['id']]);
    $buses = $stmt->fetchAll();

    // Get routes (all routes for now - operators can schedule on any)
    $routes = $pdo->query("SELECT * FROM routes WHERE status = 'active' ORDER BY origin_city, destination_city")->fetchAll();

    // Get schedules for this operator's buses
    $stmt = $pdo->prepare("
        SELECT 
            s.*,
            r.origin_city, r.destination_city,
            b.bus_number, b.bus_type
        FROM schedules s
        JOIN buses b ON s.bus_id = b.id
        JOIN routes r ON s.route_id = r.id
        WHERE b.operator_id = ?
        ORDER BY s.departure_time DESC
        LIMIT 50
    ");
    $stmt->execute([$operator['id']]);
    $schedules = $stmt->fetchAll();

} catch (Exception $e) {
    $buses = $routes = $schedules = [];
    $error = "Failed to load data.";
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 1200px;">

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
                <h1 style="margin-bottom: 0.25rem;">Manage Schedules</h1>
                <p class="text-muted">Create and manage your bus departures</p>
            </div>
            <button onclick="document.getElementById('addScheduleModal').showModal()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Schedule
            </button>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="card">
            <?php if (empty($schedules)): ?>
                <div class="card-body text-center" style="padding: 3rem;">
                    <p class="text-muted">No schedules yet. Create your first departure.</p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Route</th>
                            <th>Bus</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th>Price</th>
                            <th>Seats Left</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $sch): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($sch['origin_city']) ?></strong> → 
                                    <?= htmlspecialchars($sch['destination_city']) ?>
                                </td>
                                <td><?= htmlspecialchars($sch['bus_number']) ?> (<?= $sch['bus_type'] ?>)</td>
                                <td><?= date('M d, Y • h:i A', strtotime($sch['departure_time'])) ?></td>
                                <td><?= date('M d, Y • h:i A', strtotime($sch['arrival_time'])) ?></td>
                                <td><strong>$<?= number_format($sch['price_per_seat'], 2) ?></strong></td>
                                <td>
                                    <span class="badge <?= $sch['available_seats'] > 0 ? 'badge-success' : 'badge-danger' ?>">
                                        <?= $sch['available_seats'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $sch['status'] === 'scheduled' ? 'badge-success' : 'badge-warning' ?>">
                                        <?= ucfirst($sch['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button onclick="editSchedule(<?= htmlspecialchars(json_encode($sch)) ?>)" class="btn btn-sm btn-outline">Edit</button>
                                    
                                    <?php if ($sch['status'] !== 'cancelled'): ?>
                                    <form method="POST" action="../actions/schedule_action.php" style="display:inline;"
                                          onsubmit="return confirm('Cancel this entire schedule? All bookings will be cancelled.');">
                                        <input type="hidden" name="action" value="cancel_schedule">
                                        <input type="hidden" name="schedule_id" value="<?= $sch['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Add Schedule Modal -->
<dialog id="addScheduleModal" style="width:100%; max-width:620px; border-radius:12px; border:none; padding:0;">
    <div class="card" style="margin:0;">
        <div class="card-header" style="display:flex;justify-content:space-between;">
            <strong>Create New Schedule</strong>
            <button onclick="document.getElementById('addScheduleModal').close()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;">&times;</button>
        </div>
        <div class="card-body">
            <form action="../actions/schedule_action.php" method="POST">
                <input type="hidden" name="action" value="add_schedule">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Bus</label>
                        <select name="bus_id" class="form-control" required>
                            <?php foreach ($buses as $bus): ?>
                                <option value="<?= $bus['id'] ?>"><?= htmlspecialchars($bus['bus_number']) ?> (<?= $bus['bus_type'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Route</label>
                        <select name="route_id" class="form-control" required>
                            <?php foreach ($routes as $route): ?>
                                <option value="<?= $route['id'] ?>">
                                    <?= htmlspecialchars($route['origin_city']) ?> → <?= htmlspecialchars($route['destination_city']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Departure Time</label>
                        <input type="datetime-local" name="departure_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Arrival Time</label>
                        <input type="datetime-local" name="arrival_time" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Price Per Seat ($)</label>
                    <input type="number" name="price_per_seat" step="0.01" class="form-control" value="45.00" required>
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:1rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">Create Schedule</button>
                    <button type="button" onclick="document.getElementById('addScheduleModal').close()" class="btn btn-outline" style="flex:1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</dialog>

<!-- Edit Schedule Modal -->
<dialog id="editScheduleModal" style="width:100%; max-width:620px; border-radius:12px; border:none; padding:0;">
    <div class="card" style="margin:0;">
        <div class="card-header" style="display:flex;justify-content:space-between;">
            <strong>Edit Schedule</strong>
            <button onclick="document.getElementById('editScheduleModal').close()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;">&times;</button>
        </div>
        <div class="card-body">
            <form action="../actions/schedule_action.php" method="POST">
                <input type="hidden" name="action" value="update_schedule">
                <input type="hidden" name="schedule_id" id="edit_schedule_id">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Departure Time</label>
                        <input type="datetime-local" name="departure_time" id="edit_departure_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Arrival Time</label>
                        <input type="datetime-local" name="arrival_time" id="edit_arrival_time" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Price Per Seat ($)</label>
                    <input type="number" name="price_per_seat" id="edit_price" step="0.01" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="edit_status" class="form-control">
                        <option value="scheduled">Scheduled</option>
                        <option value="boarding">Boarding</option>
                        <option value="departed">Departed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:1rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">Save Changes</button>
                    <button type="button" onclick="document.getElementById('editScheduleModal').close()" class="btn btn-outline" style="flex:1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</dialog>

<script>
function editSchedule(sch) {
    document.getElementById('edit_schedule_id').value = sch.id;
    document.getElementById('edit_departure_time').value = sch.departure_time.replace(' ', 'T').slice(0, 16);
    document.getElementById('edit_arrival_time').value = sch.arrival_time.replace(' ', 'T').slice(0, 16);
    document.getElementById('edit_price').value = sch.price_per_seat;
    document.getElementById('edit_status').value = sch.status;
    document.getElementById('editScheduleModal').showModal();
}
</script>

<script src="<?= BASE_URL ?>/assets/js/script.js"></script>
</body>
</html>
