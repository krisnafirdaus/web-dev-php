<?php
// test_login.php
define('ALLOWED_ACCESS', true);
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Test credentials
$testCredentials = [
    'email' => 'test@example.com',
    'password' => 'test123'
];

// Initialize Auth
$auth = new Auth();

// Test login
echo "Testing login with:\n";
echo "Email: {$testCredentials['email']}\n";
echo "Password: {$testCredentials['password']}\n\n";

if ($auth->login($testCredentials['email'], $testCredentials['password'])) {
    echo "Login successful!\n";
    var_dump($_SESSION);
} else {
    echo "Login failed!\n";
}

// Verify password directly
$stmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
$stmt->execute([$testCredentials['email']]);
$storedHash = $stmt->fetchColumn();

echo "\nStored hash: $storedHash\n";
echo "Verification result: " . (password_verify($testCredentials['password'], $storedHash) ? 'Valid' : 'Invalid') . "\n";