<?php
/**
 * SmartBus Booking System
 * Application Constants
 * Phase 3 - Authentication
 */

if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

// User Roles
define('ROLE_PASSENGER', 'passenger');
define('ROLE_OPERATOR',  'operator');
define('ROLE_ADMIN',     'admin');

// User Status
define('STATUS_ACTIVE',   'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_SUSPENDED','suspended');

// Booking Status (for future use)
define('BOOKING_PENDING',   'pending');
define('BOOKING_CONFIRMED', 'confirmed');
define('BOOKING_CANCELLED', 'cancelled');
define('BOOKING_COMPLETED', 'completed');

// Default redirect after login based on role
function get_role_dashboard($role) {
    switch ($role) {
        case ROLE_OPERATOR:
            return 'operator/dashboard.php';
        case ROLE_ADMIN:
            return 'admin/dashboard.php';
        case ROLE_PASSENGER:
        default:
            return 'passenger/dashboard.php';
    }
}
