<?php
/**
 * SmartBus Booking System
 * User Registration Handler
 * Phase 3 - Authentication
 */

require_once __DIR__ . '/../includes/auth.php';

start_secure_session();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}

// CSRF Protection
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Security token invalid. Please try again.';
    header('Location: ../register.php');
    exit;
}

// Sanitize and collect input
$full_name       = trim($_POST['full_name'] ?? '');
$email           = trim(strtolower($_POST['email'] ?? ''));
$phone           = trim($_POST['phone'] ?? '');
$password        = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role            = $_POST['role'] ?? 'passenger';

// Basic validation
$errors = [];

if (empty($full_name)) {
    $errors[] = 'Full name is required.';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email address is required.';
}
if (empty($password)) {
    $errors[] = 'Password is required.';
} elseif (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters long.';
}
if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match.';
}
if (!in_array($role, [ROLE_PASSENGER, ROLE_OPERATOR], true)) {
    $errors[] = 'Invalid role selected.';
}

if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    $_SESSION['old'] = $_POST; // Preserve form data
    header('Location: ../register.php');
    exit;
}

try {
    $pdo = getDBConnection();

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'An account with this email already exists.';
        $_SESSION['old'] = $_POST;
        header('Location: ../register.php');
        exit;
    }

    // Hash the password securely
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, email, password_hash, phone, role, status, created_at) 
        VALUES (?, ?, ?, ?, ?, 'active', NOW())
    ");
    
    $stmt->execute([$full_name, $email, $password_hash, $phone, $role]);
    $user_id = $pdo->lastInsertId();

    // If registering as operator, create a basic operator profile
    if ($role === ROLE_OPERATOR) {
        $company_name = $full_name . "'s Bus Company"; // Default name
        $stmt = $pdo->prepare("
            INSERT INTO operators (user_id, company_name, status, created_at) 
            VALUES (?, ?, 'active', NOW())
        ");
        $stmt->execute([$user_id, $company_name]);
    }

    // Success - clear old data
    unset($_SESSION['old']);

    // Flash success message
    $_SESSION['success'] = 'Account created successfully! You can now log in.';

    // Redirect to login
    header('Location: ../login.php');
    exit;

} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    $_SESSION['error'] = 'An unexpected error occurred. Please try again later.';
    $_SESSION['old'] = $_POST;
    header('Location: ../register.php');
    exit;
}
