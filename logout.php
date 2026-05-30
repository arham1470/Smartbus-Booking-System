<?php
/**
 * SmartBus Booking System
 * Logout Handler (Placeholder)
 * 
 * Phase 1: Simple session destroy stub.
 * Full implementation with proper redirect in Phase 3.
 */

session_start();

// Clear all session data
$_SESSION = [];

// Destroy the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redirect to homepage
header("Location: index.php?logged_out=1");
exit;
?>
