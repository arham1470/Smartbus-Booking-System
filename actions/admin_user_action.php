<?php
/**
 * SmartBus Booking System
 * Admin - User Management Actions
 * Phase 6 - Admin Module
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_ADMIN);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/users.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'update_user') {
    handle_update_user();
} elseif ($action === 'update_user_status') {
    handle_update_user_status();
} elseif ($action === 'delete_user') {
    handle_delete_user();
} else {
    header('Location: ../admin/users.php');
    exit;
}

function handle_update_user() {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security check failed.');
        header('Location: ../admin/users.php');
        exit;
    }

    $user_id = (int)($_POST['user_id'] ?? 0);
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'passenger';

    if (!$user_id || empty($full_name)) {
        set_flash('error', 'Invalid user data.');
        header('Location: ../admin/users.php');
        exit;
    }

    try {
        $pdo = getDBConnection();

        $stmt = $pdo->prepare("
            UPDATE users 
            SET full_name = ?, phone = ?, role = ?
            WHERE id = ?
        ");
        $stmt->execute([$full_name, $phone, $role, $user_id]);

        set_flash('success', 'User updated successfully.');
        header('Location: ../admin/users.php');
        exit;

    } catch (Exception $e) {
        set_flash('error', 'Failed to update user.');
        header('Location: ../admin/users.php');
        exit;
    }
}

function handle_update_user_status() {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security check failed.');
        header('Location: ../admin/users.php');
        exit;
    }

    $user_id = (int)($_POST['user_id'] ?? 0);
    $status = $_POST['status'] ?? 'active';

    if (!$user_id || !in_array($status, ['active', 'inactive', 'suspended'])) {
        set_flash('error', 'Invalid status.');
        header('Location: ../admin/users.php');
        exit;
    }

    // Prevent admin from deactivating themselves
    if ($user_id == $_SESSION['user_id'] && $status !== 'active') {
        set_flash('error', 'You cannot deactivate your own account.');
        header('Location: ../admin/users.php');
        exit;
    }

    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$status, $user_id]);

        set_flash('success', 'User status updated successfully.');
        header('Location: ../admin/users.php');
        exit;

    } catch (Exception $e) {
        set_flash('error', 'Failed to update status.');
        header('Location: ../admin/users.php');
        exit;
    }
}

function handle_delete_user() {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security check failed.');
        header('Location: ../admin/users.php');
        exit;
    }

    $user_id = (int)($_POST['user_id'] ?? 0);

    if (!$user_id) {
        set_flash('error', 'Invalid user.');
        header('Location: ../admin/users.php');
        exit;
    }

    if ($user_id == $_SESSION['user_id']) {
        set_flash('error', 'You cannot delete your own account.');
        header('Location: ../admin/users.php');
        exit;
    }

    try {
        $pdo = getDBConnection();

        // Check if user has active bookings
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM bookings 
            WHERE user_id = ? AND status IN ('confirmed', 'pending')
        ");
        $stmt->execute([$user_id]);
        $active_bookings = $stmt->fetchColumn();

        if ($active_bookings > 0) {
            set_flash('error', 'Cannot delete user with active bookings. Cancel bookings first.');
            header('Location: ../admin/users.php');
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        set_flash('success', 'User deleted successfully.');
        header('Location: ../admin/users.php');
        exit;

    } catch (Exception $e) {
        set_flash('error', 'Failed to delete user.');
        header('Location: ../admin/users.php');
        exit;
    }
}
