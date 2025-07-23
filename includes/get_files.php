<?php
/**
 * Get Files API Endpoint
 * Returns list of files from database
 */

header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/auth.php';
require_once 'security.php';

// Check authentication for sensitive operations
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Check CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!Security::verifyCSRFToken($csrfToken)) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token']);
        exit;
    }
}

/**
 * Get files from database
 */
function getFiles($search = '', $type = '', $sort = 'upload_date', $order = 'DESC', $limit = 50, $offset = 0, $folder_id = null) {
    try {
        $pdo = getDBConnection();
        if (!$pdo) {
            throw new Exception('Database connection failed');
        }
        
        // Mapping kategori ke ekstensi file
        $typeMap = [
            'image' => ['jpg','jpeg','png','gif','bmp','svg','webp'],
            'document' => ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt'],
            'video' => ['mp4','avi','mov','wmv','flv','mkv'],
            'audio' => ['mp3','wav','flac','ogg','aac'],
            'archive' => ['zip','rar','7z','tar','gz']
        ];
        
        // Build query
        $whereConditions = ['is_deleted = 0'];
        $params = [];
        
        // Add search condition
        if (!empty($search)) {
            $whereConditions[] = '(original_name LIKE ? OR filename LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        // Add type filter (kategori atau ekstensi)
        if (!empty($type)) {
            if (array_key_exists($type, $typeMap)) {
                $placeholders = implode(',', array_fill(0, count($typeMap[$type]), '?'));
                $whereConditions[] = 'file_type IN (' . $placeholders . ')';
                foreach ($typeMap[$type] as $ext) {
                    $params[] = $ext;
                }
            } else {
                $whereConditions[] = 'file_type = ?';
                $params[] = $type;
            }
        }

        // Add folder filter
        if ($folder_id !== null) {
            $whereConditions[] = 'folder_id ' . ($folder_id === 0 ? 'IS NULL' : '= ?');
            if ($folder_id !== 0) $params[] = $folder_id;
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        // Validate sort column
        $validSortColumns = ['original_name', 'file_size', 'file_type', 'upload_date'];
        if (!in_array($sort, $validSortColumns)) {
            $sort = 'upload_date';
        }
        
        // Validate sort order
        $order = strtoupper($order);
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM files WHERE {$whereClause}";
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($params);
        $totalFiles = $countStmt->fetch()['total'];
        
        // Get files
        $query = "
            SELECT 
                id,
                filename,
                original_name,
                file_path,
                file_size,
                file_type,
                mime_type,
                upload_date,
                created_at,
                updated_at,
                password
            FROM files 
            WHERE {$whereClause}
            ORDER BY {$sort} {$order}
            LIMIT ? OFFSET ?
        ";
        
        $params[] = (int)$limit;
        $params[] = (int)$offset;
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $files = $stmt->fetchAll();
        
        // Format files data
        $formattedFiles = array_map(function($file) {
            return [
                'id' => (int)$file['id'],
                'filename' => $file['filename'],
                'original_name' => $file['original_name'],
                'file_path' => $file['file_path'],
                'file_size' => (int)$file['file_size'],
                'file_size_formatted' => formatFileSize($file['file_size']),
                'file_type' => $file['file_type'],
                'mime_type' => $file['mime_type'],
                'upload_date' => $file['upload_date'],
                'upload_date_formatted' => date('M j, Y g:i A', strtotime($file['upload_date'])),
                'created_at' => $file['created_at'],
                'updated_at' => $file['updated_at'],
                'icon_class' => getFileIconClass($file['file_type']),
                'download_url' => '../includes/download.php?id=' . $file['id'],
                'is_protected' => !empty($file['password'])
            ];
        }, $files);
        
        return [
            'success' => true,
            'data' => [
                'files' => $formattedFiles,
                'total' => (int)$totalFiles,
                'limit' => (int)$limit,
                'offset' => (int)$offset,
                'has_more' => ($offset + $limit) < $totalFiles
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
 * Get file statistics
 */
function getFileStats() {
    try {
        $pdo = getDBConnection();
        if (!$pdo) {
            throw new Exception('Database connection failed');
        }
        
        // Get total files
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM files WHERE is_deleted = 0");
        $totalFiles = $stmt->fetch()['total'];
        
        // Get total size
        $stmt = $pdo->query("SELECT COALESCE(SUM(file_size), 0) as total_size FROM files WHERE is_deleted = 0");
        $totalSize = $stmt->fetch()['total_size'];
        
        // Get files by type
        $stmt = $pdo->query("
            SELECT file_type, COUNT(*) as count 
            FROM files 
            WHERE is_deleted = 0 
            GROUP BY file_type 
            ORDER BY count DESC
        ");
        $filesByType = $stmt->fetchAll();
        
        // Get recent uploads (today)
        $stmt = $pdo->query("
            SELECT COUNT(*) as today_uploads 
            FROM files 
            WHERE is_deleted = 0 
            AND DATE(upload_date) = CURDATE()
        ");
        $todayUploads = $stmt->fetch()['today_uploads'];
        
        // Tambahan: Get total folders
        $stmt = $pdo->query("SELECT COUNT(*) as total_folders FROM folders");
        $totalFolders = $stmt->fetch()['total_folders'];
        
        // Tambahan: total_favorites (sementara 0, karena tidak ada field di tabel)
        $totalFavorites = 0;

        // Tambahan: storage_percent
        $maxStorage = 1024 * 1024 * 1024; // 1 GB dalam bytes
        $storagePercent = $maxStorage > 0 ? round(($totalSize / $maxStorage) * 100, 2) : 0;
        
        return [
            'success' => true,
            'data' => [
                'total_files' => (int)$totalFiles,
                'total_size' => (int)$totalSize,
                'total_size_formatted' => formatFileSize($totalSize),
                'today_uploads' => (int)$todayUploads,
                'files_by_type' => $filesByType,
                'total_folders' => (int)$totalFolders,
                'total_favorites' => (int)$totalFavorites,
                'storage_percent' => $storagePercent
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
 * Tambah: ambil daftar folder
 */
function getFolders($parent_id = null) {
    try {
        $pdo = getDBConnection();
        if (!$pdo) throw new Exception('Database connection failed');
        $query = 'SELECT * FROM folders';
        $params = [];
        if ($parent_id !== null) {
            $query .= ' WHERE parent_id = ?';
            $params[] = $parent_id;
        } else {
            $query .= ' WHERE parent_id IS NULL';
        }
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $folders = $stmt->fetchAll();
        // Tambahkan is_protected agar frontend tahu folder diproteksi
        $folders = array_map(function($f) {
            $f['is_protected'] = !empty($f['password']);
            return $f;
        }, $folders);
        return [ 'success' => true, 'data' => $folders ];
    } catch (Exception $e) {
        return [ 'success' => false, 'message' => $e->getMessage() ];
    }
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes) / log($k));
    
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

/**
 * Get file icon class based on file type
 */
function getFileIconClass($fileType) {
    $iconMap = [
        // Documents
        'pdf' => 'fas fa-file-pdf text-red-500',
        'doc' => 'fas fa-file-word text-blue-600',
        'docx' => 'fas fa-file-word text-blue-600',
        'xls' => 'fas fa-file-excel text-green-600',
        'xlsx' => 'fas fa-file-excel text-green-600',
        'ppt' => 'fas fa-file-powerpoint text-red-600',
        'pptx' => 'fas fa-file-powerpoint text-red-600',
        'txt' => 'fas fa-file-alt text-gray-500',
        
        // Images
        'jpg' => 'fas fa-file-image text-purple-500',
        'jpeg' => 'fas fa-file-image text-purple-500',
        'png' => 'fas fa-file-image text-purple-500',
        'gif' => 'fas fa-file-image text-purple-500',
        'bmp' => 'fas fa-file-image text-purple-500',
        'svg' => 'fas fa-file-image text-purple-500',
        'webp' => 'fas fa-file-image text-purple-500',
        
        // Videos
        'mp4' => 'fas fa-file-video text-red-500',
        'avi' => 'fas fa-file-video text-red-500',
        'mov' => 'fas fa-file-video text-red-500',
        'wmv' => 'fas fa-file-video text-red-500',
        'flv' => 'fas fa-file-video text-red-500',
        'mkv' => 'fas fa-file-video text-red-500',
        
        // Audio
        'mp3' => 'fas fa-file-audio text-green-500',
        'wav' => 'fas fa-file-audio text-green-500',
        'flac' => 'fas fa-file-audio text-green-500',
        'ogg' => 'fas fa-file-audio text-green-500',
        'aac' => 'fas fa-file-audio text-green-500',
        
        // Archives
        'zip' => 'fas fa-file-archive text-yellow-600',
        'rar' => 'fas fa-file-archive text-yellow-600',
        '7z' => 'fas fa-file-archive text-yellow-600',
        'tar' => 'fas fa-file-archive text-yellow-600',
        'gz' => 'fas fa-file-archive text-yellow-600',
        
        // Code
        'html' => 'fas fa-file-code text-orange-500',
        'css' => 'fas fa-file-code text-blue-500',
        'js' => 'fas fa-file-code text-yellow-500',
        'php' => 'fas fa-file-code text-indigo-500',
        'py' => 'fas fa-file-code text-green-600',
        'java' => 'fas fa-file-code text-red-600',
        'cpp' => 'fas fa-file-code text-blue-700',
        'c' => 'fas fa-file-code text-blue-700',
        'sql' => 'fas fa-file-code text-purple-600',
        'json' => 'fas fa-file-code text-yellow-600',
        'xml' => 'fas fa-file-code text-orange-600',
    ];
    
    return $iconMap[$fileType] ?? 'fas fa-file text-gray-400';
}

function createFolder($name, $parent_id = null) {
    try {
        $pdo = getDBConnection();
        if (!$pdo) throw new Exception('Database connection failed');
        $stmt = $pdo->prepare('INSERT INTO folders (name, parent_id) VALUES (?, ?)');
        $stmt->execute([$name, $parent_id]);
        $id = $pdo->lastInsertId();
        return [ 'success' => true, 'data' => [ 'id' => $id, 'name' => $name, 'parent_id' => $parent_id ] ];
    } catch (Exception $e) {
        return [ 'success' => false, 'message' => $e->getMessage() ];
    }
}

function moveFile($filename, $folder_id) {
    try {
        $pdo = getDBConnection();
        if (!$pdo) throw new Exception('Database connection failed');
        // Cek file
        $stmt = $pdo->prepare('SELECT * FROM files WHERE filename = ? AND is_deleted = 0');
        $stmt->execute([$filename]);
        $file = $stmt->fetch();
        if (!$file) throw new Exception('File not found');
        // Update folder_id
        $stmt = $pdo->prepare('UPDATE files SET folder_id = ? WHERE filename = ?');
        $stmt->execute([$folder_id ?: null, $filename]);
        return [ 'success' => true ];
    } catch (Exception $e) {
        return [ 'success' => false, 'message' => $e->getMessage() ];
    }
}

function deleteFolder($id) {
    try {
        $pdo = getDBConnection();
        if (!$pdo) throw new Exception('Database connection failed');
        // Set folder_id file yang ada di folder ini menjadi NULL
        $stmt = $pdo->prepare('UPDATE files SET folder_id = NULL WHERE folder_id = ?');
        $stmt->execute([$id]);
        // Hapus subfolder (cascade by DB jika foreign key ON DELETE CASCADE)
        // Hapus folder
        $stmt = $pdo->prepare('DELETE FROM folders WHERE id = ?');
        $stmt->execute([$id]);
        return [ 'success' => true ];
    } catch (Exception $e) {
        return [ 'success' => false, 'message' => $e->getMessage() ];
    }
}

function setFolderPassword($id, $password) {
    try {
        $pdo = getDBConnection();
        if (!$pdo) throw new Exception('Database connection failed');
        $hash = $password ? password_hash($password, PASSWORD_DEFAULT) : null;
        $stmt = $pdo->prepare('UPDATE folders SET password = ? WHERE id = ?');
        $stmt->execute([$hash, $id]);
        return [ 'success' => true ];
    } catch (Exception $e) {
        return [ 'success' => false, 'message' => $e->getMessage() ];
    }
}
function checkFolderPassword($id, $password) {
    try {
        $pdo = getDBConnection();
        if (!$pdo) throw new Exception('Database connection failed');
        $stmt = $pdo->prepare('SELECT password FROM folders WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) throw new Exception('Folder not found');
        if (!$row['password']) return [ 'success' => true ]; // Tidak ada password
        if (password_verify($password, $row['password'])) return [ 'success' => true ];
        return [ 'success' => false, 'message' => 'Password salah' ];
    } catch (Exception $e) {
        return [ 'success' => false, 'message' => $e->getMessage() ];
    }
}

function getFolder($id) {
    try {
        $pdo = getDBConnection();
        if (!$pdo) throw new Exception('Database connection failed');
        $stmt = $pdo->prepare('SELECT * FROM folders WHERE id = ?');
        $stmt->execute([$id]);
        $folder = $stmt->fetch();
        if (!$folder) throw new Exception('Folder not found');
        $folder['is_protected'] = !empty($folder['password']);
        return [ 'success' => true, 'data' => $folder ];
    } catch (Exception $e) {
        return [ 'success' => false, 'message' => $e->getMessage() ];
    }
}

function renameFolder($id, $name) {
    try {
        $pdo = getDBConnection();
        if (!$pdo) throw new Exception('Database connection failed');
        $stmt = $pdo->prepare('UPDATE folders SET name = ? WHERE id = ?');
        $stmt->execute([$name, $id]);
        return [ 'success' => true ];
    } catch (Exception $e) {
        return [ 'success' => false, 'message' => $e->getMessage() ];
    }
}

function setFilePassword($filename, $password) {
    try {
        $pdo = getDBConnection();
        if (!$pdo) throw new Exception('Database connection failed');
        $hash = $password ? password_hash($password, PASSWORD_DEFAULT) : null;
        $stmt = $pdo->prepare('UPDATE files SET password = ? WHERE filename = ?');
        $stmt->execute([$hash, $filename]);
        return [ 'success' => true ];
    } catch (Exception $e) {
        return [ 'success' => false, 'message' => $e->getMessage() ];
    }
}
function checkFilePassword($filename, $password) {
    try {
        $pdo = getDBConnection();
        if (!$pdo) throw new Exception('Database connection failed');
        $stmt = $pdo->prepare('SELECT password FROM files WHERE filename = ?');
        $stmt->execute([$filename]);
        $row = $stmt->fetch();
        if (!$row) throw new Exception('File not found');
        if (!$row['password']) return [ 'success' => true ]; // Tidak ada password
        if (password_verify($password, $row['password'])) return [ 'success' => true ];
        return [ 'success' => false, 'message' => 'Password salah' ];
    } catch (Exception $e) {
        return [ 'success' => false, 'message' => $e->getMessage() ];
    }
}

// Handle the request
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        $search = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? '';
        $sort = $_GET['sort'] ?? 'upload_date';
        $order = $_GET['order'] ?? 'DESC';
        $limit = min((int)($_GET['limit'] ?? 50), 100); // Max 100 files per request
        $offset = (int)($_GET['offset'] ?? 0);
        $folder_id = isset($_GET['folder_id']) ? (int)$_GET['folder_id'] : null;
        
        $response = getFiles($search, $type, $sort, $order, $limit, $offset, $folder_id);
        break;
        
    case 'folders':
        $parent_id = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : null;
        $response = getFolders($parent_id);
        break;
        
    case 'stats':
        $response = getFileStats();
        break;
        
    case 'create_folder':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
            $response = createFolder($name, $parent_id);
        } else {
            $response = [ 'success' => false, 'message' => 'Invalid request method' ];
        }
        break;
        
    case 'move_file':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $filename = $_POST['filename'] ?? '';
            $folder_id = isset($_POST['folder_id']) && $_POST['folder_id'] !== '' ? (int)$_POST['folder_id'] : null;
            $response = moveFile($filename, $folder_id);
        } else {
            $response = [ 'success' => false, 'message' => 'Invalid request method' ];
        }
        break;
        
    case 'delete_folder':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $response = $id ? deleteFolder($id) : [ 'success' => false, 'message' => 'Missing folder id' ];
        } else {
            $response = [ 'success' => false, 'message' => 'Invalid request method' ];
        }
        break;
        
    case 'set_folder_password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $password = $_POST['password'] ?? '';
            $response = $id ? setFolderPassword($id, $password) : [ 'success' => false, 'message' => 'Missing folder id' ];
        } else {
            $response = [ 'success' => false, 'message' => 'Invalid request method' ];
        }
        break;
    case 'check_folder_password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $password = $_POST['password'] ?? '';
            $response = $id ? checkFolderPassword($id, $password) : [ 'success' => false, 'message' => 'Missing folder id' ];
        } else {
            $response = [ 'success' => false, 'message' => 'Invalid request method' ];
        }
        break;
        
    case 'get_folder':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $response = $id ? getFolder($id) : [ 'success' => false, 'message' => 'Missing folder id' ];
        break;
        
    case 'rename_folder':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $name = $_POST['name'] ?? '';
            $response = ($id && $name) ? renameFolder($id, $name) : [ 'success' => false, 'message' => 'Missing folder id or name' ];
        } else {
            $response = [ 'success' => false, 'message' => 'Invalid request method' ];
        }
        break;
        
    case 'set_file_password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $filename = $_POST['filename'] ?? '';
            $password = $_POST['password'] ?? '';
            $response = $filename ? setFilePassword($filename, $password) : [ 'success' => false, 'message' => 'Missing filename' ];
        } else {
            $response = [ 'success' => false, 'message' => 'Invalid request method' ];
        }
        break;
    case 'check_file_password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $filename = $_POST['filename'] ?? '';
            $password = $_POST['password'] ?? '';
            $response = $filename ? checkFilePassword($filename, $password) : [ 'success' => false, 'message' => 'Missing filename' ];
        } else {
            $response = [ 'success' => false, 'message' => 'Invalid request method' ];
        }
        break;
        
    default:
        $response = [
            'success' => false,
            'message' => 'Invalid action'
        ];
}

echo json_encode($response);
?>
