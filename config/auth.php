<?php
/**
 * Authentication Configuration
 * Session management and user authentication functions
 */

// Enhanced session security
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
// Uncomment next line if using HTTPS
// ini_set('session.cookie_secure', 1);

// Set secure session name
session_name('FILEMANAGER_SESSION');

// Start session with security options
session_start([
    'cookie_lifetime' => 3600, // 1 hour
    'gc_maxlifetime' => 3600,
    'use_strict_mode' => true,
]);

// Session timeout handling
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
    // Session expired
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        require_once __DIR__ . '/database.php';
        $pdo = getDBConnection();
        
        if (!$pdo) {
            return null;
        }
        
        $stmt = $pdo->prepare("SELECT id, username, email, full_name, created_at FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Get current user error: " . $e->getMessage());
        return null;
    }
}

/**
 * Redirect if not authenticated
 */
function requireAuth($redirectTo = 'login.php') {
    if (!isLoggedIn()) {
        $currentPage = $_SERVER['PHP_SELF'];
        $redirectUrl = $redirectTo;
        
        // Add return URL for after login
        if (!empty($currentPage)) {
            $redirectUrl .= '?return=' . urlencode($currentPage);
        }
        
        header("Location: $redirectUrl");
        exit;
    }
}

/**
 * Redirect if already authenticated
 */
function redirectIfAuthenticated($redirectTo = 'index.php') {
    if (isLoggedIn()) {
        header("Location: $redirectTo");
        exit;
    }
}

/**
 * Login user
 */
function loginUser($userId) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['login_time'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
}

/**
 * Logout user
 */
function logoutUser() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if username exists
 */
function usernameExists($username) {
    try {
        require_once __DIR__ . '/database.php';
        $pdo = getDBConnection();
        
        if (!$pdo) {
            return false;
        }
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Username exists check error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if email exists
 */
function emailExists($email) {
    try {
        require_once __DIR__ . '/database.php';
        $pdo = getDBConnection();
        
        if (!$pdo) {
            return false;
        }
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Email exists check error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create new user
 */
function createUser($username, $email, $password, $fullName) {
    try {
        require_once __DIR__ . '/database.php';
        $pdo = getDBConnection();
        
        if (!$pdo) {
            throw new Exception("Database connection failed");
        }
        
        $hashedPassword = hashPassword($password);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, full_name) 
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([$username, $email, $hashedPassword, $fullName]);
        
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("Create user error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Authenticate user
 */
function authenticateUser($username, $password) {
    try {
        require_once __DIR__ . '/database.php';
        $pdo = getDBConnection();
        
        if (!$pdo) {
            return false;
        }
        
        $stmt = $pdo->prepare("
            SELECT id, username, email, password_hash, full_name 
            FROM users 
            WHERE (username = ? OR email = ?) AND is_active = 1
        ");
        $stmt->execute([$username, $username]);
        
        $user = $stmt->fetch();
        
        if ($user && verifyPassword($password, $user['password_hash'])) {
            return $user;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Authenticate user error: " . $e->getMessage());
        return false;
    }
}

/**
 * Rate limiting for login attempts
 */
function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300) {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = array();
    }
    
    $currentTime = time();
    $attempts = $_SESSION['login_attempts'];
    
    // Clean old attempts
    foreach ($attempts as $key => $attempt) {
        if ($currentTime - $attempt['time'] > $timeWindow) {
            unset($_SESSION['login_attempts'][$key]);
        }
    }
    
    // Count current attempts
    $currentAttempts = 0;
    foreach ($_SESSION['login_attempts'] as $attempt) {
        if ($attempt['identifier'] === $identifier) {
            $currentAttempts++;
        }
    }
    
    return $currentAttempts < $maxAttempts;
}

/**
 * Record login attempt
 */
function recordLoginAttempt($identifier) {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = array();
    }
    
    $_SESSION['login_attempts'][] = array(
        'identifier' => $identifier,
        'time' => time()
    );
}
?>
