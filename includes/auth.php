<?php
// includes/auth.php
class Auth {
    private $db;
    private $maxLoginAttempts = 3;
    private $lockoutTime = 900; // 15 minutes

    public function __construct() {
        global $pdo;
        $this->db = $pdo;
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            error_log("getCurrentUser called but user not logged in");
            return null;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id, name, email, role, status, avatar,
                    last_login, created_at, updated_at
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            error_log("getCurrentUser - Found user data: " . print_r($user, true));
            return $user;

        } catch (Exception $e) {
            error_log("getCurrentUser error: " . $e->getMessage());
            return null;
        }
    }

    public function login($email, $password) {
        try {
            error_log("[Auth][Login] Attempt for email: $email");
            error_log("[Auth][Login] Raw password length: " . strlen($password));

            // Check account lock
            if ($this->isAccountLocked($email)) {
                error_log("[Auth][Login] Account is locked: $email");
                throw new Exception("Account is locked. Please try again later.");
            }

            // Get user
            $stmt = $this->db->prepare("
                SELECT id, name, email, password, role, status, 
                       login_attempts, last_login_attempt
                FROM users 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Debug user data
            error_log("[Auth][Login] User data: " . print_r($user, true));

            if (!$user) {
                error_log("[Auth][Login] User not found: $email");
                throw new Exception("Invalid email or password");
            }

            // Debug password verification
            error_log("[Auth][Login] Password verification for user ID: {$user['id']}");
            error_log("[Auth][Login] Input password: " . substr($password, 0, 3) . '***');
            error_log("[Auth][Login] Stored hash: {$user['password']}");
            
            // Test hash generation
            $testHash = password_hash($password, PASSWORD_DEFAULT);
            error_log("[Auth][Login] Test hash with same password: $testHash");
            
            $passwordValid = password_verify($password, $user['password']);
            error_log("[Auth][Login] Password valid: " . ($passwordValid ? 'Yes' : 'No'));

            if (!$passwordValid) {
                error_log("[Auth][Login] Invalid password for user ID: {$user['id']}");
                $this->incrementLoginAttempts($user['id']);
                throw new Exception("Invalid email or password");
            }

            // Reset attempts and set session
            $this->resetLoginAttempts($user['id']);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            error_log("[Auth][Login] Success for user ID: {$user['id']}");
            return true;

        } catch (Exception $e) {
            error_log("[Auth][Login] Error: " . $e->getMessage());
            return false;
        }
    }

    private function isAccountLocked($email) {
        try {
            error_log("[Auth][Lock] Checking lock status for: $email");
            
            $stmt = $this->db->prepare("
                SELECT login_attempts, last_login_attempt 
                FROM users 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                error_log("[Auth][Lock] User not found: $email");
                return false;
            }

            error_log("[Auth][Lock] Attempts: {$user['login_attempts']}, Last attempt: {$user['last_login_attempt']}");

            if ($user['login_attempts'] >= $this->maxLoginAttempts && $user['last_login_attempt']) {
                $lockoutEnd = strtotime($user['last_login_attempt']) + $this->lockoutTime;
                $now = time();
                $remainingTime = $lockoutEnd - $now;

                error_log("[Auth][Lock] Lock check - End: $lockoutEnd, Now: $now, Remaining: $remainingTime");

                if ($now < $lockoutEnd) {
                    error_log("[Auth][Lock] Account is locked for {$remainingTime} more seconds");
                    return true;
                }

                error_log("[Auth][Lock] Lock expired, resetting attempts");
                $this->resetLoginAttempts($user['id']);
            }

            return false;

        } catch (Exception $e) {
            error_log("[Auth][Lock] Error: " . $e->getMessage());
            return false;
        }
    }

    public function register($userData) {
        try {
            $name = htmlspecialchars(strip_tags(trim($userData['name'])));
            $email = filter_var($userData['email'], FILTER_SANITIZE_EMAIL);
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }

            // Check if email exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception("Email already exists");
            }

            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

            // Insert user with timestamps
            $stmt = $this->db->prepare("
                INSERT INTO users (
                    name, email, password, role, status,
                    created_at, updated_at
                ) VALUES (
                    ?, ?, ?, 'user', 'active',
                    CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                )
            ");
            $stmt->execute([$name, $email, $hashedPassword]);

            return true;

        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }

    private function incrementLoginAttempts($userId) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET login_attempts = login_attempts + 1,
                last_login_attempt = CURRENT_TIMESTAMP,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
    }

    private function resetLoginAttempts($userId) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET login_attempts = 0,
                last_login_attempt = NULL,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
    }

    public function isLoggedIn() {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $isLoggedIn = isset($_SESSION['user_id']);
        error_log("isLoggedIn check - Result: " . ($isLoggedIn ? 'true' : 'false'));
        error_log("Current session data: " . print_r($_SESSION, true));
        
        return $isLoggedIn;
    }


    public function logout() {
        // Update last_login before logout
        if ($this->isLoggedIn()) {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
        }
        
        session_destroy();
        return true;
    }

    public function updateProfile($userId, $data) {
        try {
            $name = htmlspecialchars(strip_tags(trim($data['name'])));
            $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }

            // Check if email exists for other users
            $stmt = $this->db->prepare("
                SELECT id FROM users 
                WHERE email = ? AND id != ?
            ");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                throw new Exception("Email already exists");
            }

            $stmt = $this->db->prepare("
                UPDATE users 
                SET name = ?,
                    email = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$name, $email, $userId]);

            return true;

        } catch (Exception $e) {
            error_log("Update profile error: " . $e->getMessage());
            return false;
        }
    }
}