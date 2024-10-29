<?php
// test_password.php
define('ALLOWED_ACCESS', true);
require_once 'config/config.php';

// Generate hash untuk password test123
$password = 'test123';
$hash = password_hash($password, PASSWORD_DEFAULT);

// Verify hash
$isValid = password_verify($password, $hash);

echo "Password: $password\n";
echo "Hash: $hash\n";
echo "Verification: " . ($isValid ? 'Valid' : 'Invalid') . "\n";

// SQL untuk update user
echo "\nSQL Query:\n";
echo "UPDATE users SET password = '$hash' WHERE email = 'test@example.com';\n";

// SQL untuk insert user baru
echo "\nSQL for new user:\n";
echo "INSERT INTO users (name, email, password, role, status, created_at, updated_at)
VALUES ('Test User', 'test@example.com', '$hash', 'user', 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);\n";