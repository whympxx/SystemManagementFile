<?php
/**
 * Debug Upload Script
 * Script untuk mendiagnosis masalah upload
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Upload Debug Information</h1>";

// Check PHP upload settings
echo "<h2>PHP Upload Settings</h2>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "file_uploads: " . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "<br>";
echo "max_execution_time: " . ini_get('max_execution_time') . "<br>";
echo "memory_limit: " . ini_get('memory_limit') . "<br>";

// Check upload directory
echo "<h2>Upload Directory Check</h2>";
$uploadDir = __DIR__ . '/uploads/';
echo "Upload directory: " . $uploadDir . "<br>";
echo "Directory exists: " . (file_exists($uploadDir) ? 'Yes' : 'No') . "<br>";
echo "Directory is writable: " . (is_writable($uploadDir) ? 'Yes' : 'No') . "<br>";
echo "Directory permissions: " . substr(sprintf('%o', fileperms($uploadDir)), -4) . "<br>";

// Check database connection
echo "<h2>Database Connection Check</h2>";
try {
    require_once 'config/database.php';
    $pdo = getDBConnection();
    if ($pdo) {
        echo "Database connection: Success<br>";
        
        // Check if tables exist
        $tables = ['files', 'users', 'file_shares'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            $exists = $stmt->rowCount() > 0;
            echo "Table '$table' exists: " . ($exists ? 'Yes' : 'No') . "<br>";
        }
    } else {
        echo "Database connection: Failed<br>";
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}

// Check constants
echo "<h2>Configuration Constants</h2>";
if (defined('UPLOAD_MAX_SIZE')) {
    echo "UPLOAD_MAX_SIZE: " . UPLOAD_MAX_SIZE . " bytes (" . (UPLOAD_MAX_SIZE / (1024 * 1024)) . " MB)<br>";
} else {
    echo "UPLOAD_MAX_SIZE: Not defined<br>";
}

if (defined('UPLOAD_PATH')) {
    echo "UPLOAD_PATH: " . UPLOAD_PATH . "<br>";
} else {
    echo "UPLOAD_PATH: Not defined<br>";
}

if (defined('UPLOAD_ALLOWED_TYPES')) {
    echo "UPLOAD_ALLOWED_TYPES: " . implode(', ', UPLOAD_ALLOWED_TYPES) . "<br>";
} else {
    echo "UPLOAD_ALLOWED_TYPES: Not defined<br>";
}

// Test upload form
echo "<h2>Test Upload Form</h2>";
?>
<form action="debug_upload.php" method="post" enctype="multipart/form-data">
    <input type="file" name="test_file" required>
    <button type="submit" name="test_upload">Test Upload</button>
</form>

<?php
if (isset($_POST['test_upload'])) {
    echo "<h2>Upload Test Results</h2>";
    
    if (isset($_FILES['test_file'])) {
        $file = $_FILES['test_file'];
        
        echo "File name: " . $file['name'] . "<br>";
        echo "File size: " . $file['size'] . " bytes<br>";
        echo "File type: " . $file['type'] . "<br>";
        echo "Temp name: " . $file['tmp_name'] . "<br>";
        echo "Error code: " . $file['error'] . "<br>";
        
        // Decode error code
        $uploadErrors = [
            UPLOAD_ERR_OK => 'No error',
            UPLOAD_ERR_INI_SIZE => 'File size exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File size exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
        ];
        
        echo "Error description: " . ($uploadErrors[$file['error']] ?? 'Unknown error') . "<br>";
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            // Try to move the file
            $targetFile = $uploadDir . 'test_' . time() . '_' . $file['name'];
            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                echo "<strong>Upload successful!</strong> File saved as: " . basename($targetFile) . "<br>";
            } else {
                echo "<strong>Upload failed!</strong> Could not move file to target directory.<br>";
            }
        }
    } else {
        echo "No file uploaded.<br>";
    }
}
?>
