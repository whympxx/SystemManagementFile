<?php
/**
 * File Upload Handler
 * Handles file uploads with validation and database storage
 */

// Enable error reporting for debugging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/upload_errors.log');

header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/auth.php';
require_once 'security.php';

// Check authentication
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/../logs';
if (!file_exists($logsDir)) {
    mkdir($logsDir, 0755, true);
}

// Initialize database
initializeDatabase();

/**
 * Handle file upload
 */
function handleFileUpload() {
    try {
        // Log the upload attempt
        error_log("Upload attempt started");
        
        // Check if file was uploaded
        if (!isset($_FILES['file'])) {
            error_log("No file in _FILES array");
            throw new Exception('No file was selected for upload');
        }
        
        $file = $_FILES['file'];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File size exceeds server limit (upload_max_filesize)',
                UPLOAD_ERR_FORM_SIZE => 'File size exceeds form limit',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'Upload stopped by PHP extension'
            ];
            
            $errorMsg = $errorMessages[$file['error']] ?? 'Unknown upload error';
            error_log("Upload error: " . $errorMsg . " (Code: {$file['error']})");
            throw new Exception($errorMsg);
        }
        
        // Get file information
        $originalName = $file['name'];
        $tmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        
        // Additional validations
        if (empty($originalName)) {
            throw new Exception('Invalid file name');
        }
        
        if (empty($tmpName) || !is_uploaded_file($tmpName)) {
            throw new Exception('Invalid temporary file');
        }
        
        if ($fileSize <= 0) {
            throw new Exception('File is empty');
        }
        
        // Validate file size
        if ($fileSize > UPLOAD_MAX_SIZE) {
            $maxSizeMB = UPLOAD_MAX_SIZE / (1024 * 1024);
            throw new Exception("File size exceeds maximum allowed size of {$maxSizeMB}MB");
        }
        
        // Get MIME type
        if (!function_exists('mime_content_type')) {
            throw new Exception('mime_content_type function not available');
        }
        
        $mimeType = mime_content_type($tmpName);
        if ($mimeType === false) {
            $mimeType = 'application/octet-stream'; // Fallback
        }
        
        // Extract file extension
        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        // Validate file type
        if (!in_array($fileExtension, UPLOAD_ALLOWED_TYPES)) {
            throw new Exception('File type not allowed');
        }
        
        // Generate unique filename
        $timestamp = time();
        $randomString = bin2hex(random_bytes(8));
        $uniqueFilename = $timestamp . '_' . $randomString . '.' . $fileExtension;
        
        // Set upload path
        $uploadPath = UPLOAD_PATH . $uniqueFilename;
        
        // Move uploaded file
        if (!move_uploaded_file($tmpName, $uploadPath)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        // Save file information to database
        $pdo = getDBConnection();
        if (!$pdo) {
            // Clean up uploaded file if database fails
            if (file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            error_log("Database connection failed during upload");
            throw new Exception('Database connection failed');
        }
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO files (filename, original_name, file_path, file_size, file_type, mime_type) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $filePath = 'uploads/' . $uniqueFilename; // Fixed path without '../'
            $executeResult = $stmt->execute([
                $uniqueFilename,
                $originalName,
                $filePath,
                $fileSize,
                $fileExtension,
                $mimeType
            ]);
            
            if (!$executeResult) {
                // Clean up uploaded file if database insert fails
                if (file_exists($uploadPath)) {
                    unlink($uploadPath);
                }
                throw new Exception('Failed to save file information to database');
            }
            
            $fileId = $pdo->lastInsertId();
            
            if (!$fileId) {
                error_log("Database insert succeeded but no ID returned");
                throw new Exception('Failed to get file ID from database');
            }
            
            error_log("File uploaded successfully: ID=$fileId, File=$uniqueFilename");
            
        } catch (PDOException $e) {
            // Clean up uploaded file if database operation fails
            if (file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            error_log("Database error during upload: " . $e->getMessage());
            throw new Exception('Database error: ' . $e->getMessage());
        }
        
        // Return success response
        return [
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => [
                'id' => $fileId,
                'filename' => $uniqueFilename,
                'original_name' => $originalName,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'file_type' => $fileExtension,
                'mime_type' => $mimeType,
                'upload_date' => date('Y-m-d H:i:s')
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
 * Format file size for display
 */
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes) / log($k));
    
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

/**
 * Validate file type by content (additional security)
 */
function validateFileContent($tmpName, $extension) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpName);
    finfo_close($finfo);
    
    $allowedMimes = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'txt' => ['text/plain'],
        'zip' => ['application/zip'],
        'mp3' => ['audio/mpeg'],
        'mp4' => ['video/mp4'],
    ];
    
    if (isset($allowedMimes[$extension]) && in_array($mimeType, $allowedMimes[$extension])) {
        return true;
    }
    
    // For other file types, allow if extension matches common patterns
    return true; // You can make this more restrictive based on your needs
}

/**
 * Generate thumbnail for images
 */
function generateThumbnail($imagePath, $extension) {
    $thumbnailDir = UPLOAD_PATH . 'thumbnails/';
    if (!file_exists($thumbnailDir)) {
        mkdir($thumbnailDir, 0755, true);
    }
    
    $thumbnailPath = $thumbnailDir . pathinfo($imagePath, PATHINFO_FILENAME) . '_thumb.' . $extension;
    
    try {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $source = imagecreatefromjpeg($imagePath);
                break;
            case 'png':
                $source = imagecreatefrompng($imagePath);
                break;
            case 'gif':
                $source = imagecreatefromgif($imagePath);
                break;
            default:
                return false;
        }
        
        if (!$source) return false;
        
        // Get original dimensions
        list($width, $height) = getimagesize($imagePath);
        
        // Calculate thumbnail dimensions (max 150x150)
        $thumbWidth = 150;
        $thumbHeight = 150;
        
        if ($width > $height) {
            $thumbHeight = ($height / $width) * $thumbWidth;
        } else {
            $thumbWidth = ($width / $height) * $thumbHeight;
        }
        
        // Create thumbnail
        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        // Preserve transparency for PNG and GIF
        if ($extension == 'png' || $extension == 'gif') {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
            imagefilledrectangle($thumb, 0, 0, $thumbWidth, $thumbHeight, $transparent);
        }
        
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
        
        // Save thumbnail
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($thumb, $thumbnailPath, 80);
                break;
            case 'png':
                imagepng($thumb, $thumbnailPath);
                break;
            case 'gif':
                imagegif($thumb, $thumbnailPath);
                break;
        }
        
        imagedestroy($source);
        imagedestroy($thumb);
        
        return $thumbnailPath;
    } catch (Exception $e) {
        return false;
    }
}

// Handle the upload request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = handleFileUpload();
    echo json_encode($response);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
