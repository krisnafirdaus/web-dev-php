<?php
// test_login_detailed.php
define('ALLOWED_ACCESS', true);
require_once 'config/config.php';
require_once 'includes/auth.php';

try {
    $email = 'test@example.com';
    $password = 'test123';
    
    echo "Testing login with detailed output\n\n";
    echo "Credentials:\n";
    echo "Email: $email\n";
    echo "Password: $password\n\n";
    
    // Get current database state
    $stmt = $pdo->prepare("
        SELECT id, name, email, password, login_attempts, last_login_attempt
        FROM users 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Database state before login:\n";
    print_r($user);
    echo "\n";
    
    // Test password verification directly
    $isValid = password_verify($password, $user['password']);
    echo "Direct password verification: " . ($isValid ? "Valid" : "Invalid") . "\n\n";
    
    // Try login
    $auth = new Auth();
    $loginResult = $auth->login($email, $password);
    
    echo "Login result: " . ($loginResult ? "Success" : "Failed") . "\n\n";
    
    if ($loginResult) {
        echo "Session data after login:\n";
        print_r($_SESSION);
    } else {
        // Check updated database state
        $stmt->execute([$email]);
        $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Database state after failed login:\n";
        print_r($updatedUser);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}