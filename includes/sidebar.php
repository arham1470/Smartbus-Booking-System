<?php
/**
 * SmartBus Booking System
 * Dashboard Sidebar Component
 * 
 * Phase 1: Prepared and styled for future dashboard use (Phase 4, 5, 6)
 * 
 * Usage (from Phase 4 onward):
 *   $isDashboard = true;
 *   include 'includes/header.php';
 *   include 'includes/sidebar.php';
 */
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <strong style="font-size: 1.1rem; color: var(--primary-dark);">Dashboard</strong>
        <div style="font-size: 0.8rem; color: var(--text-light);">SmartBus Management</div>
    </div>
    
    <ul class="sidebar-nav">
        <?php 
        $currentRole = get_current_role();
        $currentFile = basename($_SERVER['PHP_SELF']);
        
        if ($currentRole === ROLE_ADMIN): ?>
            <li>
                <a href="dashboard.php" class="<?= $currentFile === 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt" style="width: 20px;"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="users.php" class="<?= $currentFile === 'users.php' ? 'active' : '' ?>">
                    <i class="fas fa-users" style="width: 20px;"></i>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="operators.php" class="<?= $currentFile === 'operators.php' ? 'active' : '' ?>">
                    <i class="fas fa-building" style="width: 20px;"></i>
                    <span>Operators</span>
                </a>
            </li>
            <li>
                <a href="bookings.php" class="<?= $currentFile === 'bookings.php' ? 'active' : '' ?>">
                    <i class="fas fa-ticket-alt" style="width: 20px;"></i>
                    <span>Bookings</span>
                </a>
            </li>
            <li>
                <a href="reports.php" class="<?= $currentFile === 'reports.php' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar" style="width: 20px;"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li style="margin-top: 1rem; border-top: 1px solid var(--border-light); padding-top: 0.5rem;">
                <a href="../passenger/profile.php">
                    <i class="fas fa-user-circle" style="width: 20px;"></i>
                    <span>My Profile</span>
                </a>
            </li>
        <?php elseif ($currentRole === ROLE_OPERATOR): ?>
            <li>
                <a href="dashboard.php" class="<?= $currentFile === 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt" style="width: 20px;"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="buses.php" class="<?= $currentFile === 'buses.php' ? 'active' : '' ?>">
                    <i class="fas fa-bus" style="width: 20px;"></i>
                    <span>My Buses</span>
                </a>
            </li>
            <li>
                <a href="schedules.php" class="<?= $currentFile === 'schedules.php' ? 'active' : '' ?>">
                    <i class="fas fa-calendar-alt" style="width: 20px;"></i>
                    <span>Schedules</span>
                </a>
            </li>
            <li>
                <a href="reservations.php" class="<?= $currentFile === 'reservations.php' ? 'active' : '' ?>">
                    <i class="fas fa-ticket-alt" style="width: 20px;"></i>
                    <span>Reservations</span>
                </a>
            </li>
            <li>
                <a href="routes.php" class="<?= $currentFile === 'routes.php' ? 'active' : '' ?>">
                    <i class="fas fa-route" style="width: 20px;"></i>
                    <span>Routes</span>
                </a>
            </li>
            <li style="margin-top: 1rem; border-top: 1px solid var(--border-light); padding-top: 0.5rem;">
                <a href="../passenger/profile.php">
                    <i class="fas fa-user-circle" style="width: 20px;"></i>
                    <span>My Profile</span>
                </a>
            </li>
        <?php else: ?>
            <!-- Passenger -->
            <li><a href="dashboard.php" class="<?= $currentFile === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
            <li><a href="search.php" class="<?= $currentFile === 'search.php' ? 'active' : '' ?>">Search Buses</a></li>
            <li><a href="bookings.php" class="<?= $currentFile === 'bookings.php' ? 'active' : '' ?>">My Bookings</a></li>
            <li><a href="profile.php" class="<?= $currentFile === 'profile.php' ? 'active' : '' ?>">My Profile</a></li>
        <?php endif; ?>

        <li>
            <a href="../logout.php">
                <i class="fas fa-sign-out-alt" style="width: 20px;"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
    
    <div style="position: absolute; bottom: 1rem; left: 1.5rem; font-size: 0.75rem; color: var(--text-muted);">
        v0.1 • Phase 1
    </div>
</aside>
