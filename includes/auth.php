<?php
/**
 * SmartBus Booking System
 * Authentication & Authorization Helper
 * 
 * Phase 3: Complete secure authentication system
 * 
 * This file provides:
 * - Secure session management
 * - CSRF protection
 * - Login / Logout functions
 * - Role-based access control (guards)
 * - Current user retrieval
 */

if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/constants.php';

// ============================================
// SECURE SESSION HANDLING
// ============================================

/**
 * Start a secure session with proper configuration
 */
function start_secure_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Secure session settings
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');

    // Only set secure cookie if HTTPS is available (comment out for local XAMPP)
    // ini_set('session.cookie_secure', 1);

    session_start();

    // Regenerate session ID periodically to prevent fixation
    if (!isset($_SESSION['last_regeneration'])) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token from POST request
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

// ============================================
// CURRENT USER & LOGIN STATUS
// ============================================

/**
 * Check if user is currently logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged-in user data from database
 * Returns null if not logged in
 */
function get_current_user() {
    if (!is_logged_in()) {
        return null;
    }

    static $user = null;
    
    if ($user !== null) {
        return $user;
    }

    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT id, full_name, email, phone, role, status, created_at 
            FROM users 
            WHERE id = ? AND status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user) {
            // Add a display name alias for convenience
            $user['name'] = $user['full_name'];
        }
        
        return $user;
    } catch (Exception $e) {
        error_log("get_current_user error: " . $e->getMessage());
        return null;
    }
}

/**
 * Get current user's role
 */
function get_current_role() {
    $user = get_current_user();
    return $user ? $user['role'] : null;
}

// ============================================
// AUTHENTICATION ACTIONS
// ============================================

/**
 * Attempt to log in a user
 */
function attempt_login($email, $password) {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        SELECT id, full_name, email, password_hash, role, status 
        FROM users 
        WHERE email = ? 
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'error' => 'Invalid email or password.'];
    }

    if ($user['status'] !== 'active') {
        return ['success' => false, 'error' => 'Your account has been suspended or deactivated.'];
    }

    if (!password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'error' => 'Invalid email or password.'];
    }

    // Successful login - set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['full_name'];
    
    // Regenerate session ID after login (security)
    session_regenerate_id(true);

    return ['success' => true, 'user' => $user];
}

/**
 * Log out current user
 */
function logout_user() {
    // Unset all session variables
    $_SESSION = [];

    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}

// ============================================
// AUTHORIZATION GUARDS (Role-based access)
// ============================================

/**
 * Require user to be logged in. Redirects to login if not.
 */
function require_login() {
    start_secure_session();
    
    if (!is_logged_in()) {
        // Store intended destination for after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        header('Location: ' . (strpos($_SERVER['PHP_SELF'], 'admin/') !== false ? '../login.php' : 'login.php') . '?redirected=1');
        exit;
    }
}

/**
 * Require specific role(s). Must be logged in first.
 */
function require_role($allowed_roles) {
    require_login();
    
    $current_role = get_current_role();
    
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }
    
    if (!in_array($current_role, $allowed_roles, true)) {
        http_response_code(403);
        die("
            <div style='max-width:600px;margin:80px auto;padding:2rem;font-family:sans-serif;border:2px solid #C62828;border-radius:12px;background:#fff;'>
                <h2 style='color:#C62828;margin-top:0;'>Access Denied</h2>
                <p>You do not have permission to access this page.</p>
                <p><strong>Required role(s):</strong> " . implode(', ', $allowed_roles) . "</p>
                <p><strong>Your role:</strong> " . htmlspecialchars($current_role ?? 'None') . "</p>
                <a href='javascript:history.back()' style='color:#1565C0;'>← Go back</a>
            </div>
        ");
    }
}

/**
 * Redirect user to their appropriate dashboard based on role
 */
function redirect_to_dashboard() {
    $role = get_current_role();
    $dashboard = get_role_dashboard($role);
    
    // Handle path differences if we're already inside a subfolder
    $base = (strpos($_SERVER['PHP_SELF'], '/passenger/') !== false || 
             strpos($_SERVER['PHP_SELF'], '/operator/') !== false || 
             strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../' : '';
    
    header('Location: ' . $base . $dashboard);
    exit;
}

// ============================================
// FLASH MESSAGES (Success / Error notifications)
// ============================================

/**
 * Set a flash message
 */
function set_flash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Get and clear flash messages
 */
function get_flash($type = null) {
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    
    if ($type) {
        if (isset($_SESSION['flash'][$type])) {
            $msg = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $msg;
        }
        return null;
    }
    
    $flashes = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flashes;
}

/**
 * Check if there are any flash messages
 */
function has_flash() {
    return !empty($_SESSION['flash']);
}

// ============================================
// OPERATOR-SPECIFIC HELPERS
// ============================================

/**
 * Get the operator record for the currently logged-in operator user.
 * Returns null if not an operator or no operator profile exists.
 */
function get_current_operator() {
    if (get_current_role() !== ROLE_OPERATOR) {
        return null;
    }

    static $operator = null;
    if ($operator !== null) {
        return $operator;
    }

    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT o.*, u.full_name 
            FROM operators o
            JOIN users u ON o.user_id = u.id
            WHERE o.user_id = ? AND o.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $operator = $stmt->fetch();

        return $operator;
    } catch (Exception $e) {
        error_log("get_current_operator error: " . $e->getMessage());
        return null;
    }
}
