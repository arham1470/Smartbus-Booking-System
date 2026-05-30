<?php
/**
 * SmartBus Booking System
 * Registration Page
 * Phase 1 - Styled shell (full backend + validation in Phase 3)
 */

$pageTitle = "Create Account";
include __DIR__ . '/includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card" style="max-width: 460px;">
        <div style="text-align: center; margin-bottom: 1rem;">
            <i class="fas fa-user-plus" style="font-size: 2.3rem; color: var(--primary);"></i>
        </div>
        
        <h2>Create Your Account</h2>
        <p class="subtitle">Join SmartBus and start booking in seconds</p>
        
        <!-- 
            NOTE: Visual shell only in Phase 1.
            Phase 3 will add server-side processing, password hashing,
            email uniqueness check, and role selection logic.
        -->
        <form action="register.php" method="POST" autocomplete="off">
            <div class="form-group">
                <label class="form-label" for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" class="form-control" 
                       placeholder="John Doe" required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           placeholder="+1 555 123 4567" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="you@example.com" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Create a strong password" minlength="8" required>
                <div class="form-hint">Minimum 8 characters. Use letters, numbers &amp; symbols.</div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                       placeholder="Repeat your password" required>
            </div>
            
            <!-- Future role selection will appear here in Phase 3 -->
            <div class="form-group">
                <label class="form-label" for="role">I am registering as</label>
                <select id="role" name="role" class="form-select" required>
                    <option value="passenger">Passenger (Book tickets)</option>
                    <option value="operator">Bus Operator (Manage fleet)</option>
                </select>
            </div>
            
            <div style="margin: 1.25rem 0; font-size: 0.85rem; color: var(--text-light);">
                <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer;">
                    <input type="checkbox" required style="margin-top: 3px;">
                    <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</span>
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block btn-lg">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>
        
        <div class="auth-links">
            Already have an account? 
            <a href="login.php" style="font-weight: 600;">Sign in instead</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
