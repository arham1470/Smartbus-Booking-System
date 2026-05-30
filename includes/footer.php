<?php
/**
 * SmartBus Booking System
 * Reusable Footer Component
 * Phase 1
 */
?>
<footer style="background: #1F2937; color: #9CA3AF; padding: 2.5rem 0; margin-top: 4rem; font-size: 0.9rem;">
    <div class="container">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 2rem;">
            <!-- Brand -->
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 0.5rem;">
                    <i class="fas fa-bus" style="color: #42A5F5; font-size: 1.3rem;"></i>
                    <strong style="color: #fff; font-size: 1.1rem;">SmartBus</strong>
                </div>
                <p style="max-width: 260px; line-height: 1.5;">Reliable bus booking platform for modern travelers across the region.</p>
            </div>
            
            <!-- Links -->
            <div>
                <strong style="color: #fff; display: block; margin-bottom: 0.5rem;">Platform</strong>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <a href="index.php" style="color: #9CA3AF;">Home</a>
                    <a href="login.php" style="color: #9CA3AF;">Book a Trip</a>
                    <a href="register.php" style="color: #9CA3AF;">Create Account</a>
                </div>
            </div>
            
            <div>
                <strong style="color: #fff; display: block; margin-bottom: 0.5rem;">Support</strong>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <a href="#" style="color: #9CA3AF;">Help Center</a>
                    <a href="#" style="color: #9CA3AF;">Contact Us</a>
                    <a href="#" style="color: #9CA3AF;">Terms &amp; Privacy</a>
                </div>
            </div>
        </div>
        
        <div style="border-top: 1px solid #374151; margin-top: 2rem; padding-top: 1.25rem; text-align: center; font-size: 0.8rem;">
            &copy; <?= date('Y') ?> SmartBus Booking System. All rights reserved. &nbsp;|&nbsp; Phase 1 Foundation
        </div>
    </div>
</footer>

<!-- Load main JavaScript -->
<script src="assets/js/script.js"></script>
</body>
</html>
