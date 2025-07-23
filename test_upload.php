<?php
/**
 * Simple Upload Test
 * Test file untuk debugging upload functionality
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database config
require_once 'config/database.php';

// Initialize database
initializeDatabase();

echo "<h1>Upload Test</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    echo "<h2>Processing Upload...</h2>";
    
    $file = $_FILES['test_file'];
    
    echo "<h3>File Information:</h3>";
    echo "Name: " . $file['name'] . "<br>";
    echo "Size: " . $file['size'] . " bytes<br>";
    echo "Type: " . $file['type'] . "<br>";
    echo "Error: " . $file['error'] . "<br>";
    echo "Temp Name: " . $file['tmp_name'] . "<br>";
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        echo "<br><strong style='color: green;'>✓ File uploaded successfully to temporary location</strong><br>";
        
        // Test moving file
        $targetDir = __DIR__ . '/uploads/';
        $targetFile = $targetDir . 'test_' . time() . '_' . basename($file['name']);
        
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            echo "<strong style='color: green;'>✓ File moved successfully to: " . basename($targetFile) . "</strong><br>";
            
            // Test database insertion
            try {
                $pdo = getDBConnection();
                if ($pdo) {
                    $stmt = $pdo->prepare("
                        INSERT INTO files (filename, original_name, file_path, file_size, file_type, mime_type) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    
                    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $mimeType = mime_content_type($targetFile) ?: 'application/octet-stream';
                    
                    $stmt->execute([
                        basename($targetFile),
                        $file['name'],
                        'uploads/' . basename($targetFile),
                        $file['size'],
                        $extension,
                        $mimeType
                    ]);
                    
                    echo "<strong style='color: green;'>✓ Database record created successfully</strong><br>";
                    echo "File ID: " . $pdo->lastInsertId() . "<br>";
                } else {
                    echo "<strong style='color: red;'>✗ Database connection failed</strong><br>";
                }
            } catch (Exception $e) {
                echo "<strong style='color: red;'>✗ Database error: " . $e->getMessage() . "</strong><br>";
            }
        } else {
            echo "<strong style='color: red;'>✗ Failed to move file</strong><br>";
        }
    } else {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File size exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File size exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
        ];
        
        $errorMsg = $errorMessages[$file['error']] ?? 'Unknown error';
        echo "<strong style='color: red;'>✗ Upload Error: " . $errorMsg . "</strong><br>";
    }
}
?>

<h2>Test Upload Form</h2>
<form method="post" enctype="multipart/form-data" style="border: 1px solid #ccc; padding: 20px; margin: 20px 0;">
    <label for="test_file">Select file to upload:</label><br><br>
    <input type="file" name="test_file" id="test_file" required><br><br>
    <button type="submit" style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px;">Upload Test File</button>
</form>

<h2>PHP Configuration</h2>
<div style="background: #f0f0f0; padding: 10px; margin: 10px 0;">
    <strong>upload_max_filesize:</strong> <?php echo ini_get('upload_max_filesize'); ?><br>
    <strong>post_max_size:</strong> <?php echo ini_get('post_max_size'); ?><br>
    <strong>max_file_uploads:</strong> <?php echo ini_get('max_file_uploads'); ?><br>
    <strong>file_uploads:</strong> <?php echo ini_get('file_uploads') ? 'Enabled' : 'Disabled'; ?><br>
    <strong>max_execution_time:</strong> <?php echo ini_get('max_execution_time'); ?><br>
    <strong>memory_limit:</strong> <?php echo ini_get('memory_limit'); ?><br>
</div>

<h2>Directory Information</h2>
<div style="background: #f0f0f0; padding: 10px; margin: 10px 0;">
    <?php
    $uploadDir = __DIR__ . '/uploads/';
    echo "<strong>Upload Directory:</strong> " . $uploadDir . "<br>";
    echo "<strong>Directory Exists:</strong> " . (file_exists($uploadDir) ? 'Yes' : 'No') . "<br>";
    echo "<strong>Directory Writable:</strong> " . (is_writable($uploadDir) ? 'Yes' : 'No') . "<br>";
    if (file_exists($uploadDir)) {
        echo "<strong>Directory Permissions:</strong> " . substr(sprintf('%o', fileperms($uploadDir)), -4) . "<br>";
    }
    ?>
</div>

<h2>Application Constants</h2>
<div style="background: #f0f0f0; padding: 10px; margin: 10px 0;">
    <?php
    if (defined('UPLOAD_MAX_SIZE')) {
        echo "<strong>UPLOAD_MAX_SIZE:</strong> " . UPLOAD_MAX_SIZE . " bytes (" . (UPLOAD_MAX_SIZE / (1024 * 1024)) . " MB)<br>";
    }
    if (defined('UPLOAD_PATH')) {
        echo "<strong>UPLOAD_PATH:</strong> " . UPLOAD_PATH . "<br>";
    }
    if (defined('UPLOAD_ALLOWED_TYPES')) {
        echo "<strong>UPLOAD_ALLOWED_TYPES:</strong> " . implode(', ', UPLOAD_ALLOWED_TYPES) . "<br>";
    }
    ?>
</div>

<p><a href="debug_upload.php">Go to Debug Upload Page</a> | <a href="index.php">Back to Dashboard</a></p>
