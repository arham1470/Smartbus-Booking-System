<?php
/**
 * SmartBus Booking System
 * Logout Handler
 * Phase 3 - Authentication
 */

require_once __DIR__ . '/includes/auth.php';

start_secure_session();
logout_user();

// Redirect with logout confirmation
header('Location: index.php?logged_out=1');
exit;
?>
