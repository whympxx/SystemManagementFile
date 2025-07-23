<?php
/**
 * Database Initialization Script
 * Script untuk memastikan database dan tabel sudah dibuat dengan benar
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

echo "<h1>Database Initialization</h1>";

try {
    // Test database connection first
    echo "<h2>Testing Database Connection</h2>";
    $pdo = getDBConnection();
    
    if (!$pdo) {
        echo "<p style='color: red;'>❌ Database connection failed!</p>";
        echo "<p>Please check your database configuration in config/database.php</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Tambah tabel folders jika belum ada
    $pdo->exec("CREATE TABLE IF NOT EXISTS folders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        parent_id INT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (parent_id) REFERENCES folders(id) ON DELETE CASCADE
    )");
    // Tambah kolom folder_id ke tabel files jika belum ada
    $cols = $pdo->query("SHOW COLUMNS FROM files LIKE 'folder_id'")->fetch();
    if (!$cols) {
        $pdo->exec("ALTER TABLE files ADD COLUMN folder_id INT DEFAULT NULL, ADD FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE SET NULL");
    }
    // Tambah kolom password ke tabel folders jika belum ada
    $cols = $pdo->query("SHOW COLUMNS FROM folders LIKE 'password'")->fetch();
    if (!$cols) {
        $pdo->exec("ALTER TABLE folders ADD COLUMN password VARCHAR(255) DEFAULT NULL");
    }
    // Tambah kolom password ke tabel files jika belum ada
    $cols = $pdo->query("SHOW COLUMNS FROM files LIKE 'password'")->fetch();
    if (!$cols) {
        $pdo->exec("ALTER TABLE files ADD COLUMN password VARCHAR(255) DEFAULT NULL");
    }

    // Initialize database (create tables)
    echo "<h2>Initializing Database Tables</h2>";
    $result = initializeDatabase();
    
    if ($result) {
        echo "<p style='color: green;'>✅ Database tables initialized successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to initialize database tables</p>";
    }
    
    // Check if tables exist
    echo "<h2>Checking Tables</h2>";
    $tables = ['files', 'users', 'file_shares'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("DESCRIBE $table");
            echo "<p style='color: green;'>✅ Table '$table' exists</p>";
            
            // Show table structure
            echo "<details><summary>Show structure for '$table'</summary>";
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table></details>";
            
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Table '$table' does not exist or error: " . $e->getMessage() . "</p>";
        }
    }
    
    // Check file count
    echo "<h2>Database Statistics</h2>";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM files");
        $result = $stmt->fetch();
        echo "<p>Files in database: <strong>" . $result['count'] . "</strong></p>";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "<p>Users in database: <strong>" . $result['count'] . "</strong></p>";
        
        // Show recent files if any
        $stmt = $pdo->query("SELECT * FROM files ORDER BY upload_date DESC LIMIT 5");
        $files = $stmt->fetchAll();
        
        if ($files) {
            echo "<h3>Recent Files</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Original Name</th><th>Size</th><th>Type</th><th>Upload Date</th></tr>";
            foreach ($files as $file) {
                echo "<tr>";
                echo "<td>" . $file['id'] . "</td>";
                echo "<td>" . htmlspecialchars($file['original_name']) . "</td>";
                echo "<td>" . number_format($file['file_size']) . " bytes</td>";
                echo "<td>" . $file['file_type'] . "</td>";
                echo "<td>" . $file['upload_date'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error checking database statistics: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>Test Insert</h2>";
    echo "<p>Testing if we can insert data into the files table...</p>";
    
    try {
        $testStmt = $pdo->prepare("
            INSERT INTO files (filename, original_name, file_path, file_size, file_type, mime_type) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $testData = [
            'test_' . time() . '.txt',
            'test_file.txt',
            'uploads/test_' . time() . '.txt',
            100,
            'txt',
            'text/plain'
        ];
        
        $testStmt->execute($testData);
        $testId = $pdo->lastInsertId();
        
        echo "<p style='color: green;'>✅ Test insert successful. Test file ID: $testId</p>";
        
        // Clean up test data
        $cleanupStmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
        $cleanupStmt->execute([$testId]);
        echo "<p>Test data cleaned up.</p>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Test insert failed: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='test_upload.php'>Go to Upload Test</a> | <a href='debug_upload.php'>Go to Debug Page</a> | <a href='index.php'>Back to Dashboard</a></p>";
?>
