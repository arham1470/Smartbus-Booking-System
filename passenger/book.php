<?php
/**
 * SmartBus Booking System
 * Passenger - Book a Trip (Advanced Visual Seat Selection - Phase 7)
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_PASSENGER);

$currentUser = get_logged_in_user();
$pageTitle = "Book Ticket";
$isDashboard = true;

$schedule_id = (int)($_GET['schedule_id'] ?? 0);

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
            b.id as bus_id, b.bus_number, b.bus_type, b.total_seats,
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

    // Get already booked seats
    $stmt = $pdo->prepare("
        SELECT seat_number 
        FROM booking_seats bs
        JOIN bookings b ON bs.booking_id = b.id
        WHERE b.schedule_id = ? AND b.status != 'cancelled'
    ");
    $stmt->execute([$schedule_id]);
    $booked_seats = $stmt->fetchAll(PDO::FETCH_COLUMN);

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
    <div class="container" style="max-width: 920px;">

        <div style="margin-bottom: 1.5rem;">
            <a href="search.php" style="color: var(--text-light);">← Back to Search</a>
            <h1 style="margin: 0.5rem 0 0;">Select Your Seats</h1>
        </div>

        <!-- Trip Summary -->
        <div class="card" style="margin-bottom: 1.25rem;">
            <div class="card-body">
                <strong><?= htmlspecialchars($schedule['origin_city']) ?> → <?= htmlspecialchars($schedule['destination_city']) ?></strong><br>
                <small><?= date('D, M d, Y • h:i A', strtotime($schedule['departure_time'])) ?> | <?= htmlspecialchars($schedule['company_name']) ?> • Bus <?= htmlspecialchars($schedule['bus_number']) ?></small>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 340px; gap: 1.5rem;">

            <!-- Visual Seat Map -->
            <div class="card">
                <div class="card-header"><strong>Seat Map — Click to Select</strong></div>
                <div class="card-body">
                    <div style="text-align:center; font-size:0.8rem; margin-bottom:8px; color:#666;">← Front of the Bus</div>
                    
                    <div id="seat-map" style="display:grid; grid-template-columns: repeat(4, 1fr); gap:7px; max-width:300px; margin:0 auto 12px;">
                        <?php
                        $total = (int)$schedule['total_seats'];
                        $seat_counter = 1;
                        for ($i = 1; $i <= $total; $i++):
                            $seat_num = 'S' . str_pad($i, 2, '0', STR_PAD_LEFT);
                            $is_booked = in_array($seat_num, $booked_seats);
                        ?>
                            <div class="seat <?= $is_booked ? 'booked' : '' ?>" 
                                 data-seat="<?= $seat_num ?>"
                                 onclick="toggleSeat(this)">
                                <?= $seat_num ?>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div style="font-size:0.75rem; display:flex; gap:12px; justify-content:center;">
                        <span><span class="seat available" style="display:inline-block;width:16px;height:16px;vertical-align:middle;font-size:8px;">S</span> Available</span>
                        <span><span class="seat selected" style="display:inline-block;width:16px;height:16px;vertical-align:middle;font-size:8px;">S</span> Selected</span>
                        <span><span class="seat booked" style="display:inline-block;width:16px;height:16px;vertical-align:middle;font-size:8px;">S</span> Booked</span>
                    </div>
                </div>
            </div>

            <!-- Booking Panel -->
            <div class="card">
                <div class="card-header"><strong>Your Selection</strong></div>
                <div class="card-body">
                    <form action="../actions/booking_action.php" method="POST" id="booking-form">
                        <input type="hidden" name="action" value="create_booking">
                        <input type="hidden" name="schedule_id" value="<?= $schedule['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                        <div style="margin-bottom:12px;">
                            <strong>Selected Seats:</strong>
                            <div id="selected-list" style="min-height:42px; background:#f8f9fa; padding:8px; border-radius:6px; margin-top:4px; font-size:0.9rem;">
                                Click seats on the left
                            </div>
                            <input type="hidden" name="selected_seats" id="selected-seats-hidden">
                        </div>

                        <div id="passenger-fields"></div>

                        <div style="margin-top:16px; padding-top:12px; border-top:1px solid #eee;">
                            <div style="display:flex;justify-content:space-between;font-size:1.15rem;margin-bottom:12px;">
                                <span>Total</span>
                                <strong id="total-price">$0.00</strong>
                            </div>
                            <button type="submit" class="btn btn-success btn-block btn-lg" id="submit-btn" disabled>
                                Confirm &amp; Pay
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.seat { width:48px; height:34px; background:#bae6fd; border:2px solid #0369a1; border-radius:5px; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; cursor:pointer; user-select:none; }
.seat:hover { background:#7dd3fc; }
.seat.selected { background:#16a34a; color:white; border-color:#15803d; }
.seat.booked { background:#e5e7eb; border-color:#9ca3af; color:#6b7280; cursor:not-allowed; }
</style>

<script>
const price = <?= $schedule['price_per_seat'] ?>;
let selected = [];

function toggleSeat(el) {
    const seat = el.dataset.seat;
    if (el.classList.contains('booked')) return;

    if (selected.includes(seat)) {
        selected = selected.filter(s => s !== seat);
        el.classList.remove('selected');
    } else {
        if (selected.length >= 6) {
            alert("Maximum 6 seats per booking.");
            return;
        }
        selected.push(seat);
        el.classList.add('selected');
    }
    renderForm();
}

function renderForm() {
    const list = document.getElementById('selected-list');
    const hidden = document.getElementById('selected-seats-hidden');
    const container = document.getElementById('passenger-fields');
    const totalEl = document.getElementById('total-price');
    const btn = document.getElementById('submit-btn');

    hidden.value = selected.join(',');

    if (selected.length === 0) {
        list.innerHTML = '<span style="color:#888">Click seats on the left</span>';
        container.innerHTML = '';
        totalEl.textContent = '$0.00';
        btn.disabled = true;
        return;
    }

    list.innerHTML = selected.map(s => `<span class="badge badge-success" style="margin:2px">${s}</span>`).join('');

    container.innerHTML = '';
    selected.forEach((seat, i) => {
        const div = document.createElement('div');
        div.className = 'form-group';
        div.style.marginBottom = '6px';
        div.innerHTML = `
            <label style="font-size:0.8rem">Passenger for ${seat}</label>
            <input type="text" name="passengers[]" class="form-control" required value="${i===0 ? '<?= addslashes(htmlspecialchars($currentUser['full_name'])) ?>' : ''}">
        `;
        container.appendChild(div);
    });

    totalEl.textContent = '$' + (selected.length * price).toFixed(2);
    btn.disabled = false;
}

document.getElementById('booking-form').addEventListener('submit', function(e) {
    if (selected.length === 0) {
        e.preventDefault();
        alert("Please select at least one seat.");
    }
});
</script>

<script src="<?= BASE_URL ?>/assets/js/script.js"></script>
</body>
</html>
