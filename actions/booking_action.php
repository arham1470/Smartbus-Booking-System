<?php
/**
 * SmartBus Booking System
 * Booking Actions (Create + Cancel)
 * Phase 4 - Passenger Module
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_PASSENGER);

$currentUser = get_current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../passenger/search.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'create_booking') {
    handle_create_booking();
} elseif ($action === 'cancel_booking') {
    handle_cancel_booking();
} else {
    header('Location: ../passenger/dashboard.php');
    exit;
}

// ============================================
// CREATE BOOKING
// ============================================
function handle_create_booking() {
    global $currentUser;

    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_flash('error', 'Security check failed. Please try again.');
        header('Location: ../passenger/search.php');
        exit;
    }

    $schedule_id = (int)($_POST['schedule_id'] ?? 0);
    $selected_seats = isset($_POST['selected_seats']) ? explode(',', $_POST['selected_seats']) : [];
    $num_seats = count($selected_seats);
    $passengers = $_POST['passengers'] ?? [];

    if (!$schedule_id || $num_seats < 1 || empty($passengers)) {
        set_flash('error', 'Invalid booking request.');
        header('Location: ../passenger/search.php');
        exit;
    }

    try {
        $pdo = getDBConnection();
        $pdo->beginTransaction();

        // Lock the schedule row
        $stmt = $pdo->prepare("
            SELECT s.*, r.origin_city, r.destination_city 
            FROM schedules s 
            JOIN routes r ON s.route_id = r.id 
            WHERE s.id = ? AND s.status = 'scheduled' 
            FOR UPDATE
        ");
        $stmt->execute([$schedule_id]);
        $schedule = $stmt->fetch();

        if (!$schedule) {
            throw new Exception("Schedule no longer available.");
        }

        if ($schedule['available_seats'] < $num_seats) {
            throw new Exception("Not enough seats available.");
        }

        // Generate booking reference
        $booking_ref = 'SB-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $total_amount = $num_seats * $schedule['price_per_seat'];

        // Create booking
        $stmt = $pdo->prepare("
            INSERT INTO bookings 
            (user_id, schedule_id, booking_reference, number_of_seats, total_amount, status, payment_status, booked_at)
            VALUES (?, ?, ?, ?, ?, 'confirmed', 'paid', NOW())
        ");
        $stmt->execute([
            $currentUser['id'],
            $schedule_id,
            $booking_ref,
            $num_seats,
            $total_amount
        ]);
        $booking_id = $pdo->lastInsertId();

        // Insert seat assignments using selected seats
        $stmtSeat = $pdo->prepare("
            INSERT INTO booking_seats (booking_id, seat_number, passenger_name) 
            VALUES (?, ?, ?)
        ");

        foreach ($selected_seats as $index => $seat_number) {
            $name = $passengers[$index] ?? 'Passenger';
            $stmtSeat->execute([$booking_id, trim($seat_number), trim($name) ?: 'Passenger']);
        }

        // Update available seats
        $stmt = $pdo->prepare("
            UPDATE schedules 
            SET available_seats = available_seats - ? 
            WHERE id = ?
        ");
        $stmt->execute([$num_seats, $schedule_id]);

        // Create payment record
        $stmt = $pdo->prepare("
            INSERT INTO payments (booking_id, amount, payment_method, status, paid_at)
            VALUES (?, ?, 'card', 'completed', NOW())
        ");
        $stmt->execute([$booking_id, $total_amount]);

        // Create notification
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, type)
            VALUES (?, ?, ?, 'booking')
        ");
        $stmt->execute([
            $currentUser['id'],
            'Booking Confirmed',
            "Your booking $booking_ref for {$schedule['origin_city']} → {$schedule['destination_city']} has been confirmed."
        ]);

        $pdo->commit();

        // Redirect to nice confirmation page (Phase 7)
        header("Location: ../passenger/booking_confirmation.php?ref=" . urlencode($booking_ref));
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("Booking creation failed: " . $e->getMessage());
        set_flash('error', $e->getMessage());
        header("Location: ../passenger/book.php?schedule_id=$schedule_id");
        exit;
    }
}

// ============================================
// CANCEL BOOKING
// ============================================
function handle_cancel_booking() {
    global $currentUser;

    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_flash('error', 'Security check failed.');
        header('Location: ../passenger/bookings.php');
        exit;
    }

    $booking_id = (int)($_POST['booking_id'] ?? 0);

    if (!$booking_id) {
        set_flash('error', 'Invalid booking.');
        header('Location: ../passenger/bookings.php');
        exit;
    }

    try {
        $pdo = getDBConnection();
        $pdo->beginTransaction();

        // Get booking and verify ownership + status
        $stmt = $pdo->prepare("
            SELECT b.*, s.departure_time 
            FROM bookings b
            JOIN schedules s ON b.schedule_id = s.id
            WHERE b.id = ? AND b.user_id = ?
            FOR UPDATE
        ");
        $stmt->execute([$booking_id, $currentUser['id']]);
        $booking = $stmt->fetch();

        if (!$booking) {
            throw new Exception("Booking not found.");
        }

        if ($booking['status'] === 'cancelled') {
            throw new Exception("This booking is already cancelled.");
        }

        // Only allow cancellation if departure is in the future
        if (strtotime($booking['departure_time']) < time()) {
            throw new Exception("Cannot cancel a trip that has already departed.");
        }

        // Update booking status
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET status = 'cancelled', cancelled_at = NOW(), payment_status = 'refunded' 
            WHERE id = ?
        ");
        $stmt->execute([$booking_id]);

        // Release seats back to schedule
        $stmt = $pdo->prepare("
            UPDATE schedules 
            SET available_seats = available_seats + ? 
            WHERE id = ?
        ");
        $stmt->execute([$booking['number_of_seats'], $booking['schedule_id']]);

        // Create notification
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, type)
            VALUES (?, 'Booking Cancelled', ?, 'booking')
        ");
        $stmt->execute([
            $currentUser['id'],
            "Booking {$booking['booking_reference']} has been cancelled. Refund processed."
        ]);

        $pdo->commit();

        set_flash('success', "Booking {$booking['booking_reference']} has been cancelled successfully.");
        header('Location: ../passenger/bookings.php');
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        set_flash('error', $e->getMessage());
        header('Location: ../passenger/bookings.php');
        exit;
    }
}
