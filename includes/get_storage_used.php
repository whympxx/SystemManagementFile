<?php
header('Content-Type: application/json');
require_once '../config/auth.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$uploadsDir = realpath(__DIR__ . '/../uploads');
$excludeDirs = ['thumbnails']; // Folder yang dikecualikan

function getFolderSize($dir, $excludeDirs = []) {
    $size = 0;
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $filePath = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($filePath)) {
            if (in_array($file, $excludeDirs)) continue;
            $size += getFolderSize($filePath, $excludeDirs);
        } else {
            $size += filesize($filePath);
        }
    }
    return $size;
}

function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

$totalUsed = $uploadsDir ? getFolderSize($uploadsDir, $excludeDirs) : 0;

// Output JSON
echo json_encode([
    'success' => true,
    'storage_used' => $totalUsed,
    'storage_used_formatted' => formatFileSize($totalUsed)
]); 