<?php
// reset_admin.php
define('ALLOWED_ACCESS', true);
require_once 'config/config.php';

try {
    $adminEmail = 'admin@example.com';
    $adminPassword = 'admin123';
    
    // Generate new password hash
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
    
    // Reset admin account
    $stmt = $pdo->prepare("
        UPDATE users 
        SET password = ?,
            login_attempts = 0,
            last_login_attempt = NULL,
            last_login = NULL,
            status = 'active',
            updated_at = CURRENT_TIMESTAMP
        WHERE email = ?
    ");
    
    $stmt->execute([$hashedPassword, $adminEmail]);
    
    if ($stmt->rowCount() > 0) {
        echo "Admin account reset successfully!\n\n";
        echo "Login credentials:\n";
        echo "Email: $adminEmail\n";
        echo "Password: $adminPassword\n";
        
        // Verify the update
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$adminEmail]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "\nAccount details:\n";
        echo "ID: {$admin['id']}\n";
        echo "Name: {$admin['name']}\n";
        echo "Role: {$admin['role']}\n";
        echo "Status: {$admin['status']}\n";
        
        // Verify password
        $isValid = password_verify($adminPassword, $admin['password']);
        echo "\nPassword verification: " . ($isValid ? "Valid" : "Invalid") . "\n";
    } else {
        echo "No admin account found with email: $adminEmail\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}