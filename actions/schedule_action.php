<?php
/**
 * SmartBus Booking System
 * Schedule Management Actions (Operator)
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
    header('Location: ../operator/schedules.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'add_schedule') {
    handle_add_schedule();
} elseif ($action === 'update_schedule') {
    handle_update_schedule();
} elseif ($action === 'cancel_schedule') {
    handle_cancel_schedule();
} else {
    header('Location: ../operator/schedules.php');
    exit;
}

function handle_add_schedule() {
    global $operator;

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security check failed.');
        header('Location: ../operator/schedules.php');
        exit;
    }

    $bus_id = (int)($_POST['bus_id'] ?? 0);
    $route_id = (int)($_POST['route_id'] ?? 0);
    $departure_time = $_POST['departure_time'] ?? '';
    $arrival_time = $_POST['arrival_time'] ?? '';
    $price_per_seat = (float)($_POST['price_per_seat'] ?? 0);

    if (!$bus_id || !$route_id || !$departure_time || !$arrival_time || $price_per_seat <= 0) {
        set_flash('error', 'All fields are required.');
        header('Location: ../operator/schedules.php');
        exit;
    }

    try {
        $pdo = getDBConnection();

        // Verify bus belongs to this operator
        $stmt = $pdo->prepare("SELECT total_seats FROM buses WHERE id = ? AND operator_id = ?");
        $stmt->execute([$bus_id, $operator['id']]);
        $bus = $stmt->fetch();

        if (!$bus) {
            throw new Exception("Invalid bus selected.");
        }

        // Verify route exists
        $stmt = $pdo->prepare("SELECT id FROM routes WHERE id = ?");
        $stmt->execute([$route_id]);
        if (!$stmt->fetch()) {
            throw new Exception("Invalid route selected.");
        }

        // Insert schedule
        $stmt = $pdo->prepare("
            INSERT INTO schedules (bus_id, route_id, departure_time, arrival_time, price_per_seat, available_seats, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'scheduled', NOW())
        ");
        $stmt->execute([
            $bus_id,
            $route_id,
            $departure_time,
            $arrival_time,
            $price_per_seat,
            $bus['total_seats']   // Start with full capacity
        ]);

        set_flash('success', 'Schedule created successfully.');
        header('Location: ../operator/schedules.php');
        exit;

    } catch (Exception $e) {
        set_flash('error', $e->getMessage());
        header('Location: ../operator/schedules.php');
        exit;
    }
}

function handle_update_schedule() {
    global $operator;

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security check failed.');
        header('Location: ../operator/schedules.php');
        exit;
    }

    $schedule_id = (int)($_POST['schedule_id'] ?? 0);
    $departure_time = $_POST['departure_time'] ?? '';
    $arrival_time = $_POST['arrival_time'] ?? '';
    $price_per_seat = (float)($_POST['price_per_seat'] ?? 0);
    $status = $_POST['status'] ?? 'scheduled';

    if (!$schedule_id || !$departure_time || !$arrival_time) {
        set_flash('error', 'Invalid schedule data.');
        header('Location: ../operator/schedules.php');
        exit;
    }

    try {
        $pdo = getDBConnection();

        // Verify ownership through bus
        $stmt = $pdo->prepare("
            SELECT s.id 
            FROM schedules s
            JOIN buses b ON s.bus_id = b.id
            WHERE s.id = ? AND b.operator_id = ?
        ");
        $stmt->execute([$schedule_id, $operator['id']]);
        if (!$stmt->fetch()) {
            throw new Exception("Schedule not found or access denied.");
        }

        $stmt = $pdo->prepare("
            UPDATE schedules 
            SET departure_time = ?, arrival_time = ?, price_per_seat = ?, status = ?
            WHERE id = ?
        ");
        $stmt->execute([$departure_time, $arrival_time, $price_per_seat, $status, $schedule_id]);

        set_flash('success', 'Schedule updated successfully.');
        header('Location: ../operator/schedules.php');
        exit;

    } catch (Exception $e) {
        set_flash('error', $e->getMessage());
        header('Location: ../operator/schedules.php');
        exit;
    }
}

function handle_cancel_schedule() {
    global $operator;

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security check failed.');
        header('Location: ../operator/schedules.php');
        exit;
    }

    $schedule_id = (int)($_POST['schedule_id'] ?? 0);

    try {
        $pdo = getDBConnection();
        $pdo->beginTransaction();

        // Verify ownership
        $stmt = $pdo->prepare("
            SELECT s.id, s.available_seats, s.status
            FROM schedules s
            JOIN buses b ON s.bus_id = b.id
            WHERE s.id = ? AND b.operator_id = ?
            FOR UPDATE
        ");
        $stmt->execute([$schedule_id, $operator['id']]);
        $schedule = $stmt->fetch();

        if (!$schedule) {
            throw new Exception("Schedule not found.");
        }

        if ($schedule['status'] === 'cancelled') {
            throw new Exception("Schedule is already cancelled.");
        }

        // Cancel the schedule
        $stmt = $pdo->prepare("UPDATE schedules SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$schedule_id]);

        // Also cancel any confirmed bookings for this schedule
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET status = 'cancelled', cancelled_at = NOW(), payment_status = 'refunded'
            WHERE schedule_id = ? AND status = 'confirmed'
        ");
        $stmt->execute([$schedule_id]);

        // Create notifications for affected passengers
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, type)
            SELECT user_id, 'Trip Cancelled', 
                   CONCAT('Your booking for schedule #', ?, ' has been cancelled by the operator.'), 'booking'
            FROM bookings 
            WHERE schedule_id = ? AND status = 'cancelled'
        ");
        $stmt->execute([$schedule_id, $schedule_id]);

        $pdo->commit();

        set_flash('success', 'Schedule cancelled successfully. Affected bookings have been cancelled.');
        header('Location: ../operator/schedules.php');
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        set_flash('error', $e->getMessage());
        header('Location: ../operator/schedules.php');
        exit;
    }
}
