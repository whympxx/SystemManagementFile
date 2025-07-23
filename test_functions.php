<?php
/**
 * Test Functions Page
 * Test delete dan download functions
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'config/auth.php';

// Check authentication
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Initialize database
initializeDatabase();

$page_title = 'Test Functions - FileManager Pro';
include 'components/header.php';
?>

<!-- Main Content -->
<div class="min-h-screen flex flex-col">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1">
        <h1 class="text-3xl font-bold text-secondary-900 mb-8">
            <i class="fas fa-vial mr-3 text-primary-600"></i>
            Test Functions
        </h1>

        <!-- Test Results -->
        <div id="test-results" class="mb-8"></div>

        <!-- Files List -->
        <div class="bg-white rounded-xl shadow-sm border border-secondary-100 p-6">
            <h2 class="text-xl font-semibold text-secondary-900 mb-4">
                <i class="fas fa-file mr-2 text-primary-600"></i>
                Available Files
            </h2>
            
            <div id="files-list" class="space-y-3">
                <!-- Files will be loaded here -->
            </div>
            
            <div id="loading" class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-2xl text-primary-600 mb-2"></i>
                <p class="text-secondary-500">Loading files...</p>
            </div>
            
            <div id="no-files" class="text-center py-8 hidden">
                <i class="fas fa-folder-open text-4xl text-secondary-300 mb-2"></i>
                <p class="text-secondary-500">No files found</p>
                <a href="pages/upload.php" class="btn-primary mt-4">
                    <i class="fas fa-upload mr-2"></i>
                    Upload File
                </a>
            </div>
        </div>

        <!-- Test Controls -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-secondary-100 p-6 text-center">
                <i class="fas fa-database text-3xl text-blue-600 mb-3"></i>
                <h3 class="font-semibold text-secondary-900 mb-2">Test Database</h3>
                <button onclick="testDatabase()" class="btn-primary w-full">
                    Test Connection
                </button>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-secondary-100 p-6 text-center">
                <i class="fas fa-download text-3xl text-green-600 mb-3"></i>
                <h3 class="font-semibold text-secondary-900 mb-2">Test Download</h3>
                <button onclick="testDownloadFunction()" class="btn-success w-full">
                    Test Download
                </button>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-secondary-100 p-6 text-center">
                <i class="fas fa-trash text-3xl text-red-600 mb-3"></i>
                <h3 class="font-semibold text-secondary-900 mb-2">Test Delete</h3>
                <button onclick="testDeleteFunction()" class="btn-danger w-full">
                    Test Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadFiles();
    
    function loadFiles() {
        const loadingElement = document.getElementById('loading');
        const noFilesElement = document.getElementById('no-files');
        const filesListElement = document.getElementById('files-list');
        
        fetch('includes/get_files.php?action=list')
            .then(response => response.json())
            .then(data => {
                loadingElement.classList.add('hidden');
                
                if (data.success && data.data.files.length > 0) {
                    filesListElement.innerHTML = '';
                    filesListElement.classList.remove('hidden');
                    noFilesElement.classList.add('hidden');
                    
                    data.data.files.forEach(file => {
                        const fileElement = createFileElement(file);
                        filesListElement.appendChild(fileElement);
                    });
                } else {
                    filesListElement.classList.add('hidden');
                    noFilesElement.classList.remove('hidden');
                }
            })
            .catch(error => {
                loadingElement.classList.add('hidden');
                showResult('error', 'Failed to load files: ' + error.message);
            });
    }
    
    function createFileElement(file) {
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between p-4 bg-secondary-50 rounded-lg';
        
        const iconClass = Utils.getFileIcon(file.original_name);
        
        div.innerHTML = `
            <div class="flex items-center space-x-3">
                <i class="${iconClass} text-lg"></i>
                <div>
                    <p class="font-medium text-secondary-900">${file.original_name}</p>
                    <p class="text-sm text-secondary-500">${file.file_size_formatted} • ${file.upload_date_formatted}</p>
                </div>
            </div>
            <div class="flex space-x-2">
                <button onclick="testDownload('${file.filename}')" class="btn-success btn-sm">
                    <i class="fas fa-download"></i>
                    Download
                </button>
                <button onclick="testDelete('${file.filename}')" class="btn-danger btn-sm">
                    <i class="fas fa-trash"></i>
                    Delete
                </button>
            </div>
        `;
        
        return div;
    }
    
    window.testDatabase = function() {
        fetch('includes/get_files.php?action=stats')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResult('success', 'Database connection successful! Found ' + data.data.total_files + ' files.');
                } else {
                    showResult('error', 'Database test failed: ' + data.message);
                }
            })
            .catch(error => {
                showResult('error', 'Database test error: ' + error.message);
            });
    };
    
    window.testDownload = function(filename) {
        console.log('Testing download for:', filename);
        showResult('info', 'Testing download for: ' + filename);
        
        const downloadUrl = `includes/download.php?filename=${encodeURIComponent(filename)}`;
        console.log('Download URL:', downloadUrl);
        
        // Create a temporary link and click it
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.target = '_blank';
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showResult('success', 'Download initiated for: ' + filename);
    };
    
    window.testDelete = function(filename) {
        if (!confirm(`Are you sure you want to delete "${filename}"?`)) {
            return;
        }
        
        console.log('Testing delete for:', filename);
        showResult('info', 'Testing delete for: ' + filename);
        
        fetch('includes/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `filename=${encodeURIComponent(filename)}`
        })
        .then(response => {
            console.log('Delete response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Delete response:', data);
            if (data.success) {
                showResult('success', 'File deleted successfully: ' + filename);
                loadFiles(); // Reload files
            } else {
                showResult('error', 'Delete failed: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            showResult('error', 'Delete error: ' + error.message);
        });
    };
    
    window.testDownloadFunction = function() {
        showResult('info', 'Testing download function...');
        
        // Test if download endpoint is accessible
        fetch('includes/download.php', {
            method: 'GET'
        })
        .then(response => {
            if (response.status === 400) {
                showResult('success', 'Download endpoint is accessible (returns 400 for missing parameters as expected)');
            } else if (response.status === 403) {
                showResult('error', 'Download endpoint returns 403 - Authentication issue');
            } else {
                showResult('info', `Download endpoint returns status: ${response.status}`);
            }
        })
        .catch(error => {
            showResult('error', 'Download test error: ' + error.message);
        });
    };
    
    window.testDeleteFunction = function() {
        showResult('info', 'Testing delete function...');
        
        // Test if delete endpoint is accessible
        fetch('includes/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'filename='
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success && data.message === 'Filename is required') {
                showResult('success', 'Delete endpoint is accessible and validates input correctly');
            } else {
                showResult('warning', 'Delete endpoint response: ' + data.message);
            }
        })
        .catch(error => {
            showResult('error', 'Delete test error: ' + error.message);
        });
    };
    
    function showResult(type, message) {
        const resultsDiv = document.getElementById('test-results');
        const alertClass = type === 'success' ? 'bg-green-50 border-green-200 text-green-800' :
                          type === 'error' ? 'bg-red-50 border-red-200 text-red-800' :
                          type === 'warning' ? 'bg-yellow-50 border-yellow-200 text-yellow-800' :
                          'bg-blue-50 border-blue-200 text-blue-800';
        
        const iconClass = type === 'success' ? 'fa-check-circle' :
                         type === 'error' ? 'fa-exclamation-circle' :
                         type === 'warning' ? 'fa-exclamation-triangle' :
                         'fa-info-circle';
        
        const resultElement = document.createElement('div');
        resultElement.className = `border rounded-lg p-4 mb-3 ${alertClass}`;
        resultElement.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${iconClass} mr-2"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-current hover:opacity-70">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        resultsDiv.appendChild(resultElement);
        
        // Auto-remove after 10 seconds
        setTimeout(() => {
            if (resultElement.parentElement) {
                resultElement.remove();
            }
        }, 10000);
    }
});
</script>

<p class="text-center mt-8">
    <a href="index.php" class="btn-secondary">
        <i class="fas fa-home mr-2"></i>
        Back to Dashboard
    </a>
    <a href="pages/file-manager.php" class="btn-primary ml-3">
        <i class="fas fa-folder mr-2"></i>
        File Manager
    </a>
</p>

<?php include 'components/footer.php'; ?>
