<?php
/**
 * Direct Upload Test (No Authentication)
 * Test upload functionality without authentication requirements
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include only database config (skip auth)
require_once 'config/database.php';

// Initialize database
initializeDatabase();

echo "<!DOCTYPE html>";
echo "<html><head><title>Direct Upload Test</title>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { background: #f0f0f0; padding: 10px; margin: 10px 0; }
    form { border: 1px solid #ccc; padding: 20px; margin: 20px 0; }
    button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
    button:hover { background: #005a9e; }
    .log { background: #f8f8f8; border: 1px solid #ddd; padding: 10px; margin: 10px 0; max-height: 200px; overflow-y: auto; }
</style>";
echo "</head><body>";

echo "<h1>Direct Upload Test (No Authentication)</h1>";
echo "<p>This page tests upload functionality without authentication requirements.</p>";

// Handle AJAX upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    // Use the same upload function from includes/upload.php
    $response = handleDirectUpload();
    echo json_encode($response);
    exit;
}

function handleDirectUpload() {
    try {
        error_log("Direct upload attempt started");
        
        if (!isset($_FILES['file'])) {
            error_log("No file in _FILES array");
            throw new Exception('No file was selected for upload');
        }
        
        $file = $_FILES['file'];
        
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
        
        $originalName = $file['name'];
        $tmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        
        if (empty($originalName)) {
            throw new Exception('Invalid file name');
        }
        
        if (empty($tmpName) || !is_uploaded_file($tmpName)) {
            throw new Exception('Invalid temporary file');
        }
        
        if ($fileSize <= 0) {
            throw new Exception('File is empty');
        }
        
        if ($fileSize > UPLOAD_MAX_SIZE) {
            $maxSizeMB = UPLOAD_MAX_SIZE / (1024 * 1024);
            throw new Exception("File size exceeds maximum allowed size of {$maxSizeMB}MB");
        }
        
        $mimeType = mime_content_type($tmpName);
        if ($mimeType === false) {
            $mimeType = 'application/octet-stream';
        }
        
        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, UPLOAD_ALLOWED_TYPES)) {
            throw new Exception('File type not allowed: ' . $fileExtension);
        }
        
        $timestamp = time();
        $randomString = bin2hex(random_bytes(8));
        $uniqueFilename = $timestamp . '_' . $randomString . '.' . $fileExtension;
        
        $uploadPath = UPLOAD_PATH . $uniqueFilename;
        
        if (!move_uploaded_file($tmpName, $uploadPath)) {
            throw new Exception('Failed to move uploaded file to: ' . $uploadPath);
        }
        
        // Test database insertion
        $pdo = getDBConnection();
        if (!$pdo) {
            if (file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            throw new Exception('Database connection failed');
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO files (filename, original_name, file_path, file_size, file_type, mime_type) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $filePath = 'uploads/' . $uniqueFilename;
        $executeResult = $stmt->execute([
            $uniqueFilename,
            $originalName,
            $filePath,
            $fileSize,
            $fileExtension,
            $mimeType
        ]);
        
        if (!$executeResult) {
            if (file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            throw new Exception('Failed to save file information to database');
        }
        
        $fileId = $pdo->lastInsertId();
        
        error_log("File uploaded successfully: ID=$fileId, File=$uniqueFilename");
        
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
        error_log("Direct upload error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Handle regular form upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['ajax'])) {
    echo "<h2>Processing Upload...</h2>";
    
    $result = handleDirectUpload();
    
    if ($result['success']) {
        echo "<div class='success'>✅ " . $result['message'] . "</div>";
        if (isset($result['data'])) {
            echo "<div class='info'>";
            echo "<strong>File Details:</strong><br>";
            echo "File ID: " . $result['data']['id'] . "<br>";
            echo "Original Name: " . htmlspecialchars($result['data']['original_name']) . "<br>";
            echo "Stored As: " . htmlspecialchars($result['data']['filename']) . "<br>";
            echo "Size: " . number_format($result['data']['file_size']) . " bytes<br>";
            echo "Type: " . $result['data']['file_type'] . "<br>";
            echo "MIME: " . $result['data']['mime_type'] . "<br>";
            echo "Path: " . $result['data']['file_path'] . "<br>";
            echo "</div>";
        }
    } else {
        echo "<div class='error'>❌ " . $result['message'] . "</div>";
    }
}
?>

<h2>Regular Form Upload</h2>
<form method="post" enctype="multipart/form-data">
    <label for="regular_file">Select file to upload:</label><br><br>
    <input type="file" name="file" id="regular_file" required><br><br>
    <button type="submit">Upload File (Regular)</button>
</form>

<h2>AJAX Upload Test</h2>
<form id="ajax-form" enctype="multipart/form-data">
    <label for="ajax_file">Select file to upload:</label><br><br>
    <input type="file" name="file" id="ajax_file" required><br><br>
    <button type="button" onclick="uploadWithAjax()">Upload File (AJAX)</button>
    <div id="ajax-progress" style="display: none; margin-top: 10px;">
        <div>Progress: <span id="progress-text">0%</span></div>
        <div style="background: #f0f0f0; height: 20px; border-radius: 10px; overflow: hidden;">
            <div id="progress-bar" style="background: #007cba; height: 100%; width: 0%; transition: width 0.3s;"></div>
        </div>
    </div>
</form>

<div id="ajax-result"></div>

<h2>Upload Configuration</h2>
<div class="info">
    <strong>PHP Settings:</strong><br>
    upload_max_filesize: <?php echo ini_get('upload_max_filesize'); ?><br>
    post_max_size: <?php echo ini_get('post_max_size'); ?><br>
    max_file_uploads: <?php echo ini_get('max_file_uploads'); ?><br>
    file_uploads: <?php echo ini_get('file_uploads') ? 'Enabled' : 'Disabled'; ?><br><br>
    
    <strong>Application Settings:</strong><br>
    UPLOAD_MAX_SIZE: <?php echo defined('UPLOAD_MAX_SIZE') ? number_format(UPLOAD_MAX_SIZE) . ' bytes (' . (UPLOAD_MAX_SIZE / (1024 * 1024)) . ' MB)' : 'Not defined'; ?><br>
    UPLOAD_PATH: <?php echo defined('UPLOAD_PATH') ? UPLOAD_PATH : 'Not defined'; ?><br>
    UPLOAD_ALLOWED_TYPES: <?php echo defined('UPLOAD_ALLOWED_TYPES') ? implode(', ', UPLOAD_ALLOWED_TYPES) : 'Not defined'; ?><br>
</div>

<h2>Recent Error Log</h2>
<div class="log">
    <?php
    $logFile = __DIR__ . '/logs/upload_errors.log';
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        echo "<pre>" . htmlspecialchars($logs) . "</pre>";
    } else {
        echo "No error log found.";
    }
    ?>
</div>

<script>
function uploadWithAjax() {
    const form = document.getElementById('ajax-form');
    const fileInput = document.getElementById('ajax_file');
    const progressDiv = document.getElementById('ajax-progress');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const resultDiv = document.getElementById('ajax-result');
    
    if (!fileInput.files[0]) {
        alert('Please select a file');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', fileInput.files[0]);
    
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            progressBar.style.width = percentComplete + '%';
            progressText.textContent = Math.round(percentComplete) + '%';
        }
    });
    
    xhr.addEventListener('load', function() {
        progressDiv.style.display = 'none';
        
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    resultDiv.innerHTML = '<div class="success">✅ ' + response.message + '</div>';
                    if (response.data) {
                        resultDiv.innerHTML += '<div class="info"><strong>File Details:</strong><br>' +
                            'File ID: ' + response.data.id + '<br>' +
                            'Original Name: ' + response.data.original_name + '<br>' +
                            'Stored As: ' + response.data.filename + '<br>' +
                            'Size: ' + response.data.file_size.toLocaleString() + ' bytes<br>' +
                            'Type: ' + response.data.file_type + '<br>' +
                            'MIME: ' + response.data.mime_type + '<br>' +
                            '</div>';
                    }
                } else {
                    resultDiv.innerHTML = '<div class="error">❌ ' + response.message + '</div>';
                }
            } catch (e) {
                console.error('JSON Parse Error:', e);
                console.error('Response Text:', xhr.responseText);
                resultDiv.innerHTML = '<div class="error">❌ Invalid server response</div>';
                resultDiv.innerHTML += '<div class="log"><pre>' + xhr.responseText + '</pre></div>';
            }
        } else {
            resultDiv.innerHTML = '<div class="error">❌ HTTP Error: ' + xhr.status + ' ' + xhr.statusText + '</div>';
        }
    });
    
    xhr.addEventListener('error', function() {
        progressDiv.style.display = 'none';
        resultDiv.innerHTML = '<div class="error">❌ Network error</div>';
    });
    
    progressDiv.style.display = 'block';
    progressBar.style.width = '0%';
    progressText.textContent = '0%';
    resultDiv.innerHTML = '';
    
    xhr.open('POST', '?ajax=1');
    xhr.send(formData);
}
</script>

<p><a href="test_upload.php">Regular Upload Test</a> | <a href="debug_upload.php">Debug Page</a> | <a href="init_database.php">Database Init</a> | <a href="index.php">Back to Dashboard</a></p>

</body></html>
