<?php
// verify_admin.php
define('ALLOWED_ACCESS', true);
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Admin credentials
$adminEmail = 'admin@example.com';
$adminPassword = 'admin123';

try {
    echo "Verifying admin account...\n\n";

    // Get admin data
    $stmt = $pdo->prepare("
        SELECT id, name, email, password, role, login_attempts, last_login_attempt
        FROM users 
        WHERE email = ?
    ");
    $stmt->execute([$adminEmail]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Admin account status:\n";
    echo "ID: {$admin['id']}\n";
    echo "Name: {$admin['name']}\n";
    echo "Role: {$admin['role']}\n";
    echo "Login attempts: {$admin['login_attempts']}\n";
    echo "Last login attempt: {$admin['last_login_attempt']}\n";

    // Verify password
    $isValid = password_verify($adminPassword, $admin['password']);
    echo "\nPassword verification: " . ($isValid ? "Valid" : "Invalid") . "\n";

    // Try login
    $auth = new Auth();
    $loginResult = $auth->login($adminEmail, $adminPassword);
    echo "\nLogin attempt result: " . ($loginResult ? "Success" : "Failed") . "\n";

    if ($loginResult) {
        echo "\nSession data:\n";
        print_r($_SESSION);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}