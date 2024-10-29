
<?php
header('Content-Type: application/json');
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

$auth = new Auth();

try {
    // Check if user is authorized
    if (!$auth->isLoggedIn() || !hasPermission('delete_user')) {
        throw new Exception('Unauthorized access', 403);
    }

    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        throw new Exception('Invalid request method', 405);
    }

    // Get user ID
    $userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$userId) {
        throw new Exception('Invalid user ID', 400);
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) {
        throw new Exception('User not found', 404);
    }

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Delete user profile
        $stmt = $pdo->prepare("DELETE FROM user_profiles WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        // Log activity
        logActivity($auth->getCurrentUser()['id'], 'delete_user', "Deleted user ID: $userId");

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}