<?php
/**
 * File Copy Handler
 * Handles file copy operations
 */

header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/auth.php';

// Require authentication
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit;
}

function handleFileCopy() {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request method');
        }
        $filename = $_POST['filename'] ?? '';
        $newName = $_POST['newname'] ?? '';
        if (empty($filename) || empty($newName)) {
            throw new Exception('Missing required parameters');
        }
        $newName = basename($newName);
        if (empty($newName)) {
            throw new Exception('Invalid new filename');
        }
        $pdo = getDBConnection();
        if (!$pdo) {
            throw new Exception('Database connection failed');
        }
        // Get file info from database
        $stmt = $pdo->prepare("SELECT * FROM files WHERE filename = ? AND is_deleted = 0");
        $stmt->execute([$filename]);
        $fileInfo = $stmt->fetch();
        if (!$fileInfo) {
            throw new Exception('File not found in database');
        }
        $oldPath = UPLOAD_PATH . $filename;
        if (!file_exists($oldPath)) {
            throw new Exception('Physical file not found');
        }
        // Generate new unique filename
        $ext = strtolower(pathinfo($fileInfo['original_name'], PATHINFO_EXTENSION));
        $newNameWithoutExt = pathinfo($newName, PATHINFO_FILENAME);
        $newExtension = strtolower(pathinfo($newName, PATHINFO_EXTENSION));
        if (empty($newExtension)) {
            $newExtension = $ext;
        }
        $newFilename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $newExtension;
        $newPath = UPLOAD_PATH . $newFilename;
        // Copy file
        if (!copy($oldPath, $newPath)) {
            throw new Exception('Failed to copy file');
        }
        // Insert new file to database
        $stmt = $pdo->prepare("INSERT INTO files (filename, original_name, file_path, file_size, file_type, mime_type) VALUES (?, ?, ?, ?, ?, ?)");
        $filePath = 'uploads/' . $newFilename;
        $stmt->execute([
            $newFilename,
            $newName,
            $filePath,
            $fileInfo['file_size'],
            $newExtension,
            $fileInfo['mime_type']
        ]);
        return [
            'success' => true,
            'message' => 'File copied successfully',
            'data' => [
                'filename' => $newFilename,
                'original_name' => $newName
            ]
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

echo json_encode(handleFileCopy()); 