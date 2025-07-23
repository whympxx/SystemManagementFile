// Main JavaScript file for File Management System

// Global CSRF token
let csrfToken = null;

// Get CSRF token from meta tag or session
function getCSRFToken() {
    if (!csrfToken) {
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        if (metaToken) {
            csrfToken = metaToken.getAttribute('content');
        } else {
            // Try to get from session via AJAX
            fetch('/ManagementSistemFile/includes/get_csrf.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        csrfToken = data.token;
                    }
                })
                .catch(error => console.error('Failed to get CSRF token:', error));
        }
    }
    return csrfToken;
}

// Utility functions
const Utils = {
    // Show notification
    showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white transform transition-all duration-300 ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 
            type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
        }`;
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${
                    type === 'success' ? 'fa-check-circle' : 
                    type === 'error' ? 'fa-exclamation-circle' : 
                    type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle'
                } mr-2"></i>
                <span>${message}</span>
                <button class="ml-4 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    },

    // Format file size
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    // Format date
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    // Get file icon based on extension
    getFileIcon(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        const iconMap = {
            // Documents
            'pdf': 'fas fa-file-pdf text-red-500',
            'doc': 'fas fa-file-word text-blue-600',
            'docx': 'fas fa-file-word text-blue-600',
            'xls': 'fas fa-file-excel text-green-600',
            'xlsx': 'fas fa-file-excel text-green-600',
            'ppt': 'fas fa-file-powerpoint text-red-600',
            'pptx': 'fas fa-file-powerpoint text-red-600',
            'txt': 'fas fa-file-alt text-gray-500',
            
            // Images
            'jpg': 'fas fa-file-image text-purple-500',
            'jpeg': 'fas fa-file-image text-purple-500',
            'png': 'fas fa-file-image text-purple-500',
            'gif': 'fas fa-file-image text-purple-500',
            'bmp': 'fas fa-file-image text-purple-500',
            'svg': 'fas fa-file-image text-purple-500',
            
            // Videos
            'mp4': 'fas fa-file-video text-red-500',
            'avi': 'fas fa-file-video text-red-500',
            'mov': 'fas fa-file-video text-red-500',
            'wmv': 'fas fa-file-video text-red-500',
            
            // Audio
            'mp3': 'fas fa-file-audio text-green-500',
            'wav': 'fas fa-file-audio text-green-500',
            'flac': 'fas fa-file-audio text-green-500',
            
            // Archives
            'zip': 'fas fa-file-archive text-yellow-600',
            'rar': 'fas fa-file-archive text-yellow-600',
            '7z': 'fas fa-file-archive text-yellow-600',
            
            // Code
            'html': 'fas fa-file-code text-orange-500',
            'css': 'fas fa-file-code text-blue-500',
            'js': 'fas fa-file-code text-yellow-500',
            'php': 'fas fa-file-code text-indigo-500',
            'py': 'fas fa-file-code text-green-600',
            'java': 'fas fa-file-code text-red-600',
        };
        
        return iconMap[ext] || 'fas fa-file text-gray-400';
    }
};

// File Operations
const FileManager = {
    // Upload file with progress
    uploadFile(formData, progressCallback, successCallback, errorCallback) {
        const xhr = new XMLHttpRequest();
        
        // Add CSRF token to form data
        const token = getCSRFToken();
        if (token) {
            formData.append('csrf_token', token);
        }
        
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                if (progressCallback) progressCallback(percentComplete);
            }
        });
        
        xhr.addEventListener('load', function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        if (successCallback) successCallback(response);
                    } else {
                        if (errorCallback) errorCallback(response.message || 'Upload failed');
                    }
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    console.error('Response Text:', xhr.responseText);
                    if (errorCallback) errorCallback('Invalid server response');
                }
            } else {
                console.error('HTTP Error:', xhr.status, xhr.statusText);
                if (errorCallback) errorCallback(`HTTP Error: ${xhr.status} ${xhr.statusText}`);
            }
        });
        
        xhr.addEventListener('error', function() {
            if (errorCallback) errorCallback('Network error');
        });
        
        xhr.open('POST', '/ManagementSistemFile/includes/upload.php');
        xhr.send(formData);
    },

    // Delete file
    deleteFile(filename, callback) {
        if (confirm(`Are you sure you want to delete "${filename}"?`)) {
            const token = getCSRFToken();
            let body = `filename=${encodeURIComponent(filename)}`;
            if (token) {
                body += `&csrf_token=${encodeURIComponent(token)}`;
            }
            
            fetch('/ManagementSistemFile/includes/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: body
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Utils.showNotification('File deleted successfully', 'success');
                    if (callback) callback();
                } else {
                    Utils.showNotification(data.message || 'Delete failed', 'error');
                }
            })
            .catch(error => {
                Utils.showNotification('Network error', 'error');
                console.error('Error:', error);
            });
        }
    },

    // Rename file
    renameFile(oldName, newName, callback) {
        const token = getCSRFToken();
        let body = `filename=${encodeURIComponent(oldName)}&newname=${encodeURIComponent(newName)}`;
        if (token) {
            body += `&csrf_token=${encodeURIComponent(token)}`;
        }
        
        fetch('/ManagementSistemFile/includes/rename.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: body
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Utils.showNotification('File renamed successfully', 'success');
                if (callback) callback();
            } else {
                Utils.showNotification(data.message || 'Rename failed', 'error');
            }
        })
        .catch(error => {
            Utils.showNotification('Network error', 'error');
            console.error('Error:', error);
        });
    }
};

// Modal Functions
const Modal = {
    show(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
    },

    hide(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
    }
};

// Search functionality
const Search = {
    debounceTimer: null,
    
    init() {
        const searchInputs = document.querySelectorAll('input[type="text"][placeholder*="Cari"]');
        searchInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.performSearch(e.target.value);
                }, 300);
            });
        });
    },

    performSearch(query) {
        if (query.length < 2) {
            this.showAllFiles();
            return;
        }

        const fileItems = document.querySelectorAll('.file-item');
        let visibleCount = 0;

        fileItems.forEach(item => {
            const filename = item.querySelector('.filename')?.textContent.toLowerCase();
            if (filename && filename.includes(query.toLowerCase())) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Show "no results" message if needed
        this.updateSearchResults(visibleCount);
    },

    showAllFiles() {
        const fileItems = document.querySelectorAll('.file-item');
        fileItems.forEach(item => {
            item.style.display = 'block';
        });
        this.updateSearchResults(fileItems.length);
    },

    updateSearchResults(count) {
        let noResultsMsg = document.querySelector('.no-results-message');
        if (count === 0) {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.className = 'no-results-message text-center py-8 text-gray-500';
                noResultsMsg.innerHTML = `
                    <i class="fas fa-search text-4xl mb-4"></i>
                    <p class="text-lg">No files found matching your search.</p>
                `;
                const container = document.querySelector('.files-container') || document.querySelector('.grid');
                if (container) {
                    container.appendChild(noResultsMsg);
                }
            }
            noResultsMsg.style.display = 'block';
        } else {
            if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        }
    }
};

// Drag and Drop functionality
const DragDrop = {
    init() {
        const dropZone = document.querySelector('.drop-zone');
        if (!dropZone) return;

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, this.preventDefaults, false);
            document.body.addEventListener(eventName, this.preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, this.highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, this.unhighlight, false);
        });

        dropZone.addEventListener('drop', this.handleDrop, false);
    },

    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    },

    highlight(e) {
        const dropZone = document.querySelector('.drop-zone');
        dropZone.classList.add('border-primary-500', 'bg-primary-50');
    },

    unhighlight(e) {
        const dropZone = document.querySelector('.drop-zone');
        dropZone.classList.remove('border-primary-500', 'bg-primary-50');
    },

    handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        this.handleFiles(files);
    },

    handleFiles(files) {
        Array.from(files).forEach(this.uploadFile);
    },

    uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);

        FileManager.uploadFile(
            formData,
            (progress) => {
                // Update progress bar if exists
                const progressBar = document.querySelector('.upload-progress');
                if (progressBar) {
                    progressBar.style.width = progress + '%';
                }
            },
            (response) => {
                Utils.showNotification(`File "${file.name}" uploaded successfully`, 'success');
                // Reload file list or add new file to list
                if (typeof loadFiles === 'function') {
                    loadFiles();
                }
            },
            (error) => {
                Utils.showNotification(`Failed to upload "${file.name}": ${error}`, 'error');
            }
        );
    }
};

// Fungsi untuk mengambil dan menampilkan storage used
function fetchStorageUsed() {
    fetch('includes/get_storage_used.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const el = document.getElementById('storage-used');
                if (el) {
                    el.textContent = 'Storage Used: ' + data.storage_used_formatted;
                }
            } else {
                const el = document.getElementById('storage-used');
                if (el) {
                    el.textContent = 'Storage Used: Error';
                }
            }
        })
        .catch(() => {
            const el = document.getElementById('storage-used');
            if (el) {
                el.textContent = 'Storage Used: Error';
            }
        });
}

// Panggil saat halaman dimuat
if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', fetchStorageUsed);
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    Search.init();
    DragDrop.init();
    
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            // Add tooltip logic here if needed
        });
    });
});

// Export for global access
window.Utils = Utils;
window.FileManager = FileManager;
window.Modal = Modal;
window.Search = Search;

// Tambahkan fungsi renameFile ke FileManager jika belum ada
window.FileManager = window.FileManager || {};
window.FileManager.renameFile = function(filename, newName, callback) {
    const token = getCSRFToken();
    let body = `filename=${encodeURIComponent(filename)}&newname=${encodeURIComponent(newName)}`;
    if (token) {
        body += `&csrf_token=${encodeURIComponent(token)}`;
    }
    
    fetch('/ManagementSistemFile/includes/rename.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (typeof Utils !== 'undefined') Utils.showNotification('File berhasil di-rename', 'success');
            if (callback) callback();
        } else {
            if (typeof Utils !== 'undefined') Utils.showNotification(data.message || 'Gagal rename file', 'error');
        }
    })
    .catch(() => {
        if (typeof Utils !== 'undefined') Utils.showNotification('Network error', 'error');
    });
};
