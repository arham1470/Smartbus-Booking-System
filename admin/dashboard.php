<?php
/**
 * SmartBus Booking System
 * Administrator Dashboard
 * Phase 3 - Authentication Foundation
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();
require_role(ROLE_ADMIN);

$currentUser = get_current_user();
$pageTitle = "Admin Dashboard";
$isDashboard = true;

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container" style="max-width: 1100px;">
        
        <div style="margin-bottom: 2rem;">
            <h1 style="margin-bottom: 0.25rem;">Administrator Dashboard</h1>
            <p class="text-muted">System overview and management tools.</p>
        </div>

        <div class="dashboard-grid">
            <div class="stat-card">
                <h3><i class="fas fa-users"></i> Total Users</h3>
                <div class="value">1,284</div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-bus"></i> Active Buses</h3>
                <div class="value">87</div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-ticket-alt"></i> Bookings Today</h3>
                <div class="value">156</div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-dollar-sign"></i> Revenue Today</h3>
                <div class="value">$18,420</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <strong>System Management</strong>
            </div>
            <div class="card-body" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="#" class="btn btn-primary"><i class="fas fa-users"></i> Manage Users</a>
                <a href="#" class="btn btn-secondary"><i class="fas fa-building"></i> Manage Operators</a>
                <a href="#" class="btn btn-outline"><i class="fas fa-chart-bar"></i> View Reports</a>
                <a href="#" class="btn btn-outline"><i class="fas fa-cog"></i> System Settings</a>
            </div>
        </div>

        <div style="text-align: center; margin-top: 3rem; color: var(--text-light); font-size: 0.9rem;">
            <p>This is a <strong>Phase 3 placeholder dashboard</strong>. Full admin tools and reports will be built in Phase 6.</p>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
