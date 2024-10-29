<?php
header('Content-Type: application/json');
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

$auth = new Auth();

try {
    // Check if user is authorized
    if (!$auth->isLoggedIn() || !hasPermission('edit_user')) {
        throw new Exception('Unauthorized access', 403);
    }

    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method', 405);
    }

    // Get user ID
    $userId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$userId) {
        throw new Exception('Invalid user ID', 400);
    }

    // Get and validate input
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (!$name || !$email) {
        throw new Exception('Missing required fields', 400);
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format', 400);
    }

    // Check if email exists for other users
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $userId]);
    if ($stmt->fetch()) {
        throw new Exception('Email already exists', 409);
    }

    // Update user
    $sql = "UPDATE users SET name = ?, email = ?";
    $params = [$name, $email];

    // Add optional fields if provided
    if ($role) {
        $sql .= ", role = ?";
        $params[] = $role;
    }
    if ($status) {
        $sql .= ", status = ?";
        $params[] = $status;
    }

    $sql .= " WHERE id = ?";
    $params[] = $userId;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Log activity
    logActivity($auth->getCurrentUser()['id'], 'update_user', "Updated user ID: $userId");

    echo json_encode([
        'success' => true,
        'message' => 'User updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}