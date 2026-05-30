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
<!-- DASHBOARD TOP BAR (light version for later phases) -->
<header class="navbar" style="background: #fff; border-bottom: 1px solid #E5E7EB;">
    <div class="container" style="max-width: 100%; padding: 0 1.5rem; height: 64px;">
        <div style="display: flex; align-items: center; justify-content: space-between; height: 100%;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <!-- Sidebar toggle button (mobile) -->
                <button class="sidebar-toggle btn btn-outline" style="padding: 6px 10px; display: none;" aria-label="Toggle sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                
                <a href="index.php" style="font-size: 1.25rem; font-weight: 700; color: var(--primary-dark); display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-bus"></i>
                    <span>SmartBus</span>
                </a>
            </div>
            
            <div style="display: flex; align-items: center; gap: 1rem;">
                <?php if ($currentUser): ?>
                    <span style="color: var(--text-light); font-size: 0.9rem;">
                        Welcome, <strong><?= htmlspecialchars($currentUser['name'] ?? $currentUser['full_name'] ?? 'User') ?></strong>
                    </span>
                    <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
<?php endif; ?>
