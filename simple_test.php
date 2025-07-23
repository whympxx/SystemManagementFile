<?php
// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>Simple Upload Test</h1>";
echo "<p>Current directory: " . __DIR__ . "</p>";
echo "<p>Upload directory: " . __DIR__ . "/uploads/</p>";

// Check if uploads directory exists
$uploadDir = __DIR__ . "/uploads/";
if (!file_exists($uploadDir)) {
    echo "<p style='color: red;'>Creating uploads directory...</p>";
    if (mkdir($uploadDir, 0777, true)) {
        echo "<p style='color: green;'>Uploads directory created successfully</p>";
    } else {
        echo "<p style='color: red;'>Failed to create uploads directory</p>";
    }
} else {
    echo "<p style='color: green;'>Uploads directory exists</p>";
}

// Check if directory is writable
if (is_writable($uploadDir)) {
    echo "<p style='color: green;'>Uploads directory is writable</p>";
} else {
    echo "<p style='color: red;'>Uploads directory is NOT writable</p>";
}

// Show current PHP settings
echo "<h2>PHP Upload Settings</h2>";
echo "<p>file_uploads: " . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "</p>";
echo "<p>upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>post_max_size: " . ini_get('post_max_size') . "</p>";
echo "<p>max_file_uploads: " . ini_get('max_file_uploads') . "</p>";
echo "<p>max_execution_time: " . ini_get('max_execution_time') . "</p>";

// Show _FILES array if upload attempted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Upload Attempt Detected</h2>";
    
    echo "<h3>\$_FILES array:</h3>";
    echo "<pre>" . print_r($_FILES, true) . "</pre>";
    
    echo "<h3>\$_POST array:</h3>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    if (isset($_FILES['testfile'])) {
        $file = $_FILES['testfile'];
        
        echo "<h3>File Processing:</h3>";
        echo "<p>Original name: " . $file['name'] . "</p>";
        echo "<p>Temporary file: " . $file['tmp_name'] . "</p>";
        echo "<p>File size: " . $file['size'] . " bytes</p>";
        echo "<p>File type: " . $file['type'] . "</p>";
        echo "<p>Error code: " . $file['error'] . "</p>";
        
        // Decode error code
        $errorCodes = [
            UPLOAD_ERR_OK => 'No error',
            UPLOAD_ERR_INI_SIZE => 'File size exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File size exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
        ];
        
        echo "<p>Error description: " . ($errorCodes[$file['error']] ?? 'Unknown error') . "</p>";
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            echo "<p style='color: green;'>✅ File uploaded to temporary location successfully</p>";
            
            // Check if temp file exists and is readable
            if (file_exists($file['tmp_name'])) {
                echo "<p style='color: green;'>✅ Temporary file exists and is readable</p>";
                echo "<p>Temporary file size: " . filesize($file['tmp_name']) . " bytes</p>";
                
                // Try to move file
                $targetFile = $uploadDir . "simple_test_" . time() . "_" . $file['name'];
                
                echo "<p>Attempting to move to: " . $targetFile . "</p>";
                
                if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                    echo "<p style='color: green;'>✅ SUCCESS! File moved successfully to: " . basename($targetFile) . "</p>";
                    echo "<p>Final file size: " . filesize($targetFile) . " bytes</p>";
                } else {
                    echo "<p style='color: red;'>❌ FAILED to move uploaded file</p>";
                    echo "<p>move_uploaded_file() returned false</p>";
                    
                    // Additional debugging
                    $lastError = error_get_last();
                    if ($lastError) {
                        echo "<p>Last error: " . $lastError['message'] . "</p>";
                    }
                }
            } else {
                echo "<p style='color: red;'>❌ Temporary file does not exist or is not readable</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Upload error occurred</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No file found in \$_FILES['testfile']</p>";
    }
} else {
    echo "<h2>No upload attempt yet</h2>";
}
?>

<h2>Test Upload Form</h2>
<form method="post" enctype="multipart/form-data" style="border: 1px solid #ccc; padding: 20px; margin: 20px 0;">
    <p><strong>Select a small test file (txt, jpg, etc.):</strong></p>
    <input type="file" name="testfile" required style="margin: 10px 0;">
    <br>
    <button type="submit" style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; margin: 10px 0;">Upload Test File</button>
</form>

<h2>Manual File Creation Test</h2>
<?php
// Test creating a file manually
$testContent = "This is a test file created at " . date('Y-m-d H:i:s');
$testFile = $uploadDir . "manual_test_" . time() . ".txt";

if (file_put_contents($testFile, $testContent)) {
    echo "<p style='color: green;'>✅ Manual file creation successful: " . basename($testFile) . "</p>";
} else {
    echo "<p style='color: red;'>❌ Manual file creation failed</p>";
}
?>

<p><a href="direct_upload_test.php">Advanced Upload Test</a> | <a href="index.php">Back to Dashboard</a></p>
