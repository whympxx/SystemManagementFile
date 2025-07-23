<?php
require_once 'config/auth.php';

// Require authentication
requireAuth();

// Get current user
$currentUser = getCurrentUser();

$page_title = 'Dashboard - FileManager Pro';
include 'components/header.php';
?>

<!-- Main Content -->
<div class="min-h-screen flex flex-col">
    <!-- Hero Section -->
    <div class="gradient-bg py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-white mb-4">
                    Selamat Datang di FileManager Pro
                </h1>
                <p class="text-xl text-blue-100 mb-8">
                    Kelola file Anda dengan mudah, cepat, dan aman
                </p>
                <div class="flex justify-center space-x-4">
                    <a href="pages/upload.php" class="bg-white text-primary-600 hover:bg-gray-50 font-semibold py-3 px-6 rounded-lg transition duration-200 ease-in-out">
                        <i class="fas fa-upload mr-2"></i>
                        Upload File
                    </a>
                    <a href="pages/file-manager.php" class="bg-primary-800 hover:bg-primary-900 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 ease-in-out">
                        <i class="fas fa-folder mr-2"></i>
                        Jelajahi File
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Files -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-secondary-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <i class="fas fa-file text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-2xl font-bold text-secondary-900" id="total-files">0</h3>
                        <p class="text-secondary-500">Total File</p>
                    </div>
                </div>
            </div>

            <!-- Storage Used -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-secondary-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <i class="fas fa-hdd text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-2xl font-bold text-secondary-900">0 MB</h3>
                        <p class="text-secondary-500">Storage Digunakan</p>
                    </div>
                </div>
            </div>

            <!-- Recent Uploads -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-secondary-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100">
                        <i class="fas fa-upload text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-2xl font-bold text-secondary-900">0</h3>
                        <p class="text-secondary-500">Upload Hari Ini</p>
                    </div>
                </div>
            </div>

            <!-- File Types -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-secondary-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100">
                        <i class="fas fa-file-archive text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-2xl font-bold text-secondary-900">0</h3>
                        <p class="text-secondary-500">Jenis File</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Files & Quick Actions -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex-1">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Recent Files -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-secondary-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-secondary-900">
                            <i class="fas fa-clock mr-2 text-primary-600"></i>
                            File Terbaru
                        </h2>
                        <a href="pages/file-manager.php" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                            Lihat Semua
                        </a>
                    </div>
                    
                    <!-- Recent Files Container -->
                    <div id="recent-files-container">
                        <!-- Loading State -->
                        <div id="recent-loading" class="text-center py-12">
                            <i class="fas fa-spinner fa-spin text-2xl text-primary-600 mb-2"></i>
                            <p class="text-secondary-500">Loading recent files...</p>
                        </div>
                        
                        <!-- Empty State -->
                        <div id="recent-empty" class="text-center py-12 hidden">
                            <i class="fas fa-folder-open text-6xl text-secondary-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-secondary-900 mb-2">Belum ada file</h3>
                            <p class="text-secondary-500 mb-6">Upload file pertama Anda untuk memulai</p>
                            <a href="pages/upload.php" class="bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 ease-in-out">
                                <i class="fas fa-upload mr-2"></i>
                                Upload File
                            </a>
                        </div>
                        
                        <!-- Recent Files List -->
                        <div id="recent-files" class="space-y-3 hidden"></div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="space-y-6">
                <!-- Upload Zone -->
                <div class="bg-white rounded-xl shadow-sm border border-secondary-100 p-6">
                    <h3 class="text-lg font-semibold text-secondary-900 mb-4">
                        <i class="fas fa-bolt mr-2 text-primary-600"></i>
                        Aksi Cepat
                    </h3>
                    <div class="space-y-3">
                        <a href="pages/upload.php" class="flex items-center p-3 rounded-lg border-2 border-dashed border-primary-200 hover:border-primary-300 hover:bg-primary-50 transition duration-200 ease-in-out group">
                            <div class="p-2 rounded-full bg-primary-100 group-hover:bg-primary-200">
                                <i class="fas fa-upload text-primary-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="font-medium text-secondary-900">Upload File</p>
                                <p class="text-sm text-secondary-500">Tambah file baru</p>
                            </div>
                        </a>
                        
                        <a href="pages/file-manager.php" class="flex items-center p-3 rounded-lg hover:bg-secondary-50 transition duration-200 ease-in-out group">
                            <div class="p-2 rounded-full bg-secondary-100 group-hover:bg-secondary-200">
                                <i class="fas fa-folder text-secondary-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="font-medium text-secondary-900">File Manager</p>
                                <p class="text-sm text-secondary-500">Kelola semua file</p>
                            </div>
                        </a>
                        
                        <a href="pages/file-manager.php" class="flex items-center p-3 rounded-lg hover:bg-secondary-50 transition duration-200 ease-in-out group">
                            <div class="p-2 rounded-full bg-secondary-100 group-hover:bg-secondary-200">
                                <i class="fas fa-search text-secondary-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="font-medium text-secondary-900">Cari File</p>
                                <p class="text-sm text-secondary-500">Temukan file cepat</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Storage Info -->
                <div class="bg-white rounded-xl shadow-sm border border-secondary-100 p-6">
                    <h3 class="text-lg font-semibold text-secondary-900 mb-4">
                        <i class="fas fa-chart-pie mr-2 text-primary-600"></i>
                        Storage
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary-600">Digunakan</span>
                            <span class="font-medium text-secondary-900" id="storage-used">0 MB dari 1 GB</span>
                        </div>
                        <div class="w-full bg-secondary-200 rounded-full h-2">
                            <div class="bg-primary-600 h-2 rounded-full" id="storage-bar" style="width: 0%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-secondary-500">
                            <span id="storage-percent">0%</span>
                            <span>100%</span>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl border border-blue-200 p-6">
                    <h3 class="text-lg font-semibold text-secondary-900 mb-2">
                        <i class="fas fa-lightbulb mr-2 text-yellow-500"></i>
                        Tips
                    </h3>
                    <p class="text-sm text-secondary-600 mb-3">
                        Gunakan drag & drop untuk upload file dengan mudah!
                    </p>
                    <div class="flex items-center text-xs text-blue-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span>Maksimal ukuran file: 10MB</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load dashboard data
    loadDashboardData();
    
    function loadDashboardData() {
        // Load stats
        fetch('includes/get_files.php?action=stats')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateStatCards(data.data);
                }
            })
            .catch(error => console.error('Error loading stats:', error));
            
        // Load recent files
        fetch('includes/get_files.php?action=list&limit=5&sort=upload_date&order=DESC')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayRecentFiles(data.data.files);
                }
            })
            .catch(error => console.error('Error loading recent files:', error));
    }
    
    function updateStatCards(stats) {
        // Update total files
        const totalFilesElement = document.getElementById('total-files');
        if (totalFilesElement) {
            totalFilesElement.textContent = stats.total_files;
        }
        
        // Update storage info (card atas)
        const statCards = document.querySelectorAll('.text-2xl.font-bold.text-secondary-900');
        if (statCards[1]) statCards[1].textContent = stats.total_size_formatted;
        if (statCards[2]) statCards[2].textContent = stats.today_uploads;
        if (statCards[3]) statCards[3].textContent = stats.files_by_type ? stats.files_by_type.length : 0;

        // Update storage info (card bawah)
        const storageUsedElement = document.getElementById('storage-used');
        const storageBarElement = document.getElementById('storage-bar');
        const storagePercentElement = document.getElementById('storage-percent');
        if (storageUsedElement) storageUsedElement.textContent = stats.total_size_formatted + ' dari 1 GB';
        if (storageBarElement) storageBarElement.style.width = stats.storage_percent + '%';
        if (storagePercentElement) storagePercentElement.textContent = stats.storage_percent + '%';
    }
    
    function displayRecentFiles(files) {
        const loadingElement = document.getElementById('recent-loading');
        const emptyElement = document.getElementById('recent-empty');
        const filesElement = document.getElementById('recent-files');
        
        // Hide loading
        loadingElement.classList.add('hidden');
        
        if (files && files.length > 0) {
            // Show files
            emptyElement.classList.add('hidden');
            filesElement.classList.remove('hidden');
            
            // Clear existing files
            filesElement.innerHTML = '';
            
            // Add each file
            files.forEach(file => {
                const fileElement = createRecentFileElement(file);
                filesElement.appendChild(fileElement);
            });
        } else {
            // Show empty state
            filesElement.classList.add('hidden');
            emptyElement.classList.remove('hidden');
        }
    }
    
    // Add missing escapeHtml function
    function escapeHtml(text) {
        if (typeof text !== 'string') return text;
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    function createRecentFileElement(file) {
        const fileElement = document.createElement('div');
        fileElement.className = 'flex items-center p-3 rounded-lg hover:bg-secondary-50 transition duration-200';
        
        // Perbaikan: gunakan icon_class dari backend jika ada
        const iconClass = file.icon_class || (typeof Utils !== 'undefined' ? Utils.getFileIcon(file.original_name) : 'fas fa-file text-gray-400');
        
        fileElement.innerHTML = `
            <div class="flex-shrink-0">
                <i class="${iconClass} text-lg"></i>
            </div>
            <div class="ml-3 flex-1 min-w-0">
                <p class="text-sm font-medium text-secondary-900 truncate">${escapeHtml(file.original_name)}</p>
                <p class="text-xs text-secondary-500">${escapeHtml(file.file_size_formatted)} • ${escapeHtml(file.upload_date_formatted)}</p>
            </div>
            <div class="flex-shrink-0 flex space-x-1">
                <button class="p-1 text-secondary-400 hover:text-green-600 rounded" title="Download" onclick="downloadFile('${escapeHtml(file.filename)}')">
                    <i class="fas fa-download text-xs"></i>
                </button>
                <button class="p-1 text-secondary-400 hover:text-red-600 rounded" title="Delete" onclick="deleteFile('${escapeHtml(file.filename)}')">
                    <i class="fas fa-trash text-xs"></i>
                </button>
            </div>
        `;
        
        return fileElement;
    }
    
    // Utility functions for file operations
    window.downloadFile = function(filename) {
        console.log('Dashboard - Downloading file:', filename);
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
    };
    
    window.deleteFile = function(filename) {
        console.log('Dashboard - Deleting file:', filename);
        
        if (confirm(`Apakah Anda yakin ingin menghapus file "${filename}"?`)) {
            // Show loading indicator
            Utils.showNotification('Menghapus file...', 'info');
            
            fetch('includes/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `filename=${encodeURIComponent(filename)}`
            })
            .then(response => {
                console.log('Dashboard - Delete response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Dashboard - Delete response:', data);
                if (data.success) {
                    Utils.showNotification('File berhasil dihapus', 'success');
                    loadDashboardData(); // Reload data
                } else {
                    Utils.showNotification(data.message || 'Gagal menghapus file', 'error');
                }
            })
            .catch(error => {
                console.error('Dashboard - Delete error:', error);
                Utils.showNotification('Network error: ' + error.message, 'error');
            });
        }
    };
});
</script>

<?php include 'components/footer.php'; ?>
