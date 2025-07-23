<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/auth.php';

// Check authentication
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$filename = $_POST['filename'] ?? '';
$newname = $_POST['newname'] ?? '';
$uploadDir = '../uploads/';

if (!$filename || !$newname) {
    echo json_encode(['success' => false, 'message' => 'Parameter missing']);
    exit;
}

// Validasi nama file baru (tidak boleh mengandung karakter ilegal)
if (preg_match('/[\\\/:*?"<>|]/', $newname)) {
    echo json_encode(['success' => false, 'message' => 'Nama file baru mengandung karakter tidak valid']);
    exit;
}

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    // Get file info from database
    $stmt = $pdo->prepare("SELECT * FROM files WHERE filename = ? AND is_deleted = 0");
    $stmt->execute([$filename]);
    $fileInfo = $stmt->fetch();
    
    if (!$fileInfo) {
        echo json_encode(['success' => false, 'message' => 'File not found in database']);
        exit;
    }
    
    $oldPath = $uploadDir . $filename;
    
    // Ambil ekstensi lama
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    // Cek apakah newname sudah mengandung ekstensi
    if (strtolower(pathinfo($newname, PATHINFO_EXTENSION)) !== strtolower($ext)) {
        // Jika tidak, tambahkan ekstensi lama
        $newname .= $ext ? ('.' . $ext) : '';
    }
    
    $newPath = $uploadDir . $newname;
    
    if (!file_exists($oldPath)) {
        echo json_encode(['success' => false, 'message' => 'File not found: ' . $filename]);
        exit;
    }
    
    if (file_exists($newPath)) {
        echo json_encode(['success' => false, 'message' => 'File with new name already exists']);
        exit;
    }
    
    // Rename physical file
    if (rename($oldPath, $newPath)) {
        // Update database record
        $updateStmt = $pdo->prepare("UPDATE files SET original_name = ?, updated_at = CURRENT_TIMESTAMP WHERE filename = ?");
        $updateStmt->execute([$newname, $filename]);
        
        echo json_encode(['success' => true, 'newname' => $newname]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Rename failed']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
