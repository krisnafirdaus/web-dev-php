<?php
header('Content-Type: application/json');
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

$auth = new Auth();

try {
    if (!$auth->isLoggedIn()) {
        throw new Exception('Unauthorized access', 403);
    }

    // Get pagination parameters
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
    $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 10;
    $offset = ($page - 1) * $limit;

    // Get search and filter parameters
    $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING);
    $role = filter_input(INPUT_GET, 'role', FILTER_SANITIZE_STRING);

    // Build query
    $where = [];
    $params = [];

    if ($search) {
        $where[] = "(name LIKE ? OR email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($status) {
        $where[] = "status = ?";
        $params[] = $status;
    }

    if ($role) {
        $where[] = "role = ?";
        $params[] = $role;
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Get total count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users $whereClause");
    $stmt->execute($params);
    $totalItems = $stmt->fetchColumn();

    // Get users
    $stmt = $pdo->prepare("
        SELECT id, name, email, role, status, created_at, last_login
        FROM users
        $whereClause
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ");

    $stmt->execute(array_merge($params, [$limit, $offset]));
    $users = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => [
            'users' => $users,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $totalItems,
                'pages' => ceil($totalItems / $limit)
            ]
        ]
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}