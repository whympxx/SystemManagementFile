<?php
/**
 * CSRF Token Endpoint
 * Returns CSRF token for JavaScript requests
 */

header('Content-Type: application/json');
require_once '../config/auth.php';

// Check if user is authenticated
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit;
}

// Return CSRF token
echo json_encode([
    'success' => true,
    'token' => generateCSRFToken()
]);
?>
