<?php
header('Content-Type: application/json');
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

$auth = new Auth();

try {
    // Check if user is authorized
    if (!$auth->isLoggedIn() || !hasPermission('create_user')) {
        throw new Exception('Unauthorized access', 403);
    }

    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method', 405);
    }

    // Validate and sanitize input
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? null;
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (!$name || !$email || !$password) {
        throw new Exception('Missing required fields', 400);
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format', 400);
    }

    // Check if email already exists
    if (userExists($email, $pdo)) {
        throw new Exception('Email already exists', 409);
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role, status)
        VALUES (?, ?, ?, ?, 'active')
    ");

    $stmt->execute([$name, $email, $hashedPassword, $role ?? 'user']);
    $userId = $pdo->lastInsertId();

    // Log activity
    logActivity($auth->getCurrentUser()['id'], 'create_user', "Created user ID: $userId");

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'User created successfully',
        'userId' => $userId
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}