<?php
// includes/session_middleware.php
function ensureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        
        session_start();
        error_log("Session started with ID: " . session_id());
    }
    
    return session_id();
}

// Add to config.php
require_once 'includes/session_middleware.php';
ensureSession();