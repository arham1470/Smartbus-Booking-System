<?php
/**
 * SmartBus Booking System
 * Operator - Manage Buses (Full CRUD)
 * Phase 5
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_OPERATOR);

$operator = get_current_operator();
if (!$operator) {
    die("Operator profile not found.");
}

$pageTitle = "Manage Buses";
$isDashboard = true;

$success = get_flash('success');
$error = get_flash('error');

try {
    $pdo = getDBConnection();

    // Get all buses for this operator
    $stmt = $pdo->prepare("
        SELECT * FROM buses 
        WHERE operator_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$operator['id']]);
    $buses = $stmt->fetchAll();

} catch (Exception $e) {
    $buses = [];
    $error = "Failed to load buses.";
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 1100px;">

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
                <h1 style="margin-bottom: 0.25rem;">Manage Your Fleet</h1>
                <p class="text-muted"><?= htmlspecialchars($operator['company_name']) ?></p>
            </div>
            <button onclick="document.getElementById('addBusModal').showModal()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Bus
            </button>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="card">
            <?php if (empty($buses)): ?>
                <div class="card-body text-center" style="padding: 3rem;">
                    <i class="fas fa-bus" style="font-size: 3rem; color: var(--text-muted);"></i>
                    <h3 style="margin-top: 1rem;">No buses yet</h3>
                    <p class="text-muted">Add your first bus to start creating schedules.</p>
                    <button onclick="document.getElementById('addBusModal').showModal()" class="btn btn-primary">Add Bus</button>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Bus Number</th>
                            <th>Type</th>
                            <th>Seats</th>
                            <th>Layout</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($buses as $bus): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($bus['bus_number']) ?></strong></td>
                                <td><?= htmlspecialchars($bus['bus_type']) ?></td>
                                <td><?= $bus['total_seats'] ?></td>
                                <td><?= htmlspecialchars($bus['seat_layout'] ?: '-') ?></td>
                                <td>
                                    <span class="badge <?= $bus['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>">
                                        <?= ucfirst($bus['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button onclick="editBus(<?= htmlspecialchars(json_encode($bus)) ?>)" class="btn btn-sm btn-outline">Edit</button>
                                    
                                    <form method="POST" action="../actions/bus_action.php" style="display:inline;" 
                                          onsubmit="return confirm('Delete this bus? This cannot be undone if it has no future schedules.');">
                                        <input type="hidden" name="action" value="delete_bus">
                                        <input type="hidden" name="bus_id" value="<?= $bus['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Add Bus Modal -->
<dialog id="addBusModal" style="width: 100%; max-width: 520px; border-radius: 12px; border: none; padding: 0;">
    <div class="card" style="margin:0;">
        <div class="card-header" style="display:flex; justify-content:space-between;">
            <strong>Add New Bus</strong>
            <button onclick="document.getElementById('addBusModal').close()" style="background:none; border:none; font-size:1.3rem; cursor:pointer;">&times;</button>
        </div>
        <div class="card-body">
            <form action="../actions/bus_action.php" method="POST">
                <input type="hidden" name="action" value="add_bus">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="form-group">
                    <label class="form-label">Bus Number / Plate</label>
                    <input type="text" name="bus_number" class="form-control" required placeholder="EB-105">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Bus Type</label>
                        <select name="bus_type" class="form-control">
                            <option value="Standard">Standard</option>
                            <option value="Deluxe">Deluxe</option>
                            <option value="Sleeper">Sleeper</option>
                            <option value="Semi-Sleeper">Semi-Sleeper</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Total Seats</label>
                        <input type="number" name="total_seats" class="form-control" value="40" min="10" max="60" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Seat Layout (optional)</label>
                    <input type="text" name="seat_layout" class="form-control" placeholder="2x2">
                </div>

                <div class="form-group">
                    <label class="form-label">Amenities</label>
                    <textarea name="amenities" class="form-control" rows="2" placeholder="AC, WiFi, Charging Ports"></textarea>
                </div>

                <div style="display: flex; gap: 0.75rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">Add Bus</button>
                    <button type="button" onclick="document.getElementById('addBusModal').close()" class="btn btn-outline" style="flex:1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</dialog>

<!-- Edit Bus Modal -->
<dialog id="editBusModal" style="width: 100%; max-width: 520px; border-radius: 12px; border: none; padding: 0;">
    <div class="card" style="margin:0;">
        <div class="card-header" style="display:flex; justify-content:space-between;">
            <strong>Edit Bus</strong>
            <button onclick="document.getElementById('editBusModal').close()" style="background:none; border:none; font-size:1.3rem; cursor:pointer;">&times;</button>
        </div>
        <div class="card-body">
            <form action="../actions/bus_action.php" method="POST">
                <input type="hidden" name="action" value="update_bus">
                <input type="hidden" name="bus_id" id="edit_bus_id">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="form-group">
                    <label class="form-label">Bus Number</label>
                    <input type="text" name="bus_number" id="edit_bus_number" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Bus Type</label>
                        <select name="bus_type" id="edit_bus_type" class="form-control">
                            <option value="Standard">Standard</option>
                            <option value="Deluxe">Deluxe</option>
                            <option value="Sleeper">Sleeper</option>
                            <option value="Semi-Sleeper">Semi-Sleeper</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Total Seats</label>
                        <input type="number" name="total_seats" id="edit_total_seats" class="form-control" min="10" max="60" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="edit_status" class="form-control">
                        <option value="active">Active</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="retired">Retired</option>
                    </select>
                </div>

                <div style="display: flex; gap: 0.75rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">Save Changes</button>
                    <button type="button" onclick="document.getElementById('editBusModal').close()" class="btn btn-outline" style="flex:1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</dialog>

<script>
function editBus(bus) {
    document.getElementById('edit_bus_id').value = bus.id;
    document.getElementById('edit_bus_number').value = bus.bus_number;
    document.getElementById('edit_bus_type').value = bus.bus_type;
    document.getElementById('edit_total_seats').value = bus.total_seats;
    document.getElementById('edit_status').value = bus.status;
    document.getElementById('editBusModal').showModal();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
