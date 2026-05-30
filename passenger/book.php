<?php
/**
 * SmartBus Booking System
 * Passenger - Book a Trip
 * Phase 4
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_PASSENGER);

$currentUser = get_current_user();
$pageTitle = "Book Ticket";
$isDashboard = true;

$schedule_id = (int)($_GET['schedule_id'] ?? 0);
$schedule = null;
$error = null;

if (!$schedule_id) {
    header('Location: search.php');
    exit;
}

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        SELECT 
            s.*,
            r.origin_city, r.destination_city,
            b.bus_number, b.bus_type, b.total_seats,
            o.company_name
        FROM schedules s
        JOIN routes r ON s.route_id = r.id
        JOIN buses b ON s.bus_id = b.id
        JOIN operators o ON b.operator_id = o.id
        WHERE s.id = ? AND s.status = 'scheduled' AND s.available_seats > 0
        LIMIT 1
    ");
    $stmt->execute([$schedule_id]);
    $schedule = $stmt->fetch();

    if (!$schedule) {
        set_flash('error', 'This schedule is no longer available.');
        header('Location: search.php');
        exit;
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    set_flash('error', 'Unable to load schedule details.');
    header('Location: search.php');
    exit;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 800px;">

        <div style="margin-bottom: 1.5rem;">
            <a href="search.php" style="color: var(--text-light); text-decoration: none;">← Back to Search</a>
            <h1 style="margin: 0.5rem 0 0;">Complete Your Booking</h1>
        </div>

        <!-- Trip Summary -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <strong>Trip Details</strong>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <div style="font-size: 0.85rem; color: var(--text-light);">FROM</div>
                        <div style="font-size: 1.3rem; font-weight: 600;"><?= htmlspecialchars($schedule['origin_city']) ?></div>
                        <div style="margin-top: 0.25rem;"><?= date('D, M d, Y • h:i A', strtotime($schedule['departure_time'])) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: var(--text-light);">TO</div>
                        <div style="font-size: 1.3rem; font-weight: 600;"><?= htmlspecialchars($schedule['destination_city']) ?></div>
                        <div style="margin-top: 0.25rem;"><?= date('D, M d, Y • h:i A', strtotime($schedule['arrival_time'])) ?></div>
                    </div>
                </div>

                <div style="margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--border-light);">
                    <strong><?= htmlspecialchars($schedule['company_name']) ?></strong> • 
                    Bus <?= htmlspecialchars($schedule['bus_number']) ?> (<?= htmlspecialchars($schedule['bus_type']) ?>)
                    <div style="margin-top: 0.5rem;">
                        <span class="badge badge-success"><?= $schedule['available_seats'] ?> seats available</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="card">
            <div class="card-header">
                <strong>Passenger Details</strong>
            </div>
            <div class="card-body">
                <form action="../actions/booking_action.php" method="POST">
                    <input type="hidden" name="action" value="create_booking">
                    <input type="hidden" name="schedule_id" value="<?= $schedule['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                    <div class="form-group">
                        <label class="form-label">Number of Seats</label>
                        <select name="num_seats" id="num_seats" class="form-control" style="max-width: 180px;" required>
                            <?php for ($i = 1; $i <= min(6, $schedule['available_seats']); $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> seat<?= $i > 1 ? 's' : '' ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div id="passenger_names">
                        <div class="form-group">
                            <label class="form-label">Passenger Name (Seat 1)</label>
                            <input type="text" name="passengers[]" class="form-control" required value="<?= htmlspecialchars($currentUser['full_name']) ?>">
                        </div>
                    </div>

                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-light);">
                        <div style="display: flex; justify-content: space-between; font-size: 1.1rem; margin-bottom: 1rem;">
                            <span>Total Amount:</span>
                            <strong id="total_amount">$<?= number_format($schedule['price_per_seat'], 2) ?></strong>
                        </div>

                        <button type="submit" class="btn btn-success btn-block btn-lg">
                            <i class="fas fa-check-circle"></i> Confirm &amp; Pay
                        </button>
                        <p style="font-size: 0.8rem; text-align: center; margin-top: 0.75rem; color: var(--text-light);">
                            Payment will be processed after confirmation (demo mode)
                        </p>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    // Dynamic total calculation + passenger name fields
    const numSeatsSelect = document.getElementById('num_seats');
    const passengerContainer = document.getElementById('passenger_names');
    const totalDisplay = document.getElementById('total_amount');
    const pricePerSeat = <?= $schedule['price_per_seat'] ?>;

    function updateForm() {
        const numSeats = parseInt(numSeatsSelect.value);
        const total = (numSeats * pricePerSeat).toFixed(2);
        totalDisplay.textContent = '$' + total;

        // Update passenger name fields
        passengerContainer.innerHTML = '';
        for (let i = 1; i <= numSeats; i++) {
            const div = document.createElement('div');
            div.className = 'form-group';
            div.innerHTML = `
                <label class="form-label">Passenger Name (Seat ${i})</label>
                <input type="text" name="passengers[]" class="form-control" required 
                       value="${i === 1 ? '<?= addslashes(htmlspecialchars($currentUser['full_name'])) ?>' : ''}">
            `;
            passengerContainer.appendChild(div);
        }
    }

    numSeatsSelect.addEventListener('change', updateForm);
    // Initialize
    updateForm();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
