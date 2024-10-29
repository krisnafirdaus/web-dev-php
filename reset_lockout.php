<?php
// reset_lockout.php
define('ALLOWED_ACCESS', true);
require_once 'config/config.php';

try {
    // Email yang akan di-reset
    $email = 'test@example.com';
    
    $stmt = $pdo->prepare("
        UPDATE users 
        SET login_attempts = 0,
            last_login_attempt = NULL,
            updated_at = CURRENT_TIMESTAMP
        WHERE email = ?
    ");
    
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        echo "Successfully reset lockout for $email\n";
        
        // Verify reset
        $stmt = $pdo->prepare("
            SELECT name, email, login_attempts, last_login_attempt 
            FROM users 
            WHERE email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        echo "\nCurrent user status:\n";
        print_r($user);
    } else {
        echo "No user found with email: $email\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}