<?php
/**
 * SmartBus Booking System
 * Bus Management Actions (Operator)
 * Phase 5 - Operator Module
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_OPERATOR);

$operator = get_current_operator();
if (!$operator) {
    set_flash('error', 'Operator profile not found.');
    header('Location: ../operator/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../operator/buses.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'add_bus') {
    handle_add_bus();
} elseif ($action === 'update_bus') {
    handle_update_bus();
} elseif ($action === 'delete_bus') {
    handle_delete_bus();
} else {
    header('Location: ../operator/buses.php');
    exit;
}

function handle_add_bus() {
    global $operator;

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security check failed.');
        header('Location: ../operator/buses.php');
        exit;
    }

    $bus_number = trim($_POST['bus_number'] ?? '');
    $bus_type   = $_POST['bus_type'] ?? 'Standard';
    $total_seats = (int)($_POST['total_seats'] ?? 40);
    $seat_layout = trim($_POST['seat_layout'] ?? '');
    $amenities   = trim($_POST['amenities'] ?? '');

    if (empty($bus_number)) {
        set_flash('error', 'Bus number is required.');
        header('Location: ../operator/buses.php');
        exit;
    }

    try {
        $pdo = getDBConnection();

        $stmt = $pdo->prepare("
            INSERT INTO buses (operator_id, bus_number, bus_type, total_seats, seat_layout, amenities, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
        ");
        $stmt->execute([
            $operator['id'],
            $bus_number,
            $bus_type,
            $total_seats,
            $seat_layout ?: null,
            $amenities ?: null
        ]);

        set_flash('success', 'Bus added successfully.');
        header('Location: ../operator/buses.php');
        exit;

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            set_flash('error', 'A bus with this number already exists for your company.');
        } else {
            set_flash('error', 'Failed to add bus.');
        }
        header('Location: ../operator/buses.php');
        exit;
    }
}

function handle_update_bus() {
    global $operator;

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security check failed.');
        header('Location: ../operator/buses.php');
        exit;
    }

    $bus_id = (int)($_POST['bus_id'] ?? 0);
    $bus_number = trim($_POST['bus_number'] ?? '');
    $bus_type   = $_POST['bus_type'] ?? 'Standard';
    $total_seats = (int)($_POST['total_seats'] ?? 40);
    $status     = $_POST['status'] ?? 'active';

    if (!$bus_id || empty($bus_number)) {
        set_flash('error', 'Invalid bus data.');
        header('Location: ../operator/buses.php');
        exit;
    }

    try {
        $pdo = getDBConnection();

        // Verify ownership
        $stmt = $pdo->prepare("SELECT id FROM buses WHERE id = ? AND operator_id = ?");
        $stmt->execute([$bus_id, $operator['id']]);
        if (!$stmt->fetch()) {
            set_flash('error', 'Bus not found or access denied.');
            header('Location: ../operator/buses.php');
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE buses 
            SET bus_number = ?, bus_type = ?, total_seats = ?, status = ?
            WHERE id = ?
        ");
        $stmt->execute([$bus_number, $bus_type, $total_seats, $status, $bus_id]);

        set_flash('success', 'Bus updated successfully.');
        header('Location: ../operator/buses.php');
        exit;

    } catch (Exception $e) {
        set_flash('error', 'Failed to update bus.');
        header('Location: ../operator/buses.php');
        exit;
    }
}

function handle_delete_bus() {
    global $operator;

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security check failed.');
        header('Location: ../operator/buses.php');
        exit;
    }

    $bus_id = (int)($_POST['bus_id'] ?? 0);

    try {
        $pdo = getDBConnection();

        // Check if bus has any future schedules
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM schedules 
            WHERE bus_id = ? AND departure_time > NOW() AND status != 'cancelled'
        ");
        $stmt->execute([$bus_id]);
        $future_schedules = $stmt->fetchColumn();

        if ($future_schedules > 0) {
            set_flash('error', 'Cannot delete bus with upcoming schedules. Please cancel schedules first.');
            header('Location: ../operator/buses.php');
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM buses WHERE id = ? AND operator_id = ?");
        $stmt->execute([$bus_id, $operator['id']]);

        set_flash('success', 'Bus deleted successfully.');
        header('Location: ../operator/buses.php');
        exit;

    } catch (Exception $e) {
        set_flash('error', 'Failed to delete bus.');
        header('Location: ../operator/buses.php');
        exit;
    }
}
