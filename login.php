<?php
/**
 * SmartBus Booking System
 * Login Page
 * Phase 1 - Styled shell (backend logic in Phase 3)
 */

$pageTitle = "Login";
include __DIR__ . '/includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <i class="fas fa-bus" style="font-size: 2.5rem; color: var(--primary);"></i>
        </div>
        
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in to your SmartBus account</p>
        
        <!-- 
            NOTE: This form is a visual shell in Phase 1.
            In Phase 3, action will point to actions/login_action.php
            and will include proper validation + CSRF protection.
        -->
        <form action="login.php" method="POST" autocomplete="on">
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="you@example.com" required value="">
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
        
        <!-- Demo credentials hint (remove in production) -->
        <div class="alert alert-info" style="margin-top: 1.75rem; font-size: 0.85rem; padding: 0.75rem 1rem;">
            <strong>Phase 1 Demo:</strong> Form is styled and ready.<br>
            Backend authentication will be added in Phase 3.
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
