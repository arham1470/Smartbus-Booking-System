<?php
/**
 * SmartBus Booking System
 * Profile Update Actions
 * Phase 4
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_PASSENGER);

$currentUser = get_current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../passenger/profile.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'update_profile') {
    handle_update_profile();
} elseif ($action === 'change_password') {
    handle_change_password();
} else {
    header('Location: ../passenger/profile.php');
    exit;
}

function handle_update_profile() {
    global $currentUser;

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security check failed.');
        header('Location: ../passenger/profile.php');
        exit;
    }

    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($full_name)) {
        set_flash('error', 'Full name is required.');
        header('Location: ../passenger/profile.php');
        exit;
    }

    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$full_name, $phone, $currentUser['id']]);

        set_flash('success', 'Profile updated successfully.');
        header('Location: ../passenger/profile.php');
        exit;
    } catch (Exception $e) {
        set_flash('error', 'Failed to update profile.');
        header('Location: ../passenger/profile.php');
        exit;
    }
}

function handle_change_password() {
    global $currentUser;

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security check failed.');
        header('Location: ../passenger/profile.php');
        exit;
    }

    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($new) < 8) {
        set_flash('error', 'New password must be at least 8 characters.');
        header('Location: ../passenger/profile.php');
        exit;
    }
    if ($new !== $confirm) {
        set_flash('error', 'New passwords do not match.');
        header('Location: ../passenger/profile.php');
        exit;
    }

    try {
        $pdo = getDBConnection();

        // Verify current password
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$currentUser['id']]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($current, $hash)) {
            set_flash('error', 'Current password is incorrect.');
            header('Location: ../passenger/profile.php');
            exit;
        }

        // Update password
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$new_hash, $currentUser['id']]);

        set_flash('success', 'Password changed successfully.');
        header('Location: ../passenger/profile.php');
        exit;

    } catch (Exception $e) {
        set_flash('error', 'Failed to change password.');
        header('Location: ../passenger/profile.php');
        exit;
    }
}
