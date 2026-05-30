<?php
/**
 * SmartBus Booking System
 * Custom 500 Error Page
 * Phase 8 - Final Polish
 */

http_response_code(500);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - SmartBus</title>
    <link rel="stylesheet" href="/SmartBus-Booking-System/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background: #F5F7FA; }
        .error-container {
            max-width: 500px;
            margin: 120px auto;
            text-align: center;
            padding: 2rem;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: #C62828;
            line-height: 1;
        }
        .error-message {
            font-size: 1.5rem;
            margin: 1rem 0;
            color: #1F2937;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: #C62828; margin-bottom: 1rem;"></i>
        <div class="error-code">500</div>
        <div class="error-message">Internal Server Error</div>
        <p style="color: #6B7280; margin-bottom: 2rem;">
            Something went wrong on our end. Our team has been notified.
        </p>
        
        <a href="/SmartBus-Booking-System/" class="btn btn-primary btn-lg">
            <i class="fas fa-home"></i> Return to Homepage
        </a>
        
        <div style="margin-top: 2rem; font-size: 0.9rem; color: #6B7280;">
            If the problem persists, please contact support.
        </div>
    </div>
</body>
</html>
