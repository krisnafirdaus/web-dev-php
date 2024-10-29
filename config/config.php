<?php
// config/config.php

// Prevent direct access
if (!defined('ALLOWED_ACCESS')) {
    die('Direct access not permitted');
}

// Directory configuration
define('ROOT_DIR', dirname(__DIR__));
define('LOGS_DIR', ROOT_DIR . '/logs');

// Create logs directory if needed
if (!file_exists(LOGS_DIR)) {
    mkdir(LOGS_DIR, 0777, true);
}

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'user_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', LOGS_DIR . '/error.log');

// Database connection with disabled SSL
try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        // Disable SSL
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::MYSQL_ATTR_SSL_CA => false,
    ];

    $dsn = sprintf(
        "mysql:host=%s;port=%s;dbname=%s;charset=%s",
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    
    echo "<div style='background:#fee;padding:20px;margin:20px;border:1px solid #f00;'>";
    echo "<h2>Database Connection Error</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>DSN:</strong> " . $dsn . "</p>";
    echo "</div>";
    die();
}