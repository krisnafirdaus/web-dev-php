<?php
// Function to format date
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

// Function to limit string length
function limitString($string, $length = 100) {
    if (strlen($string) > $length) {
        return substr($string, 0, $length) . '...';
    }
    return $string;
}

// Function to check permissions
function hasPermission($permission) {
    if (!isset($_SESSION['user_permissions'])) {
        return false;
    }
    return in_array($permission, $_SESSION['user_permissions']);
}

function debugLog($message, $data = null) {
    $logMessage = "[Debug] $message";
    if ($data !== null) {
        $logMessage .= ": " . print_r($data, true);
    }
    error_log($logMessage);
}

// Function to set flash message
function setFlashMessage($message, $type = 'success') {
    debugLog("Setting flash message", ['message' => $message, 'type' => $type]);
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

// Function to sanitize input
function sanitizeInput($data) {
    $original = $data;
    $sanitized = htmlspecialchars(strip_tags(trim($data)));
    debugLog("Sanitizing input", [
        'original' => $original,
        'sanitized' => $sanitized
    ]);
    return $sanitized;
}

// Function to validate email
function validateEmail($email) {
    $isValid = filter_var($email, FILTER_VALIDATE_EMAIL);
    debugLog("Validating email", [
        'email' => $email,
        'isValid' => $isValid ? 'true' : 'false'
    ]);
    return $isValid;
}

// Function to check if user exists
function userExists($email, $conn) {
    try {
        debugLog("Checking user existence", ['email' => $email]);
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $exists = $stmt->fetch() !== false;
        
        debugLog("User exists check result", ['exists' => $exists ? 'true' : 'false']);
        return $exists;
    } catch (Exception $e) {
        debugLog("Error checking user existence", ['error' => $e->getMessage()]);
        return false;
    }
}

// Function to generate random token
function generateToken($length = 32) {
    $token = bin2hex(random_bytes($length));
    debugLog("Generated token", ['length' => $length, 'token' => $token]);
    return $token;
}

// Function to log activity
function logActivity($user_id, $action, $details = '') {
    global $pdo;
    try {
        debugLog("Logging activity", [
            'user_id' => $user_id,
            'action' => $action,
            'details' => $details
        ]);

        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, details, ip_address)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR']
        ]);

        debugLog("Activity logged successfully");
        return true;
    } catch (Exception $e) {
        debugLog("Error logging activity", ['error' => $e->getMessage()]);
        return false;
    }
}

// Helper function untuk testing password
function createTestPassword($password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    debugLog("Test password created", [
        'password' => $password,
        'hash' => $hash,
        'verify_test' => password_verify($password, $hash) ? 'valid' : 'invalid'
    ]);
    return $hash;
}