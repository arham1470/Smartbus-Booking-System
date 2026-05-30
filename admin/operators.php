<?php
/**
 * SmartBus Booking System
 * Admin - Operator Management
 * Phase 6
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_ADMIN);

$pageTitle = "Manage Operators";
$isDashboard = true;

$success = get_flash('success');
$error = get_flash('error');

try {
    $pdo = getDBConnection();

    $operators = $pdo->query("
        SELECT o.*, u.full_name, u.email, u.status as user_status
        FROM operators o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
    ")->fetchAll();

} catch (Exception $e) {
    $operators = [];
    $error = "Failed to load operators.";
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 1100px;">

        <h1 style="margin-bottom: 1.5rem;">Manage Operators</h1>

        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>License</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($operators as $op): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($op['company_name']) ?></strong></td>
                            <td><?= htmlspecialchars($op['contact_person'] ?? $op['full_name']) ?></td>
                            <td><?= htmlspecialchars($op['email']) ?></td>
                            <td><?= htmlspecialchars($op['license_number'] ?? 'N/A') ?></td>
                            <td>
                                <span class="badge <?= $op['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>">
                                    <?= ucfirst($op['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="users.php" class="btn btn-sm btn-outline">Manage User</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/script.js"></script>
</body>
</html>
