<?php
/**
 * SmartBus Booking System
 * Operator Dashboard
 * Phase 3 - Authentication Foundation
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_OPERATOR);

$currentUser = get_current_user();
$pageTitle = "Operator Dashboard";
$isDashboard = true;

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 1100px;">
        
        <div style="margin-bottom: 2rem;">
            <h1 style="margin-bottom: 0.25rem;">Operator Dashboard</h1>
            <p class="text-muted">Welcome, <?= htmlspecialchars($currentUser['name']) ?>. Manage your fleet and schedules.</p>
        </div>

        <div class="dashboard-grid">
            <div class="stat-card">
                <h3><i class="fas fa-bus"></i> Active Buses</h3>
                <div class="value">4</div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-calendar-alt"></i> Upcoming Trips</h3>
                <div class="value">12</div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-ticket-alt"></i> Bookings Today</h3>
                <div class="value">28</div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-dollar-sign"></i> Revenue (MTD)</h3>
                <div class="value">$4,820</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <strong>Quick Actions</strong>
            </div>
            <div class="card-body" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="#" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Bus</a>
                <a href="#" class="btn btn-secondary"><i class="fas fa-route"></i> Create Schedule</a>
                <a href="#" class="btn btn-outline"><i class="fas fa-list"></i> View All Reservations</a>
            </div>
        </div>

        <div style="text-align: center; margin-top: 3rem; color: var(--text-light); font-size: 0.9rem;">
            <p>This is a <strong>Phase 3 placeholder dashboard</strong>. Full operator management tools will be built in Phase 5.</p>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
