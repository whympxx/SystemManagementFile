<?php
/**
 * File Delete Handler
 * Handles file deletion with database and filesystem cleanup
 */

header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/auth.php';
require_once 'security.php';

// Check authentication for AJAX requests
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit;
}

// Check CSRF token for all POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!Security::verifyCSRFToken($csrfToken)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid security token'
        ]);
        exit;
    }
}

/**
 * Delete file from both database and filesystem
 */
function deleteFile($filename) {
    try {
        $pdo = getDBConnection();
        if (!$pdo) {
            throw new Exception('Database connection failed');
        }
        
        // Get file information from database
        $stmt = $pdo->prepare("SELECT * FROM files WHERE filename = ? AND is_deleted = 0");
        $stmt->execute([$filename]);
        $fileInfo = $stmt->fetch();
        
        if (!$fileInfo) {
            throw new Exception('File not found in database');
        }
        
        // Mark as deleted in database (soft delete)
        $updateStmt = $pdo->prepare("UPDATE files SET is_deleted = 1, updated_at = CURRENT_TIMESTAMP WHERE filename = ?");
        $updateStmt->execute([$filename]);
        
        // Delete physical file
        $filePath = UPLOAD_PATH . $filename;
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                // File deleted from DB but not from filesystem, log warning
                error_log("Warning: File deleted from database but not from filesystem: " . $filePath);
            }
        }
        
        // Delete thumbnail if exists
        $thumbnailPath = UPLOAD_PATH . 'thumbnails/' . pathinfo($filename, PATHINFO_FILENAME) . '_thumb.' . pathinfo($filename, PATHINFO_EXTENSION);
        if (file_exists($thumbnailPath)) {
            unlink($thumbnailPath);
        }
        
        return [
            'success' => true,
            'message' => 'File deleted successfully',
            'data' => [
                'filename' => $filename,
                'original_name' => $fileInfo['original_name']
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Permanently delete file (hard delete)
 */
function permanentlyDeleteFile($filename) {
    try {
        $pdo = getDBConnection();
        if (!$pdo) {
            throw new Exception('Database connection failed');
        }
        
        // Get file information from database
        $stmt = $pdo->prepare("SELECT * FROM files WHERE filename = ?");
        $stmt->execute([$filename]);
        $fileInfo = $stmt->fetch();
        
        if (!$fileInfo) {
            throw new Exception('File not found in database');
        }
        
        // Delete from database permanently
        $deleteStmt = $pdo->prepare("DELETE FROM files WHERE filename = ?");
        $deleteStmt->execute([$filename]);
        
        // Delete physical file
        $filePath = UPLOAD_PATH . $filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Delete thumbnail if exists
        $thumbnailPath = UPLOAD_PATH . 'thumbnails/' . pathinfo($filename, PATHINFO_FILENAME) . '_thumb.' . pathinfo($filename, PATHINFO_EXTENSION);
        if (file_exists($thumbnailPath)) {
            unlink($thumbnailPath);
        }
        
        return [
            'success' => true,
            'message' => 'File permanently deleted',
            'data' => [
                'filename' => $filename,
                'original_name' => $fileInfo['original_name']
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Restore deleted file (undelete)
 */
function restoreFile($filename) {
    try {
        $pdo = getDBConnection();
        if (!$pdo) {
            throw new Exception('Database connection failed');
        }
        
        // Get file information from database
        $stmt = $pdo->prepare("SELECT * FROM files WHERE filename = ? AND is_deleted = 1");
        $stmt->execute([$filename]);
        $fileInfo = $stmt->fetch();
        
        if (!$fileInfo) {
            throw new Exception('Deleted file not found in database');
        }
        
        // Check if physical file still exists
        $filePath = UPLOAD_PATH . $filename;
        if (!file_exists($filePath)) {
            throw new Exception('Physical file no longer exists, cannot restore');
        }
        
        // Restore file in database
        $updateStmt = $pdo->prepare("UPDATE files SET is_deleted = 0, updated_at = CURRENT_TIMESTAMP WHERE filename = ?");
        $updateStmt->execute([$filename]);
        
        return [
            'success' => true,
            'message' => 'File restored successfully',
            'data' => [
                'filename' => $filename,
                'original_name' => $fileInfo['original_name']
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Handle the delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'delete';
    $filename = $_POST['filename'] ?? '';
    
    if (empty($filename)) {
        echo json_encode([
            'success' => false,
            'message' => 'Filename is required'
        ]);
        exit;
    }
    
    switch ($action) {
        case 'delete':
        default:
            $response = deleteFile($filename);
            break;
            
        case 'permanent':
            $response = permanentlyDeleteFile($filename);
            break;
            
        case 'restore':
            $response = restoreFile($filename);
            break;
    }
    
    echo json_encode($response);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
