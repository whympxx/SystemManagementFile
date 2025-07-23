<?php
require_once '../config/auth.php';

// Require authentication
requireAuth('../login.php');

// Get current user
$currentUser = getCurrentUser();

$page_title = 'Upload File - FileManager Pro';
include '../components/header.php';
?>

<!-- Debug session recent uploads -->
<?php
if (isset($_SESSION['hidden_recent_uploads'])) {
    echo '<!-- hidden_recent_uploads: ' . htmlspecialchars(json_encode($_SESSION['hidden_recent_uploads'])) . ' -->';
}
?>

<!-- Main Content -->
<div class="min-h-screen flex flex-col">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-secondary-900">
                        <i class="fas fa-upload mr-3 text-primary-600"></i>
                        Upload File
                    </h1>
                    <p class="text-secondary-600 mt-2">
                        Upload file dengan drag & drop atau pilih file secara manual
                    </p>
                </div>
                <a href="../index.php" class="btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Dashboard
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Upload Zone -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-secondary-100 p-6">
                    <!-- Drag & Drop Zone -->
                    <div class="drop-zone border-2 border-dashed border-secondary-300 rounded-xl p-12 text-center hover:border-primary-400 hover:bg-primary-50 transition duration-200 ease-in-out cursor-pointer">
                        <div class="space-y-6">
                            <div class="text-6xl text-secondary-300">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-secondary-900 mb-2">
                                    Drag & Drop File Anda Di Sini
                                </h3>
                                <p class="text-secondary-500 mb-4">
                                    Atau klik untuk memilih file dari komputer Anda
                                </p>
                                <input type="file" id="file-input" class="hidden" multiple>
                                <button onclick="document.getElementById('file-input').click()" class="btn-primary">
                                    <i class="fas fa-folder-open mr-2"></i>
                                    Pilih File
                                </button>
                            </div>
                            <div class="flex items-center justify-center space-x-4 text-sm text-secondary-500">
                                <span class="flex items-center">
                                    <i class="fas fa-check-circle mr-1 text-green-500"></i>
                                    Max 10MB
                                </span>
                                <span class="flex items-center">
                                    <i class="fas fa-check-circle mr-1 text-green-500"></i>
                                    Multiple files
                                </span>
                                <span class="flex items-center">
                                    <i class="fas fa-check-circle mr-1 text-green-500"></i>
                                    All formats
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Progress -->
                    <div id="upload-progress" class="hidden mt-6">
                        <div class="bg-secondary-50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-secondary-700">Mengupload...</span>
                                <span class="text-sm text-secondary-500" id="progress-text">0%</span>
                            </div>
                            <div class="w-full bg-secondary-200 rounded-full h-2">
                                <div class="bg-primary-600 h-2 rounded-full transition-all duration-300 upload-progress" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- File Queue -->
                    <div id="file-queue" class="mt-6 space-y-3 hidden">
                        <h4 class="text-lg font-semibold text-secondary-900 mb-3">
                            <i class="fas fa-list mr-2 text-primary-600"></i>
                            File Queue
                        </h4>
                        <div id="queue-list" class="space-y-2">
                            <!-- Files will be added here dynamically -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Info & Settings -->
            <div class="space-y-6">
                <!-- Upload Guidelines -->
                <div class="bg-white rounded-xl shadow-sm border border-secondary-100 p-6">
                    <h3 class="text-lg font-semibold text-secondary-900 mb-4">
                        <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                        Upload Guidelines
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-500 mt-0.5"></i>
                            <span class="text-secondary-600">Maximum file size: 10MB</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-500 mt-0.5"></i>
                            <span class="text-secondary-600">Supported formats: All file types</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-500 mt-0.5"></i>
                            <span class="text-secondary-600">Multiple files allowed</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-500 mt-0.5"></i>
                            <span class="text-secondary-600">Drag & drop supported</span>
                        </div>
                    </div>
                </div>

                <!-- Recent Uploads -->
                <div class="bg-white rounded-xl shadow-sm border border-secondary-100 p-6">
                    <h3 class="text-lg font-semibold text-secondary-900 mb-4">
                        <i class="fas fa-clock mr-2 text-purple-500"></i>
                        Recent Uploads
                    </h3>
                    <div class="space-y-3" id="recent-uploads-list">
                        <?php
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }
                        $uploadDir = '../uploads/';
                        $allFiles = array_diff(scandir($uploadDir, SCANDIR_SORT_DESCENDING), array('.', '..', 'profile_photos', 'thumbnails'));
                        $hiddenFiles = isset($_SESSION['hidden_recent_uploads']) ? $_SESSION['hidden_recent_uploads'] : array();
                        $visibleFiles = array_values(array_diff($allFiles, $hiddenFiles));
                        $recentFiles = array_slice($visibleFiles, 0, 5);

                        if (count($recentFiles) === 0) {
                            echo '<div class="text-center py-6">
                                <i class="fas fa-upload text-3xl text-secondary-300 mb-2"></i>
                                <p class="text-secondary-500 text-sm">No recent uploads</p>
                            </div>';
                        } else {
                            foreach ($recentFiles as $file) {
                                $fileUrl = $uploadDir . $file;
                                $fileName = htmlspecialchars($file);
                                $fileId = 'recent-upload-' . md5($file);
                                echo '<div class="flex items-center space-x-3 p-2 bg-secondary-50 rounded-lg" id="' . $fileId . '">
                                        <i class="fas fa-file text-secondary-400"></i>
                                        <a href="' . $fileUrl . '" class="text-secondary-800 hover:underline" target="_blank">' . $fileName . '</a>
                                        <button class="ml-auto text-red-500 hover:text-red-700 delete-recent-upload" data-file="' . htmlspecialchars($file, ENT_QUOTES) . '" title="Hapus dari recent uploads">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>';
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-gradient-to-r from-primary-50 to-blue-50 rounded-xl border border-primary-200 p-6">
                    <h3 class="text-lg font-semibold text-secondary-900 mb-3">
                        <i class="fas fa-zap mr-2 text-primary-600"></i>
                        Quick Actions
                    </h3>
                    <div class="space-y-2">
                        <a href="file-manager.php" class="flex items-center text-sm text-primary-700 hover:text-primary-800 transition duration-150">
                            <i class="fas fa-folder mr-2"></i>
                            View All Files
                        </a>
                        <a href="../index.php" class="flex items-center text-sm text-primary-700 hover:text-primary-800 transition duration-150">
                            <i class="fas fa-home mr-2"></i>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.querySelector('.drop-zone');
    const fileInput = document.getElementById('file-input');
    const uploadProgress = document.getElementById('upload-progress');
    const progressBar = document.querySelector('.upload-progress');
    const progressText = document.getElementById('progress-text');
    const fileQueue = document.getElementById('file-queue');
    const queueList = document.getElementById('queue-list');

    let selectedFiles = [];

    // Handle file input change
    fileInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });

    // Handle drag and drop
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('border-primary-500', 'bg-primary-50');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-primary-500', 'bg-primary-50');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-primary-500', 'bg-primary-50');
        handleFiles(e.dataTransfer.files);
    });

    function handleFiles(files) {
        selectedFiles = Array.from(files);
        displayFileQueue();
        uploadFiles();
    }

    function displayFileQueue() {
        if (selectedFiles.length === 0) {
            fileQueue.classList.add('hidden');
            return;
        }

        fileQueue.classList.remove('hidden');
        queueList.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'flex items-center justify-between p-3 bg-secondary-50 rounded-lg';
            fileItem.innerHTML = `
                <div class="flex items-center space-x-3">
                    <i class="${Utils.getFileIcon(file.name)} text-lg"></i>
                    <div>
                        <p class="font-medium text-secondary-900 text-sm">${file.name}</p>
                        <p class="text-xs text-secondary-500">${Utils.formatFileSize(file.size)}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-secondary-500" id="file-status-${index}">Pending</span>
                    <button onclick="removeFile(${index})" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            queueList.appendChild(fileItem);
        });
    }

    function uploadFiles() {
        if (selectedFiles.length === 0) return;

        uploadProgress.classList.remove('hidden');
        
        selectedFiles.forEach((file, index) => {
            const formData = new FormData();
            formData.append('file', file);

            // Update file status
            const statusElement = document.getElementById(`file-status-${index}`);
            if (statusElement) {
                statusElement.textContent = 'Uploading...';
                statusElement.className = 'text-xs text-blue-600';
            }

            FileManager.uploadFile(
                formData,
                (progress) => {
                    progressBar.style.width = progress + '%';
                    progressText.textContent = Math.round(progress) + '%';
                },
                (response) => {
                    if (statusElement) {
                        statusElement.textContent = 'Completed';
                        statusElement.className = 'text-xs text-green-600';
                    }
                    Utils.showNotification(`File "${file.name}" uploaded successfully`, 'success');
                    
                    // If all files are uploaded, hide progress and offer navigation
                    if (index === selectedFiles.length - 1) {
                        setTimeout(() => {
                            uploadProgress.classList.add('hidden');
                            fileQueue.classList.add('hidden');
                            selectedFiles = [];
                            fileInput.value = '';
                            
                            // Show success message with navigation option
                            const successMsg = document.createElement('div');
                            successMsg.className = 'bg-green-50 border border-green-200 rounded-lg p-4 mb-6';
                            successMsg.innerHTML = `
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                        <span class="text-green-800">Upload berhasil! File telah disimpan.</span>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="file-manager.php" class="btn-primary btn-sm">Lihat File</a>
                                        <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-green-600 hover:text-green-800">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                            document.querySelector('.max-w-7xl').insertBefore(successMsg, document.querySelector('.grid'));
                        }, 2000);
                    }
                },
                (error) => {
                    if (statusElement) {
                        statusElement.textContent = 'Failed';
                        statusElement.className = 'text-xs text-red-600';
                    }
                    Utils.showNotification(`Failed to upload "${file.name}": ${error}`, 'error');
                }
            );
        });
    }

    window.removeFile = function(index) {
        selectedFiles.splice(index, 1);
        displayFileQueue();
    };
});
</script>

<?php include '../components/footer.php'; ?>
