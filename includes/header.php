<?php
/**
 * SmartBus Booking System
 * Reusable Header Component
 * Phase 3 - Fully integrated with authentication
 */

require_once __DIR__ . '/auth.php';
start_secure_session();

$pageTitle = $pageTitle ?? 'SmartBus Booking System';
$isDashboard = $isDashboard ?? false;

// Automatically get logged-in user if not explicitly passed
if (!isset($currentUser)) {
    $currentUser = get_logged_in_user();
}
$role = $currentUser['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | SmartBus</title>
    
    <!-- Favicon placeholder -->
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    
    <!-- Font Awesome for icons (CDN - reliable for development) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

<?php if (!$isDashboard): ?>
<!-- PUBLIC NAVIGATION BAR -->
<nav class="navbar">
    <div class="container">
        <!-- Brand -->
        <a href="index.php" class="navbar-brand">
            <i class="fas fa-bus"></i>
            <span>SmartBus</span>
        </a>
        
        <!-- Desktop Navigation -->
        <ul class="navbar-nav">
            <li><a href="index.php">Home</a></li>
            <li><a href="login.php#search">Search Buses</a></li>
            
            <?php if ($currentUser): ?>
                <!-- Logged in state -->
                <li><a href="<?= get_role_dashboard($role) ?>">Dashboard</a></li>
                <?php if (in_array($role, ['passenger', 'operator'])): ?>
                    <?php 
                        $notifUrl = ($role === 'operator') 
                            ? 'operator/notifications.php' 
                            : 'passenger/notifications.php'; 
                    ?>
                    <li>
                        <a href="<?= $notifUrl ?>" title="Notifications" style="position:relative;">
                            <i class="fas fa-bell"></i>
                        </a>
                    </li>
                <?php endif; ?>
                <li><a href="logout.php" class="btn btn-outline">Logout</a></li>
            <?php else: ?>
                <!-- Public state -->
                <li><a href="login.php" class="btn btn-outline">Login</a></li>
                <li><a href="register.php" class="btn btn-primary">Register</a></li>
            <?php endif; ?>
        </ul>
        
        <!-- Mobile Hamburger -->
        <button class="navbar-toggle" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</nav>
<?php else: ?>
<!-- DASHBOARD TOP BAR - Modern UI -->
<header class="dashboard-header">
    <div class="dashboard-header-inner">
        <div class="dashboard-header-left">
            <!-- Mobile Sidebar Toggle -->
            <button class="sidebar-toggle" aria-label="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>

            <a href="<?= BASE_URL ?>/" class="dashboard-logo">
                <i class="fas fa-bus"></i>
                <span>SmartBus</span>
            </a>
        </div>

        <div class="dashboard-header-right">
            <?php if ($currentUser): ?>
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <span class="user-name"><?= htmlspecialchars($currentUser['name'] ?? $currentUser['full_name'] ?? 'User') ?></span>
                        <span class="user-role"><?= ucfirst($role ?? 'User') ?></span>
                    </div>
                </div>

                <a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm btn-danger dashboard-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>
<?php endif; ?>
