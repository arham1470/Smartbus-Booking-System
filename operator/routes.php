<?php
/**
 * SmartBus Booking System
 * Operator - Routes (View + Create)
 * Phase 5
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_OPERATOR);

$operator = get_current_operator();
$pageTitle = "Routes";
$isDashboard = true;

$success = get_flash('success');
$error = get_flash('error');

try {
    $pdo = getDBConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_route') {
        if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $origin = trim($_POST['origin_city'] ?? '');
            $dest = trim($_POST['destination_city'] ?? '');
            $distance = (float)($_POST['distance_km'] ?? 0);
            $duration = (int)($_POST['estimated_duration_minutes'] ?? 0);
            $base_fare = (float)($_POST['base_fare'] ?? 0);

            if ($origin && $dest) {
                $stmt = $pdo->prepare("
                    INSERT INTO routes (origin_city, destination_city, distance_km, estimated_duration_minutes, base_fare, status, created_at)
                    VALUES (?, ?, ?, ?, ?, 'active', NOW())
                ");
                $stmt->execute([$origin, $dest, $distance, $duration, $base_fare]);
                set_flash('success', 'Route added successfully.');
                header('Location: routes.php');
                exit;
            }
        }
    }

    $routes = $pdo->query("SELECT * FROM routes ORDER BY origin_city, destination_city LIMIT 50")->fetchAll();

} catch (Exception $e) {
    $routes = [];
    $error = "Failed to load routes.";
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 1000px;">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <h1>Routes</h1>
            <button onclick="document.getElementById('addRouteModal').showModal()" class="btn btn-primary">+ Add Route</button>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Route</th>
                        <th>Distance</th>
                        <th>Duration</th>
                        <th>Base Fare</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($routes as $route): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($route['origin_city']) ?> → <?= htmlspecialchars($route['destination_city']) ?></strong></td>
                        <td><?= $route['distance_km'] ? $route['distance_km'] . ' km' : '-' ?></td>
                        <td><?= $route['estimated_duration_minutes'] ? floor($route['estimated_duration_minutes']/60).'h' : '-' ?></td>
                        <td>$<?= number_format($route['base_fare'], 2) ?></td>
                        <td><span class="badge badge-success"><?= ucfirst($route['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<dialog id="addRouteModal" style="width:100%; max-width:480px; border-radius:12px; border:none; padding:0;">
    <div class="card" style="margin:0;">
        <div class="card-header"><strong>Add New Route</strong></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="add_route">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Origin City</label>
                        <input type="text" name="origin_city" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Destination City</label>
                        <input type="text" name="destination_city" class="form-control" required>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Distance (km)</label>
                        <input type="number" name="distance_km" class="form-control" step="0.1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duration (min)</label>
                        <input type="number" name="estimated_duration_minutes" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Base Fare ($)</label>
                        <input type="number" name="base_fare" class="form-control" step="0.01" value="40">
                    </div>
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:1rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">Add Route</button>
                    <button type="button" onclick="document.getElementById('addRouteModal').close()" class="btn btn-outline" style="flex:1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</dialog>

<?php include __DIR__ . '/../includes/footer.php'; ?>
