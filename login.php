<?php
/**
 * SmartBus Booking System
 * Login Page
 * Phase 3 - Fully functional with backend
 */

require_once __DIR__ . '/includes/auth.php';
start_secure_session();

$pageTitle = "Login";

// Display messages from session
$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);

include __DIR__ . '/includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <i class="fas fa-bus" style="font-size: 2.5rem; color: var(--primary);"></i>
        </div>
        
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in to your SmartBus account</p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <form action="actions/login_action.php" method="POST" autocomplete="on">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="you@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Enter your password" required>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; font-size: 0.9rem;">
                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="#" style="font-size: 0.9rem;">Forgot password?</a>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block btn-lg">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>
        
        <div class="auth-links">
            Don't have an account? 
            <a href="register.php" style="font-weight: 600;">Create one now</a>
        </div>
        
        <div class="alert alert-info" style="margin-top: 1.5rem; font-size: 0.8rem;">
            <strong>Test Accounts:</strong><br>
            Admin: admin@smartbus.com<br>
            Operator: michael@expressbus.com<br>
            Passenger: james.wilson@email.com<br>
            <em>All passwords: Password123</em>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
