<?php
/**
 * SmartBus Booking System
 * Admin - User Management
 * Phase 6
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_ADMIN);

$pageTitle = "Manage Users";
$isDashboard = true;

$success = get_flash('success');
$error = get_flash('error');

try {
    $pdo = getDBConnection();

    $users = $pdo->query("
        SELECT id, full_name, email, phone, role, status, created_at 
        FROM users 
        ORDER BY created_at DESC 
        LIMIT 100
    ")->fetchAll();

} catch (Exception $e) {
    $users = [];
    $error = "Failed to load users.";
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 1200px;">

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
                <h1 style="margin-bottom: 0.25rem;">Manage Users</h1>
                <p class="text-muted">All passengers, operators, and administrators</p>
            </div>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($user['full_name']) ?></strong></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
                            <td><span class="badge badge-info"><?= ucfirst($user['role']) ?></span></td>
                            <td>
                                <span class="badge <?= $user['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= ucfirst($user['status']) ?>
                                </span>
                            </td>
                            <td><small><?= date('M d, Y', strtotime($user['created_at'])) ?></small></td>
                            <td>
                                <button onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>)" class="btn btn-sm btn-outline">Edit</button>

                                <!-- Status Form -->
                                <form method="POST" action="../actions/admin_user_action.php" style="display:inline;">
                                    <input type="hidden" name="action" value="update_user_status">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <select name="status" onchange="this.form.submit()" style="padding: 4px; font-size: 0.8rem;">
                                        <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                        <option value="suspended" <?= $user['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                    </select>
                                </form>

                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" action="../actions/admin_user_action.php" style="display:inline;" 
                                      onsubmit="return confirm('Permanently delete this user?');">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Edit User Modal -->
<dialog id="editUserModal" style="width:100%; max-width:520px; border-radius:12px; border:none; padding:0;">
    <div class="card" style="margin:0;">
        <div class="card-header" style="display:flex; justify-content:space-between;">
            <strong>Edit User</strong>
            <button onclick="document.getElementById('editUserModal').close()" style="background:none; border:none; font-size:1.4rem; cursor:pointer;">&times;</button>
        </div>
        <div class="card-body">
            <form action="../actions/admin_user_action.php" method="POST">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" id="edit_user_id">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" id="edit_phone" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" id="edit_role" class="form-control">
                        <option value="passenger">Passenger</option>
                        <option value="operator">Operator</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:1rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">Save Changes</button>
                    <button type="button" onclick="document.getElementById('editUserModal').close()" class="btn btn-outline" style="flex:1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</dialog>

<script>
function editUser(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_full_name').value = user.full_name;
    document.getElementById('edit_phone').value = user.phone || '';
    document.getElementById('edit_role').value = user.role;
    document.getElementById('editUserModal').showModal();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
