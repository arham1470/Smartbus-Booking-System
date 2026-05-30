<?php
/**
 * SmartBus Booking System
 * Admin - Booking Management Actions
 * Phase 6 - Admin Module
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_ADMIN);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/bookings.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'update_booking_status') {
    handle_update_booking_status();
} elseif ($action === 'cancel_booking') {
    handle_admin_cancel_booking();
} else {
    header('Location: ' . BASE_URL . '/admin/bookings.php');
    exit;
}

function handle_update_booking_status() {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security check failed.');
        header('Location: ' . BASE_URL . '/admin/bookings.php');
        exit;
    }

    $booking_id = (int)($_POST['booking_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $payment_status = $_POST['payment_status'] ?? '';

    if (!$booking_id || !in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'])) {
        set_flash('error', 'Invalid booking data.');
        header('Location: ' . BASE_URL . '/admin/bookings.php');
        exit;
    }

    try {
        $pdo = getDBConnection();

        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET status = ?, payment_status = ?
            WHERE id = ?
        ");
        $stmt->execute([$status, $payment_status, $booking_id]);

        set_flash('success', 'Booking status updated successfully.');
        header('Location: ' . BASE_URL . '/admin/bookings.php');
        exit;

    } catch (Exception $e) {
        set_flash('error', 'Failed to update booking.');
        header('Location: ' . BASE_URL . '/admin/bookings.php');
        exit;
    }
}

function handle_admin_cancel_booking() {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security check failed.');
        header('Location: ' . BASE_URL . '/admin/bookings.php');
        exit;
    }

    $booking_id = (int)($_POST['booking_id'] ?? 0);

    if (!$booking_id) {
        set_flash('error', 'Invalid booking.');
        header('Location: ' . BASE_URL . '/admin/bookings.php');
        exit;
    }

    try {
        $pdo = getDBConnection();
        $pdo->beginTransaction();

        // Get booking details
        $stmt = $pdo->prepare("
            SELECT b.*, s.id as schedule_id, s.departure_time 
            FROM bookings b
            JOIN schedules s ON b.schedule_id = s.id
            WHERE b.id = ?
            FOR UPDATE
        ");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch();

        if (!$booking) {
            throw new Exception("Booking not found.");
        }

        if ($booking['status'] === 'cancelled') {
            throw new Exception("Booking is already cancelled.");
        }

        // Update booking
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET status = 'cancelled', cancelled_at = NOW(), payment_status = 'refunded'
            WHERE id = ?
        ");
        $stmt->execute([$booking_id]);

        // Release seats if the trip hasn't departed yet
        if (strtotime($booking['departure_time']) > time()) {
            $stmt = $pdo->prepare("
                UPDATE schedules 
                SET available_seats = available_seats + ? 
                WHERE id = ?
            ");
            $stmt->execute([$booking['number_of_seats'], $booking['schedule_id']]);
        }

        // Create notification for the passenger
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, type)
            VALUES (?, 'Booking Cancelled by Admin', ?, 'booking')
        ");
        $stmt->execute([
            $booking['user_id'],
            "Your booking {$booking['booking_reference']} has been cancelled by an administrator."
        ]);

        $pdo->commit();

        set_flash('success', "Booking {$booking['booking_reference']} has been cancelled.");
        header('Location: ' . BASE_URL . '/admin/bookings.php');
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        set_flash('error', $e->getMessage());
        header('Location: ' . BASE_URL . '/admin/bookings.php');
        exit;
    }
}
