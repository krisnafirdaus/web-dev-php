<?php
// verify_password.php
define('ALLOWED_ACCESS', true);
require_once 'config/config.php';

try {
    $email = 'test@example.com';
    $password = 'test123';
    
    // Get current hash from database
    $stmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $storedHash = $stmt->fetchColumn();
    
    echo "Testing password verification for $email\n\n";
    echo "Stored hash: $storedHash\n";
    
    // Verify password
    $isValid = password_verify($password, $storedHash);
    echo "Password verification: " . ($isValid ? "Valid" : "Invalid") . "\n";
    
    // Generate new hash for comparison
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    echo "\nNew hash generated: $newHash\n";
    echo "New hash verification: " . (password_verify($password, $newHash) ? "Valid" : "Invalid") . "\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}