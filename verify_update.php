<?php
// verify_update.php
define('ALLOWED_ACCESS', true);
require_once 'config/config.php';

try {
    $email = 'test@example.com';
    $password = 'test123';
    
    echo "Verifying database update for $email\n\n";
    
    // Get user data
    $stmt = $pdo->prepare("
        SELECT id, name, email, password, login_attempts, last_login_attempt
        FROM users 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "User found:\n";
        echo "ID: {$user['id']}\n";
        echo "Name: {$user['name']}\n";
        echo "Email: {$user['email']}\n";
        echo "Login attempts: {$user['login_attempts']}\n";
        echo "Last login attempt: {$user['last_login_attempt']}\n";
        echo "Password hash: {$user['password']}\n\n";
        
        // Verify password
        $isValid = password_verify($password, $user['password']);
        echo "Password verification: " . ($isValid ? "Valid" : "Invalid") . "\n";
        
        if (!$isValid) {
            echo "\nExpected hash for 'test123': \n";
            echo password_hash($password, PASSWORD_DEFAULT) . "\n";
        }
    } else {
        echo "No user found with email: $email\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}