<?php
/**
 * SmartBus Booking System
 * Public Landing Page
 * Phase 1
 */

$pageTitle = "Welcome";
include __DIR__ . '/includes/header.php';
?>

<!-- HERO SECTION -->
<section class="hero">
    <div class="container">
        <h1>Travel Smarter.<br>Book Faster.</h1>
        <p>Book bus tickets across the region in seconds. Real-time availability, secure payments, and instant confirmations.</p>
        
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="register.php" class="btn btn-primary btn-lg">
                <i class="fas fa-user-plus"></i> Create Free Account
            </a>
            <a href="login.php" class="btn btn-secondary btn-lg">
                <i class="fas fa-sign-in-alt"></i> Login to Book
            </a>
        </div>
        
        <p style="margin-top: 1.5rem; font-size: 0.95rem; opacity: 0.9;">
            Already have an account? <a href="login.php" style="color: #90CAF9; text-decoration: underline;">Sign in here</a>
        </p>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="features" style="background: white;">
    <div class="container">
        <h2 class="text-center" style="margin-bottom: 0.5rem;">How SmartBus Works</h2>
        <p class="text-center text-muted" style="max-width: 520px; margin: 0 auto 3rem;">Three simple steps to your next journey</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 2rem;">
            <div class="feature-card card">
                <div class="feature-icon"><i class="fas fa-search"></i></div>
                <h3>1. Search Routes</h3>
                <p class="text-muted">Find buses by destination, date, and time. See real-time seat availability and pricing.</p>
            </div>
            
            <div class="feature-card card">
                <div class="feature-icon"><i class="fas fa-chair"></i></div>
                <h3>2. Choose Your Seat</h3>
                <p class="text-muted">Pick your preferred seat from an interactive map. Know exactly where you'll sit.</p>
            </div>
            
            <div class="feature-card card">
                <div class="feature-icon"><i class="fas fa-ticket-alt"></i></div>
                <h3>3. Confirm &amp; Travel</h3>
                <p class="text-muted">Pay securely and receive your digital ticket instantly via email and in your account.</p>
            </div>
        </div>
    </div>
</section>

<!-- TRUST / STATS SECTION -->
<section style="padding: 3rem 0; background: var(--bg);">
    <div class="container">
        <div class="card" style="padding: 2.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 2rem; text-align: center;">
            <div>
                <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary-dark);">150+</div>
                <div class="text-muted">Daily Departures</div>
            </div>
            <div>
                <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary-dark);">48</div>
                <div class="text-muted">Routes Covered</div>
            </div>
            <div>
                <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary-dark);">98k+</div>
                <div class="text-muted">Happy Passengers</div>
            </div>
            <div>
                <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary-dark);">4.9</div>
                <div class="text-muted">Average Rating</div>
            </div>
        </div>
    </div>
</section>

<!-- FINAL CTA -->
<section style="padding: 4rem 0; text-align: center;">
    <div class="container">
        <h2 style="margin-bottom: 1rem;">Ready to hit the road?</h2>
        <p class="text-muted" style="max-width: 440px; margin: 0 auto 1.75rem;">Join thousands of smart travelers who book with SmartBus every day.</p>
        
        <a href="register.php" class="btn btn-primary btn-lg">Get Started — It's Free</a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
