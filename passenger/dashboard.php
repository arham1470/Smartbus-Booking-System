<?php
/**
 * SmartBus Booking System
 * Passenger Dashboard
 * Phase 3 - Authentication Foundation
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_PASSENGER);

$currentUser = get_current_user();
$pageTitle = "Passenger Dashboard";
$isDashboard = true;

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 1100px;">
        
        <div style="margin-bottom: 2rem;">
            <h1 style="margin-bottom: 0.25rem;">Welcome back, <?= htmlspecialchars($currentUser['name']) ?>!</h1>
            <p class="text-muted">Here's what's happening with your bookings today.</p>
        </div>

        <!-- Stats Cards -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <h3><i class="fas fa-ticket-alt"></i> Active Bookings</h3>
                <div class="value">2</div>
                <small class="text-muted">Next trip in 5 days</small>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-history"></i> Total Trips</h3>
                <div class="value">14</div>
                <small class="text-muted">This year</small>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-wallet"></i> Wallet Balance</h3>
                <div class="value">$0</div>
                <small class="text-muted">No pending refunds</small>
            </div>
        </div>

        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <strong>Upcoming Trips</strong>
            </div>
            <div class="card-body">
                <p class="text-muted" style="margin-bottom: 1rem;">You have <strong>2 upcoming bookings</strong>.</p>
                
                <div style="display: grid; gap: 1rem;">
                    <div style="border: 1px solid var(--border); border-radius: 8px; padding: 1rem;">
                        <strong>Chicago → New York</strong><br>
                        <small>Feb 20, 2026 • 08:00 AM • Seats: A12, A13</small><br>
                        <span class="badge badge-success" style="margin-top: 0.5rem;">Confirmed</span>
                    </div>
                    <div style="border: 1px solid var(--border); border-radius: 8px; padding: 1rem;">
                        <strong>New York → Boston</strong><br>
                        <small>Mar 5, 2026 • 07:45 AM • Seat: B07</small><br>
                        <span class="badge badge-success" style="margin-top: 0.5rem;">Confirmed</span>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="#" class="btn btn-primary btn-sm">View All Bookings</a>
                <a href="#" class="btn btn-outline btn-sm">Search New Trips</a>
            </div>
        </div>

        <div style="text-align: center; margin-top: 3rem; color: var(--text-light); font-size: 0.9rem;">
            <p>This is a <strong>Phase 3 placeholder dashboard</strong>. Full booking management will be built in Phase 4.</p>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
