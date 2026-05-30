<?php
/**
 * SmartBus Booking System
 * Database Connection Configuration (PDO)
 * 
 * This file provides a secure, reusable PDO database connection.
 * Uses prepared statements by default (never use direct queries).
 * 
 * Phase 1: Foundation - Connection ready for Phase 2 database.
 */

// Prevent direct access
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    http_response_code(403);
    exit('Direct access to this file is not allowed.');
}

// ============================================
// DATABASE CONFIGURATION
// Update these values after creating the database in Phase 2
// ============================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'smartbus_db');
define('DB_USER', 'root');
define('DB_PASS', '');           // Default XAMPP password is empty
define('DB_CHARSET', 'utf8mb4');

// ============================================
// PDO Connection Function
// ============================================

/**
 * Get a PDO database connection instance.
 * 
 * @return PDO
 * @throws PDOException on connection failure
 */
function getDBConnection(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,   // Use real prepared statements
            PDO::ATTR_PERSISTENT         => false,   // Better for shared hosting
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In production, log this error instead of showing it
            error_log("Database Connection Failed: " . $e->getMessage());
            
            // Development-friendly error (remove or customize for production)
            die("
                <div style='font-family: system-ui, sans-serif; max-width: 600px; margin: 80px auto; padding: 24px; border: 2px solid #C62828; border-radius: 12px; background: #fff;'>
                    <h2 style='color: #C62828; margin-top:0;'>Database Connection Error</h2>
                    <p><strong>Could not connect to the SmartBus database.</strong></p>
                    <p>This is expected until <strong>Phase 2 (Database Design)</strong> is completed.</p>
                    <hr style='margin: 20px 0; border: none; border-top: 1px solid #eee;'>
                    <p><strong>Next steps:</strong></p>
                    <ol style='line-height: 1.7;'>
                        <li>Open XAMPP and start MySQL</li>
                        <li>Go to <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>
                        <li>Create database named <code>smartbus_db</code></li>
                        <li>Import the SQL file from <code>database/smartbus.sql</code> (Phase 2)</li>
                        <li>Verify credentials in <code>config/db.php</code></li>
                    </ol>
                    <p style='margin-top: 20px; font-size: 0.9em; color: #666;'>Error details have been logged for administrators.</p>
                </div>
            ");
        }
    }
    
    return $pdo;
}

/**
 * Helper function to safely close connection (optional - PDO closes automatically)
 */
function closeDBConnection(): void {
    // PDO connections are closed when variable goes out of scope
    // This function exists for explicit cleanup if needed in future
}
