<?php
/**
 * SmartBus Booking System
 * User Login Handler
 * Phase 3 - Authentication
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// CSRF check
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Security token invalid. Please try again.';
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$email    = trim(strtolower($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Please enter both email and password.';
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$result = attempt_login($email, $password);

if ($result['success']) {
    // Successful login
    $user = $result['user'];
    
    // Check for redirect after login
    if (!empty($_SESSION['redirect_after_login'])) {
        $redirect = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
        exit;
    }
    
    // Redirect based on role
    redirect_to_dashboard();
    
} else {
    $_SESSION['error'] = $result['error'];
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}
