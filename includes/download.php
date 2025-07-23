<?php
/**
 * File Download Handler
 * Downloads files from the upload directory
 */

require_once '../config/database.php';
require_once '../config/auth.php';

// Require authentication (but don't redirect for API calls)
if (!isLoggedIn()) {
    http_response_code(403);
    echo "<h1>Access Denied</h1><p>Please login to download files.</p><a href='../login.php'>Login</a>";
    exit;
}

/**
 * Download file by filename or ID
 */
function downloadFile($identifier, $byId = false) {
    try {
        $pdo = getDBConnection();
        if (!$pdo) {
            throw new Exception('Database connection failed');
        }
        
        // Get file information from database
        if ($byId) {
            $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND is_deleted = 0");
            $stmt->execute([$identifier]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM files WHERE filename = ? AND is_deleted = 0");
            $stmt->execute([$identifier]);
        }
        
        $fileInfo = $stmt->fetch();
        
        if (!$fileInfo) {
            throw new Exception('File not found');
        }
        
        // Construct full file path
        $filePath = UPLOAD_PATH . $fileInfo['filename'];
        
        // Check if file exists on disk
        if (!file_exists($filePath)) {
            throw new Exception('File not found on disk');
        }
        
        // Get file info
        $originalName = $fileInfo['original_name'];
        $mimeType = $fileInfo['mime_type'];
        $fileSize = $fileInfo['file_size'];
        
        // Set headers for download
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $originalName . '"');
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        
        // Clear any output buffers
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Read and output file
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            throw new Exception('Could not open file for reading');
        }
        
        while (!feof($handle)) {
            echo fread($handle, 8192);
            flush();
        }
        
        fclose($handle);
        exit;
        
    } catch (Exception $e) {
        // Log error
        error_log("Download error: " . $e->getMessage());
        
        // Show error page
        http_response_code(404);
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>File Not Found</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .error { color: #e74c3c; }
            </style>
        </head>
        <body>
            <h1 class='error'>File Not Found</h1>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
            <a href='../pages/file-manager.php'>Back to File Manager</a>
        </body>
        </html>";
        exit;
    }
}

// Handle the download request
if (isset($_GET['id'])) {
    downloadFile($_GET['id'], true);
} elseif (isset($_GET['filename'])) {
    downloadFile($_GET['filename'], false);
} else {
    http_response_code(400);
    echo "Invalid download request";
}
?>
