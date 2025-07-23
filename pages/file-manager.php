<?php
require_once '../config/auth.php';

// Require authentication
requireAuth('../login.php');

// --- Tambahan: Inisialisasi CSRF Token ---
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
// --- atau gunakan fungsi generateCSRFToken() jika sudah ada ---
// $csrfToken = generateCSRFToken();
// ---

// Get current user
$currentUser = getCurrentUser();

$page_title = 'File Manager - FileManager Pro';
include '../components/header.php';
?>

<!-- CSRF Token for JavaScript -->
<meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken); ?>">

<!-- Main Content -->
<div class="min-h-screen flex flex-col">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-3xl font-bold text-secondary-900">
                        <i class="fas fa-folder-open mr-3 text-primary-600"></i>
                        File Manager
                    </h1>
                    <p class="text-secondary-600 mt-2">
                        Kelola dan organisir semua file Anda
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="upload.php" class="btn-primary">
                        <i class="fas fa-upload mr-2"></i>
                        Upload File
                    </a>
                    <a href="../index.php" class="btn-secondary">
                        <i class="fas fa-home mr-2"></i>
                        Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="bg-white rounded-xl shadow-sm border border-secondary-100 p-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <!-- Left side: Search and filters -->
                <div class="flex items-center space-x-4">
                    <div class="relative">
                    <input type="text" id="search-input" placeholder="Cari file..." 
                           class="w-64 pl-10 pr-4 py-2 border border-secondary-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-2.5 text-secondary-400"></i>
                    </div>
                    
                    <select id="type-filter" class="border border-secondary-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">All Types</option>
                        <option value="image">Images</option>
                        <option value="document">Documents</option>
                        <option value="video">Videos</option>
                        <option value="audio">Audio</option>
                        <option value="archive">Archives</option>
                    </select>
                </div>

                <!-- Right side: View options and actions -->
                <div class="flex items-center space-x-3">
                    <!-- View Toggle -->
                    <div class="flex bg-secondary-100 rounded-lg p-1">
                        <button id="grid-view" class="p-2 rounded-md bg-white shadow-sm text-primary-600">
                            <i class="fas fa-th"></i>
                        </button>
                        <button id="list-view" class="p-2 rounded-md text-secondary-500 hover:text-secondary-700">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>

                    <!-- Sort Options -->
                    <select id="sort-select" class="border border-secondary-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="upload_date">Sort by Date</option>
                        <option value="original_name">Sort by Name</option>
                        <option value="file_size">Sort by Size</option>
                        <option value="file_type">Sort by Type</option>
                    </select>

                    <!-- Tambahkan tombol New Folder di toolbar -->
                    <button id="new-folder-btn" class="btn-secondary flex items-center"><i class="fas fa-folder-plus mr-2"></i> New Folder</button>
                </div>
            </div>
        </div>

        <!-- Breadcrumb Navigation -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3" id="breadcrumb-list">
                <li class="inline-flex items-center">
                    <a href="#" class="inline-flex items-center text-sm font-medium text-secondary-700 hover:text-primary-600" onclick="navigateToFolder(null)">
                        <i class="fas fa-home mr-2"></i>
                        Root
                    </a>
                </li>
            </ol>
        </nav>

        <!-- File Grid/List Container -->
        <div class="bg-white rounded-xl shadow-sm border border-secondary-100 p-6">
            <!-- Grid View (Default) -->
            <div id="files-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <!-- Empty State -->
                <div class="col-span-full text-center py-16" id="empty-state">
                    <div class="text-6xl text-secondary-300 mb-4">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-secondary-900 mb-2">
                        Folder Kosong
                    </h3>
                    <p class="text-secondary-500 mb-6">
                        Belum ada file di folder ini. Upload file pertama Anda untuk memulai.
                    </p>
                    <a href="upload.php" class="btn-primary">
                        <i class="fas fa-upload mr-2"></i>
                        Upload File
                    </a>
                </div>
            </div>

            <!-- List View (Hidden by default) -->
            <div id="files-list" class="hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-secondary-200">
                        <thead class="bg-secondary-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    <input type="checkbox" class="rounded border-gray-300">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Size
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Modified
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-secondary-200" id="files-table-body">
                            <!-- Files will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- File Statistics -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-secondary-100 p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <i class="fas fa-file text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-2xl font-bold text-secondary-900">0</h3>
                        <p class="text-secondary-500">Total Files</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-secondary-100 p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <i class="fas fa-folder text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-2xl font-bold text-secondary-900">0</h3>
                        <p class="text-secondary-500">Folders</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-secondary-100 p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100">
                        <i class="fas fa-hdd text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-2xl font-bold text-secondary-900">0 MB</h3>
                        <p class="text-secondary-500">Total Size</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-secondary-100 p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100">
                        <i class="fas fa-star text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-2xl font-bold text-secondary-900">0</h3>
                        <p class="text-secondary-500">Favorites</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview File -->
<div id="preview-modal" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full p-6 relative">
    <button id="close-preview" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl">&times;</button>
    <div id="preview-content" class="max-h-[70vh] overflow-auto"></div>
  </div>
</div>
<!-- Modal Rename File -->
<div id="rename-modal" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
    <button id="close-rename" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl">&times;</button>
    <h3 class="text-lg font-bold mb-4">Rename File</h3>
    <input id="rename-input" type="text" class="w-full border rounded p-2 mb-4" placeholder="Nama file baru">
    <button id="rename-confirm" class="btn-primary w-full">Rename</button>
  </div>
</div>
<!-- Modal Copy File -->
<div id="copy-modal" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
    <button id="close-copy" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl">&times;</button>
    <h3 class="text-lg font-bold mb-4">Copy File</h3>
    <input id="copy-input" type="text" class="w-full border rounded p-2 mb-4" placeholder="Nama file hasil copy">
    <button id="copy-confirm" class="btn-primary w-full">Copy</button>
  </div>
</div>
<!-- Modal Move File (dummy) -->
<div id="move-modal" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
    <button id="close-move" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl">&times;</button>
    <h3 class="text-lg font-bold mb-4">Move File</h3>
    <select id="move-select" class="w-full border rounded p-2 mb-4">
      <option value="root">Root (dummy)</option>
    </select>
    <button id="move-confirm" class="btn-primary w-full">Move</button>
  </div>
</div>
<!-- Modal New Folder -->
<div id="new-folder-modal" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
    <button id="close-new-folder" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl">&times;</button>
    <h3 class="text-lg font-bold mb-4">Buat Folder Baru</h3>
    <input id="new-folder-input" type="text" class="w-full border rounded p-2 mb-4" placeholder="Nama folder">
    <button id="new-folder-confirm" class="btn-primary w-full">Buat Folder</button>
    </div>
</div>
<!-- Modal Set Password Folder -->
<div id="set-folder-password-modal" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
    <button id="close-set-folder-password" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl">&times;</button>
    <h3 class="text-lg font-bold mb-4">Set Password Folder</h3>
    <input id="set-folder-password-input" type="password" class="w-full border rounded p-2 mb-4" placeholder="Password baru (kosongkan untuk hapus password)">
    <button id="set-folder-password-confirm" class="btn-primary w-full">Simpan Password</button>
  </div>
</div>
<!-- Modal Input Password Folder -->
<div id="input-folder-password-modal" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
    <button id="close-input-folder-password" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl">&times;</button>
    <h3 class="text-lg font-bold mb-4">Masukkan Password Folder</h3>
    <input id="input-folder-password-input" type="password" class="w-full border rounded p-2 mb-4" placeholder="Password folder">
    <button id="input-folder-password-confirm" class="btn-primary w-full">Buka Folder</button>
  </div>
</div>
<!-- Modal Rename Folder -->
<div id="rename-folder-modal" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
    <button id="close-rename-folder" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl">&times;</button>
    <h3 class="text-lg font-bold mb-4">Rename Folder</h3>
    <input id="rename-folder-input" type="text" class="w-full border rounded p-2 mb-4" placeholder="Nama folder baru">
    <button id="rename-folder-confirm" class="btn-primary w-full">Rename</button>
  </div>
</div>
<!-- Modal Set Password File -->
<div id="set-file-password-modal" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
    <button id="close-set-file-password" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl">&times;</button>
    <h3 class="text-lg font-bold mb-4">Set Password File</h3>
    <input id="set-file-password-input" type="password" class="w-full border rounded p-2 mb-4" placeholder="Password baru (kosongkan untuk hapus password)">
    <button id="set-file-password-confirm" class="btn-primary w-full">Simpan Password</button>
  </div>
</div>
<!-- Modal Input Password File -->
<div id="input-file-password-modal" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
    <button id="close-input-file-password" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl">&times;</button>
    <h3 class="text-lg font-bold mb-4">Masukkan Password File</h3>
    <input id="input-file-password-input" type="password" class="w-full border rounded p-2 mb-4" placeholder="Password file">
    <button id="input-file-password-confirm" class="btn-primary w-full">Akses File</button>
    </div>
</div>

<!-- File Context Menu -->
<div id="context-menu" class="fixed z-50 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 hidden">
    <button class="flex items-center w-full px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-100">
        <i class="fas fa-eye mr-3"></i> Preview
    </button>
    <button class="flex items-center w-full px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-100">
        <i class="fas fa-download mr-3"></i> Download
    </button>
    <button class="flex items-center w-full px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-100">
        <i class="fas fa-edit mr-3"></i> Rename
    </button>
    <button class="flex items-center w-full px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-100">
        <i class="fas fa-copy mr-3"></i> Copy
    </button>
    <button class="flex items-center w-full px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-100">
        <i class="fas fa-cut mr-3"></i> Move
    </button>
    <hr class="my-1">
    <button class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
        <i class="fas fa-trash mr-3"></i> Delete
    </button>
</div>

<script>
// Get CSRF token from meta tag
function getCSRFToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

window.folderPasswordCache = {};
// --- Tambahan untuk seleksi file dan aksi massal ---
let selectedFiles = [];

function updateSelectedFiles(filename, checked) {
    if (checked) {
        if (!selectedFiles.includes(filename)) selectedFiles.push(filename);
    } else {
        selectedFiles = selectedFiles.filter(f => f !== filename);
    }
}

function clearSelectedFiles() {
    selectedFiles = [];
    // Uncheck all checkboxes
    document.querySelectorAll('.file-checkbox').forEach(cb => cb.checked = false);
}

function getSelectedFiles() {
    return selectedFiles;
}

// --- Aksi Massal ---
function downloadSelected() {
    if (selectedFiles.length === 0) return Utils.showNotification('Pilih file terlebih dahulu', 'warning');
    selectedFiles.forEach(filename => downloadFile(filename));
}
function deleteSelected() {
    if (selectedFiles.length === 0) return Utils.showNotification('Pilih file terlebih dahulu', 'warning');
    if (!confirm('Yakin ingin menghapus file terpilih?')) return;
    let deleted = 0;
    selectedFiles.forEach(filename => {
        fetch('/ManagementSistemFile/includes/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `filename=${encodeURIComponent(filename)}&csrf_token=${encodeURIComponent(getCSRFToken())}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) deleted++;
            if (deleted === selectedFiles.length) {
                Utils.showNotification('File berhasil dihapus', 'success');
                loadFiles();
                clearSelectedFiles();
            }
        });
    });
}
function copySelected() {
    Utils.showNotification('Fitur copy belum diimplementasikan', 'info');
}
function moveSelected() {
    Utils.showNotification('Fitur move belum diimplementasikan', 'info');
}

// --- Event Handler Dropdown Actions ---
// (Blok kode berikut dihapus karena tidak ada elemen dengan id 'actions-menu')
// document.addEventListener('DOMContentLoaded', function() {
//   var actionsMenu = document.getElementById('actions-menu');
//   if (actionsMenu) {
//     var btns = actionsMenu.querySelectorAll('button');
//     if (btns[0]) btns[0].onclick = downloadSelected;
//     if (btns[1]) btns[1].onclick = copySelected;
//     if (btns[2]) btns[2].onclick = moveSelected;
//     if (btns[4]) btns[4].onclick = deleteSelected;
//   }
// });

// --- Modal Preview ---
function showPreview(filename, originalName, mimeType) {
  const modal = document.getElementById('preview-modal');
  const content = document.getElementById('preview-content');
  content.innerHTML = '<div class="text-center text-gray-400">Loading preview...</div>';
  modal.classList.remove('hidden');

  // Fallback: gunakan filename jika originalName kosong
  if (!originalName) originalName = filename;
  // Fallback: deteksi ekstensi dari originalName
  let ext = originalName.split('.').pop().toLowerCase();
  let url = `/ManagementSistemFile/uploads/${filename}`;

  // Fallback: deteksi mimeType dari ekstensi jika kosong
  if (!mimeType) {
    const extMime = {
      'jpg': 'image/jpeg', 'jpeg': 'image/jpeg', 'png': 'image/png', 'gif': 'image/gif', 'bmp': 'image/bmp', 'webp': 'image/webp',
      'pdf': 'application/pdf',
      'mp3': 'audio/mpeg', 'wav': 'audio/wav', 'ogg': 'audio/ogg',
      'mp4': 'video/mp4', 'webm': 'video/webm', 'mov': 'video/quicktime',
      'txt': 'text/plain', 'csv': 'text/csv', 'json': 'application/json', 'xml': 'application/xml', 'md': 'text/markdown', 'log': 'text/plain'
    };
    mimeType = extMime[ext] || '';
  }

  // Preview logic
  if (mimeType.startsWith('image/')) {
    content.innerHTML = `<img src="${url}" alt="${originalName}" class="max-w-full max-h-[60vh] mx-auto" />`;
  } else if (mimeType === 'application/pdf') {
    content.innerHTML = `<iframe src="${url}" class="w-full h-[60vh]" frameborder="0"></iframe>`;
  } else if (mimeType.startsWith('audio/')) {
    content.innerHTML = `<audio controls src="${url}" class="w-full mt-4"></audio>`;
  } else if (mimeType.startsWith('video/')) {
    content.innerHTML = `<video controls src="${url}" class="w-full max-h-[60vh] mt-4"></video>`;
  } else if (mimeType.startsWith('text/') || ['txt','csv','json','xml','md','log'].includes(ext)) {
    fetch(url).then(r => {
      if (!r.ok) throw new Error('File tidak ditemukan');
      return r.text();
    }).then(txt => {
      // Batasi preview file teks besar
      let previewText = txt.length > 5000 ? txt.substring(0, 5000) + '\n... (terpotong)' : txt;
      content.innerHTML = `<pre class="bg-gray-100 p-4 rounded text-xs overflow-auto max-h-[60vh]">${escapeHtml(previewText)}</pre>`;
    }).catch((err) => {
      content.innerHTML = `<div class="text-red-500">Tidak bisa menampilkan file ini. ${err.message || ''}</div>`;
    });
  } else {
    // Fallback: tampilkan info file dan link download
    content.innerHTML = `
      <div class="text-center text-secondary-700 mb-4">
        <i class="fas fa-file text-4xl"></i>
        <div class="mt-2 font-semibold">${escapeHtml(originalName)}</div>
        <div class="text-xs text-secondary-400">Ekstensi: .${ext}</div>
      </div>
      <a href="${url}" target="_blank" class="text-blue-600 underline">Buka atau download file ini</a>
    `;
  }
}
function escapeHtml(text) {
  var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
document.getElementById('close-preview').onclick = function() {
  document.getElementById('preview-modal').classList.add('hidden');
};
// --- Modal Rename, Copy, Move ---
let currentRenameFile = null;
let currentRenameOriginalName = null;
let currentRenameExt = null;
function openRenameModal(filename, originalName) {
  currentRenameFile = filename;
  currentRenameOriginalName = originalName;
  // Ambil nama tanpa ekstensi
  const dotIdx = originalName.lastIndexOf('.');
  let nameOnly = originalName;
  currentRenameExt = '';
  if (dotIdx > 0) {
    nameOnly = originalName.substring(0, dotIdx);
    currentRenameExt = originalName.substring(dotIdx); // termasuk titik
  }
  document.getElementById('rename-input').value = nameOnly;
  document.getElementById('rename-modal').classList.remove('hidden');
}
document.getElementById('close-rename').onclick = function() {
  document.getElementById('rename-modal').classList.add('hidden');
};
document.getElementById('rename-confirm').onclick = function() {
  let newName = document.getElementById('rename-input').value.trim();
  if (!newName) return Utils.showNotification('Nama file baru tidak boleh kosong', 'warning');
  // Tambahkan ekstensi jika user tidak mengetik ekstensi
  if (currentRenameExt && !newName.toLowerCase().endsWith(currentRenameExt.toLowerCase())) {
    newName += currentRenameExt;
  }
  FileManager.renameFile(currentRenameFile, newName, function() {
    document.getElementById('rename-modal').classList.add('hidden');
    loadFiles();
  });
};
function openCopyModal(filename, originalName) {
  currentCopyFile = filename;
  document.getElementById('copy-input').value = 'Copy of ' + originalName;
  document.getElementById('copy-modal').classList.remove('hidden');
}
document.getElementById('close-copy').onclick = function() {
  document.getElementById('copy-modal').classList.add('hidden');
};
document.getElementById('copy-confirm').onclick = function() {
  const newName = document.getElementById('copy-input').value.trim();
  if (!newName) return Utils.showNotification('Nama file hasil copy tidak boleh kosong', 'warning');
  fetch('/ManagementSistemFile/includes/copy.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `filename=${encodeURIComponent(currentCopyFile)}&newname=${encodeURIComponent(newName)}&csrf_token=${encodeURIComponent(getCSRFToken())}`
  })
  .then(r => r.json())
  .then(data => {
    document.getElementById('copy-modal').classList.add('hidden');
    if (data.success) {
      Utils.showNotification('File berhasil dicopy', 'success');
      loadFiles();
    } else {
      Utils.showNotification(data.message || 'Gagal menyalin file', 'error');
    }
  })
  .catch(() => {
    document.getElementById('copy-modal').classList.add('hidden');
    Utils.showNotification('Network error', 'error');
  });
};
// --- Modal Move File (update: dropdown folder tujuan) ---
function openMoveModal(filename, originalName) {
  currentMoveFile = filename;
  const select = document.getElementById('move-select');
  select.innerHTML = '<option value="">Loading...</option>';
  // Ambil semua folder (tanpa parent_id)
  fetch('/ManagementSistemFile/includes/get_files.php?action=folders')
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        select.innerHTML = '<option value="">Pilih folder tujuan</option>';
        select.innerHTML += '<option value="root">Root</option>';
        data.data.forEach(folder => {
          select.innerHTML += `<option value="${folder.id}">${folder.name}</option>`;
        });
      } else {
        select.innerHTML = '<option value="">Gagal memuat folder</option>';
      }
    });
  document.getElementById('move-modal').classList.remove('hidden');
}
document.getElementById('close-move').onclick = function() {
  document.getElementById('move-modal').classList.add('hidden');
};
document.getElementById('move-confirm').onclick = function() {
  const folderId = document.getElementById('move-select').value;
  if (!folderId) return Utils.showNotification('Pilih folder tujuan', 'warning');
  fetch('/ManagementSistemFile/includes/get_files.php?action=move_file', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `filename=${encodeURIComponent(currentMoveFile)}&folder_id=${folderId === 'root' ? '' : encodeURIComponent(folderId)}&csrf_token=${encodeURIComponent(getCSRFToken())}`
  })
  .then(r => r.json())
  .then(data => {
    document.getElementById('move-modal').classList.add('hidden');
    if (data.success) {
      Utils.showNotification('File berhasil dipindahkan', 'success');
      // Refresh folder asal dan tujuan
      loadFiles('', '', 'upload_date', 'DESC', window.currentFolderId);
    } else {
      Utils.showNotification(data.message || 'Gagal memindahkan file', 'error');
    }
  })
  .catch(() => {
    document.getElementById('move-modal').classList.add('hidden');
    Utils.showNotification('Network error', 'error');
  });
};
// --- Modal New Folder ---
document.getElementById('new-folder-btn').onclick = function() {
  document.getElementById('new-folder-input').value = '';
  document.getElementById('new-folder-modal').classList.remove('hidden');
};
document.getElementById('close-new-folder').onclick = function() {
  document.getElementById('new-folder-modal').classList.add('hidden');
};
document.getElementById('new-folder-confirm').onclick = function() {
  const name = document.getElementById('new-folder-input').value.trim();
  if (!name) return Utils.showNotification('Nama folder tidak boleh kosong', 'warning');
  const parentId = window.currentFolderId !== undefined ? window.currentFolderId : null;
  fetch('/ManagementSistemFile/includes/get_files.php?action=create_folder', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `name=${encodeURIComponent(name)}${parentId ? `&parent_id=${parentId}` : ''}&csrf_token=${encodeURIComponent(getCSRFToken())}`
  })
  .then(r => r.json())
  .then(data => {
    document.getElementById('new-folder-modal').classList.add('hidden');
    if (data.success) {
      Utils.showNotification('Folder berhasil dibuat', 'success');
      loadFiles('', '', 'upload_date', 'DESC', window.currentFolderId);
    } else {
      Utils.showNotification(data.message || 'Gagal membuat folder', 'error');
    }
  })
  .catch(() => {
    document.getElementById('new-folder-modal').classList.add('hidden');
    Utils.showNotification('Network error', 'error');
  });
};
// Integrasi ke context menu
document.addEventListener('DOMContentLoaded', function() {
  const contextMenu = document.getElementById('context-menu');
  const btns = contextMenu.querySelectorAll('button');
  btns[2].onclick = function() { if(window.contextFile) openRenameModal(window.contextFile, window.contextOriginal); };
  btns[3].onclick = function() { if(window.contextFile) openCopyModal(window.contextFile, window.contextOriginal); };
  btns[4].onclick = function() { if(window.contextFile) openMoveModal(window.contextFile, window.contextOriginal); };
});
// --- Kembalikan tombol action ke pola semula ---
// Patch tombol Preview dan Download file agar cek password dulu jika file diproteksi
function createFileElement(file) {
  const fileElement = document.createElement('div');
  fileElement.className = 'file-item bg-white border border-secondary-200 rounded-lg p-4 hover:shadow-md hover:border-primary-300 transition duration-200 cursor-pointer group';
  fileElement.setAttribute('data-filename', file.filename);
  const iconClass = Utils.getFileIcon(file.original_name);
  fileElement.innerHTML = `
    <div class="text-center">
      <div class="text-3xl mb-3">
        <i class="${iconClass}"></i>
      </div>
      <h3 class="filename text-sm font-medium text-secondary-900 truncate mb-1" title="${file.original_name}">
        ${file.original_name}
      </h3>
      <p class="text-xs text-secondary-500">${file.file_size_formatted}</p>
      <div class="mt-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
        <div class="flex justify-center space-x-1">
          <button class="p-1 text-secondary-400 hover:text-primary-600 rounded" title="Preview" onclick="${file.is_protected ? `openInputFilePassword('${file.filename}', 'preview')` : `showPreview('${file.filename}','${file.original_name}','${file.mime_type}')`}">
            <i class="fas fa-eye text-xs"></i>
          </button>
          <button class="p-1 text-secondary-400 hover:text-green-600 rounded" title="Download" onclick="${file.is_protected ? `openInputFilePassword('${file.filename}', 'download')` : `downloadFile('${file.filename}')`}">
            <i class="fas fa-download text-xs"></i>
          </button>
          <button class="p-1 text-secondary-400 hover:text-yellow-600 rounded" title="Rename" onclick="openRenameModal('${file.filename}','${file.original_name}')">
            <i class="fas fa-edit text-xs"></i>
          </button>
          <button class="p-1 text-secondary-400 hover:text-blue-600 rounded" title="Copy" onclick="openCopyModal('${file.filename}','${file.original_name}')">
            <i class="fas fa-copy text-xs"></i>
          </button>
          <button class="p-1 text-secondary-400 hover:text-purple-600 rounded" title="Move" onclick="openMoveModal('${file.filename}','${file.original_name}')">
            <i class="fas fa-cut text-xs"></i>
          </button>
          <button class="p-1 text-secondary-400 hover:text-red-600 rounded" title="Delete" onclick="deleteFile('${file.filename}')">
            <i class="fas fa-trash text-xs"></i>
          </button>
          <button class="p-1 text-blue-500 hover:text-blue-700 rounded" title="Set Password" onclick="openSetFilePassword('${file.filename}')">
            <i class="fas fa-key text-xs"></i>
          </button>
        </div>
      </div>
    </div>
  `;
  return fileElement;
}
function createListRow(file) {
  const tr = document.createElement('tr');
  tr.className = 'file-item';
  tr.setAttribute('data-filename', file.filename);
  tr.innerHTML = `
    <td class="px-6 py-3"><input type="checkbox" class="file-checkbox" onchange="updateSelectedFiles('${file.filename}', this.checked)"></td>
    <td class="px-6 py-3">${file.original_name}</td>
    <td class="px-6 py-3">${file.file_size_formatted}</td>
    <td class="px-6 py-3">${file.file_type || '-'} </td>
    <td class="px-6 py-3">${file.modified || '-'} </td>
    <td class="px-6 py-3">
      <button class="p-1 text-secondary-400 hover:text-primary-600 rounded" title="Preview" onclick="${file.is_protected ? `openInputFilePassword('${file.filename}', 'preview')` : `showPreview('${file.filename}','${file.original_name}','${file.mime_type}')`}"><i class="fas fa-eye"></i></button>
      <button class="p-1 text-secondary-400 hover:text-green-600 rounded" title="Download" onclick="${file.is_protected ? `openInputFilePassword('${file.filename}', 'download')` : `downloadFile('${file.filename}')`}"><i class="fas fa-download"></i></button>
      <button class="p-1 text-secondary-400 hover:text-yellow-600 rounded" title="Rename" onclick="openRenameModal('${file.filename}','${file.original_name}')"><i class="fas fa-edit"></i></button>
      <button class="p-1 text-secondary-400 hover:text-blue-600 rounded" title="Copy" onclick="openCopyModal('${file.filename}','${file.original_name}')"><i class="fas fa-copy"></i></button>
      <button class="p-1 text-secondary-400 hover:text-purple-600 rounded" title="Move" onclick="openMoveModal('${file.filename}','${file.original_name}')"><i class="fas fa-cut"></i></button>
      <button class="p-1 text-secondary-400 hover:text-red-600 rounded" title="Delete" onclick="deleteFile('${file.filename}')"><i class="fas fa-trash"></i></button>
      <button class="p-1 text-blue-500 hover:text-blue-700 rounded" title="Set Password" onclick="openSetFilePassword('${file.filename}')">
        <i class="fas fa-key text-xs"></i>
      </button>
    </td>
  `;
  return tr;
}
// Context menu preview
(function() {
  document.addEventListener('contextmenu', function(e) {
    const fileItem = e.target.closest('.file-item');
    if (fileItem) {
      const files = window.lastLoadedFiles || [];
      const fname = fileItem.getAttribute('data-filename');
      const f = files.find(f => f.filename === fname);
      if (f) {
        window.contextFile = f.filename;
        window.contextOriginal = f.original_name;
        window.contextMime = f.mime_type;
      }
    }
  });
  document.addEventListener('DOMContentLoaded', function() {
    const contextMenu = document.getElementById('context-menu');
    const btns = contextMenu.querySelectorAll('button');
    if (btns[0]) btns[0].onclick = function() { if(window.contextFile) showPreview(window.contextFile, window.contextOriginal, window.contextMime); };
    if (btns[1]) btns[1].onclick = function() { if(window.contextFile) downloadFile(window.contextFile); };
    if (btns[2]) btns[2].onclick = function() { if(window.contextFile) openRenameModal(window.contextFile, window.contextOriginal); };
    if (btns[3]) btns[3].onclick = function() { if(window.contextFile) openCopyModal(window.contextFile, window.contextOriginal); };
    if (btns[4]) btns[4].onclick = function() { if(window.contextFile) openMoveModal(window.contextFile, window.contextOriginal); };
    if (btns[6]) btns[6].onclick = function() { if(window.contextFile) deleteFile(window.contextFile); };
  });
})();
// Simpan data file terakhir untuk context menu
window.lastLoadedFiles = [];
const origLoadFiles = window.loadFiles;
window.loadFiles = function(search = '', type = '', sort = 'upload_date', order = 'DESC', folderId = null) {
  const params = new URLSearchParams({
    action: 'list',
    search: search,
    type: type,
    sort: sort,
    order: order
  });
  if (folderId !== null) params.append('folder_id', folderId);
  // Fetch folder dan file paralel
  Promise.all([
    fetch(`/ManagementSistemFile/includes/get_files.php?action=folders${folderId !== null ? `&parent_id=${folderId}` : ''}`).then(r => r.json()),
    fetch(`/ManagementSistemFile/includes/get_files.php?${params}`).then(r => r.json())
  ]).then(([folderData, data]) => {
    const filesGrid = document.getElementById('files-grid');
    filesGrid.querySelectorAll('.file-item, .folder-item').forEach(item => item.remove());
    // Folder
    if (folderData.success && folderData.data.length > 0) {
      folderData.data.forEach(folder => {
        const folderDiv = document.createElement('div');
        folderDiv.className = 'folder-item bg-yellow-50 border border-yellow-200 rounded-lg p-4 hover:shadow-md hover:border-yellow-400 transition duration-200 cursor-pointer group flex flex-col items-center';
        folderDiv.innerHTML = `<div class='text-3xl mb-2'><i class='fas fa-folder text-yellow-500'></i></div><div class='text-sm font-medium text-secondary-900 truncate mb-1'>${folder.name}</div>`;
        // Tombol Set Password
        const btnSetPwd = document.createElement('button');
        btnSetPwd.className = 'text-xs text-blue-500 hover:text-blue-700 mb-1';
        btnSetPwd.innerHTML = `<i class='fas fa-key'></i> Set Password`;
        btnSetPwd.onclick = function(e) { e.stopPropagation(); openSetFolderPassword(folder.id); };
        // Tombol Hapus
        const btnDel = document.createElement('button');
        btnDel.className = 'text-xs text-red-500 hover:text-red-700';
        btnDel.innerHTML = `<i class='fas fa-trash'></i> Hapus`;
        btnDel.onclick = function(e) { e.stopPropagation(); deleteFolder(folder.id); };
        // Tombol Rename
        const btnRename = document.createElement('button');
        btnRename.className = 'text-xs text-yellow-600 hover:text-yellow-800 mb-1';
        btnRename.innerHTML = `<i class='fas fa-edit'></i> Rename`;
        btnRename.onclick = function(e) { e.stopPropagation(); openRenameFolder(folder.id, folder.name); };
        // Container tombol
        const btnWrap = document.createElement('div');
        btnWrap.className = 'flex flex-col items-center mt-2';
        btnWrap.appendChild(btnSetPwd);
        btnWrap.appendChild(btnDel);
        btnWrap.appendChild(btnRename); // Tambahkan tombol Rename
        folderDiv.appendChild(btnWrap);
        // Event klik utama folder
        folderDiv.onclick = function(e) {
          console.log('Folder clicked', folder.id, folder.name);
          try {
            navigateToFolder(folder.id, folder.name || 'Folder');
          } catch (err) {
            alert('navigateToFolder error: ' + err);
            console.error('navigateToFolder error:', err);
          }
        };
        filesGrid.appendChild(folderDiv);
      });
    }
    // File
    if (data.success && data.data.files.length > 0) {
      document.getElementById('empty-state').style.display = 'none';
      data.data.files.forEach(file => {
        const fileElement = createFileElement(file);
        filesGrid.appendChild(fileElement);
      });
    } else if (!folderData.success || folderData.data.length === 0) {
      document.getElementById('empty-state').style.display = 'block';
    } else {
      document.getElementById('empty-state').style.display = 'none';
    }
    // List view (TODO: folder di list view)
    const filesTableBody = document.getElementById('files-table-body');
    filesTableBody.innerHTML = '';
    if (data.success && data.data.files.length > 0) {
      data.data.files.forEach(file => {
        const row = createListRow(file);
        filesTableBody.appendChild(row);
      });
    }
    updateFileStats(data.data);
  }).catch(error => {
    console.error('Error loading files or folders:', error);
    if (typeof Utils !== 'undefined') {
      Utils.showNotification('Gagal memuat file/folder', 'error');
    }
  });
};

    // Search functionality
    const searchInput = document.getElementById('search-input');
    const typeFilter = document.getElementById('type-filter');
    const sortSelect = document.getElementById('sort-select');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadFiles(searchInput.value, typeFilter.value, sortSelect.value);
        }, 500);
    });

    typeFilter.addEventListener('change', function() {
        loadFiles(searchInput.value, typeFilter.value, sortSelect.value);
    });

    sortSelect.addEventListener('change', function() {
        loadFiles(searchInput.value, typeFilter.value, sortSelect.value);
    });

    // Load files from database
function loadFiles(search = '', type = '', sort = 'upload_date', order = 'DESC', folderId = null) {
        console.log('Loading files...', { search, type, sort, order });
        
        // Build query parameters
        const params = new URLSearchParams({
            action: 'list',
            search: search,
            type: type,
            sort: sort,
            order: order
        });
        
        fetch(`/ManagementSistemFile/includes/get_files.php?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.files.length > 0) {
                    document.getElementById('empty-state').style.display = 'none';
                    
                    // Clear existing files
                const existingFiles = document.querySelectorAll('.file-item');
                    existingFiles.forEach(item => item.remove());
                    
                    // Add files to grid
                    data.data.files.forEach(file => {
                        const fileElement = createFileElement(file);
                    document.getElementById('files-grid').appendChild(fileElement);
                    });
                    
                    // Update statistics
                    updateFileStats(data.data);
                } else {
                    document.getElementById('empty-state').style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error loading files:', error);
                if (typeof Utils !== 'undefined') {
                    Utils.showNotification('Gagal memuat file', 'error');
                }
            });
    }

    function updateFileStats(data) {
        // Update total files
        const totalFilesElement = document.querySelector('.text-2xl.font-bold.text-secondary-900');
        if (totalFilesElement) {
            totalFilesElement.textContent = data.total || 0;
        }

        // Load stats from API
        fetch('/ManagementSistemFile/includes/get_files.php?action=stats')
            .then(response => response.json())
            .then(statsData => {
                if (statsData.success) {
                    const stats = statsData.data;
                    
                    // Update all stat cards
                    const statCards = document.querySelectorAll('.text-2xl.font-bold.text-secondary-900');
                    if (statCards[0]) statCards[0].textContent = stats.total_files;
                    if (statCards[1]) statCards[1].textContent = stats.total_folders || 0; // Folders
                    if (statCards[2]) statCards[2].textContent = stats.total_size_formatted;
                    if (statCards[3]) statCards[3].textContent = stats.total_favorites || 0; // Favorites
                }
            })
            .catch(error => console.error('Error loading stats:', error));
    }

    function downloadFile(filename) {
        console.log('Downloading file:', filename);
        const downloadUrl = `/ManagementSistemFile/includes/download.php?filename=${encodeURIComponent(filename)}`;
        console.log('Download URL:', downloadUrl);
        
        // Create a temporary link and click it
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.target = '_blank';
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function deleteFile(filename) {
        console.log('Deleting file:', filename);
        
        if (confirm(`Apakah Anda yakin ingin menghapus file "${filename}"?`)) {
            // Show loading indicator
            Utils.showNotification('Menghapus file...', 'info');
            
            fetch('/ManagementSistemFile/includes/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `filename=${encodeURIComponent(filename)}&csrf_token=${encodeURIComponent(getCSRFToken())}`
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
                    Utils.showNotification('File berhasil dihapus', 'success');
                    loadFiles(); // Reload files
                } else {
                    Utils.showNotification(data.message || 'Gagal menghapus file', 'error');
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                Utils.showNotification('Network error: ' + error.message, 'error');
            });
        }
    }

    // Tambahkan fungsi deleteFolder
    function deleteFolder(id) {
      if (!confirm('Yakin ingin menghapus folder ini? Semua subfolder juga akan dihapus.')) return;
      fetch('/ManagementSistemFile/includes/get_files.php?action=delete_folder', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&csrf_token=${encodeURIComponent(getCSRFToken())}`
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          Utils.showNotification('Folder berhasil dihapus', 'success');
          loadFiles('', '', 'upload_date', 'DESC', window.currentFolderId);
        } else {
          Utils.showNotification(data.message || 'Gagal menghapus folder', 'error');
        }
      })
      .catch(() => {
        Utils.showNotification('Network error', 'error');
      });
    }

    // Initialize
    loadFiles();
window.currentFolderId = null;
window.folderPath = [];
function navigateToFolder(folderId, folderName, force) {
  folderName = folderName || 'Folder';
  console.log('navigateToFolder', {folderId, folderName, force});
  if (folderId === null) {
    window.currentFolderId = null;
    window.folderPath = [];
    loadFiles('', '', 'upload_date', 'DESC', null);
    renderBreadcrumb();
    return;
  }
  // Cek cache password
  if (!force && !window.folderPasswordCache[folderId]) {
    // Ambil detail folder langsung
    console.log('Fetching folder detail for id:', folderId);
    fetch(`/ManagementSistemFile/includes/get_files.php?action=get_folder&id=${folderId}`)
      .then(r => r.json())
      .then(data => {
        console.log('get_folder response', data);
        if (!data.success || !data.data) {
          Utils.showNotification('Gagal mengambil data folder', 'error');
          console.error('get_folder error', data);
          return;
        }
        const folder = data.data;
        if (folder && folder.is_protected) {
          openInputFolderPassword(folderId, folderName);
        } else {
          window.folderPasswordCache[folderId] = true;
          navigateToFolder(folderId, folderName, true);
        }
      })
      .catch(err => {
        alert('navigateToFolder error: ' + err);
        console.error('navigateToFolder error:', err);
      });
    return;
  }
  window.currentFolderId = folderId;
  loadFiles('', '', 'upload_date', 'DESC', folderId);
  // Perbaikan: update folderPath agar tidak duplikat
  const idx = window.folderPath.findIndex(f => f.id === folderId);
  if (idx !== -1) {
    window.folderPath = window.folderPath.slice(0, idx + 1);
  } else {
    window.folderPath.push({id: folderId, name: folderName});
  }
  renderBreadcrumb();
}
function renderBreadcrumb() {
  const ol = document.getElementById('breadcrumb-list');
  ol.innerHTML = '';
  // Root
  const rootLi = document.createElement('li');
  rootLi.className = 'inline-flex items-center';
  rootLi.innerHTML = `<a href="#" class="inline-flex items-center text-sm font-medium text-secondary-700 hover:text-primary-600" onclick="navigateToFolder(null)"><i class="fas fa-home mr-2"></i>Root</a>`;
  ol.appendChild(rootLi);
  window.folderPath.forEach((f, i) => {
    const li = document.createElement('li');
    li.className = 'inline-flex items-center';
    li.innerHTML = `<span class="mx-2 text-secondary-400">/</span><a href="#" class="text-sm font-medium text-secondary-700 hover:text-primary-600" onclick="navigateToFolder(${f.id})">${f.name}</a>`;
    ol.appendChild(li);
  });
}
// Inisialisasi awal
navigateToFolder(null);

// JS: Set Password
let currentSetPasswordFolderId = null;
function openSetFolderPassword(folderId) {
  currentSetPasswordFolderId = folderId;
  document.getElementById('set-folder-password-input').value = '';
  document.getElementById('set-folder-password-modal').classList.remove('hidden');
}
document.getElementById('close-set-folder-password').onclick = function() {
  document.getElementById('set-folder-password-modal').classList.add('hidden');
};
document.getElementById('set-folder-password-confirm').onclick = function() {
  const pwd = document.getElementById('set-folder-password-input').value;
  fetch('/ManagementSistemFile/includes/get_files.php?action=set_folder_password', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${currentSetPasswordFolderId}&password=${encodeURIComponent(pwd)}&csrf_token=${encodeURIComponent(getCSRFToken())}`
  })
  .then(r => r.json())
  .then(data => {
    document.getElementById('set-folder-password-modal').classList.add('hidden');
    if (data.success) {
      Utils.showNotification('Password folder berhasil diubah', 'success');
      loadFiles('', '', 'upload_date', 'DESC', window.currentFolderId);
    } else {
      Utils.showNotification(data.message || 'Gagal set password', 'error');
    }
  })
  .catch(() => {
    document.getElementById('set-folder-password-modal').classList.add('hidden');
    Utils.showNotification('Network error', 'error');
  });
};
// JS: Input Password
let pendingOpenFolder = null;
function openInputFolderPassword(folderId, folderName) {
  pendingOpenFolder = {id: folderId, name: folderName};
  document.getElementById('input-folder-password-input').value = '';
  document.getElementById('input-folder-password-modal').classList.remove('hidden');
}
document.getElementById('close-input-folder-password').onclick = function() {
  document.getElementById('input-folder-password-modal').classList.add('hidden');
};
document.getElementById('input-folder-password-confirm').onclick = function() {
  const pwd = document.getElementById('input-folder-password-input').value;
  fetch('/ManagementSistemFile/includes/get_files.php?action=check_folder_password', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${pendingOpenFolder.id}&password=${encodeURIComponent(pwd)}&csrf_token=${encodeURIComponent(getCSRFToken())}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      document.getElementById('input-folder-password-modal').classList.add('hidden');
      window.folderPasswordCache[pendingOpenFolder.id] = true;
      navigateToFolder(pendingOpenFolder.id, pendingOpenFolder.name, true);
    } else {
      Utils.showNotification(data.message || 'Password salah', 'error');
    }
  })
  .catch(() => {
    Utils.showNotification('Network error', 'error');
  });
};
// JS: Rename Folder
let currentRenameFolderId = null;
function openRenameFolder(folderId, folderName) {
  currentRenameFolderId = folderId;
  document.getElementById('rename-folder-input').value = folderName;
  document.getElementById('rename-folder-modal').classList.remove('hidden');
}
document.getElementById('close-rename-folder').onclick = function() {
  document.getElementById('rename-folder-modal').classList.add('hidden');
};
document.getElementById('rename-folder-confirm').onclick = function() {
  const name = document.getElementById('rename-folder-input').value.trim();
  if (!name) return Utils.showNotification('Nama folder baru tidak boleh kosong', 'warning');
  fetch('/ManagementSistemFile/includes/get_files.php?action=rename_folder', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${currentRenameFolderId}&name=${encodeURIComponent(name)}&csrf_token=${encodeURIComponent(getCSRFToken())}`
  })
  .then(r => r.json())
  .then(data => {
    document.getElementById('rename-folder-modal').classList.add('hidden');
    if (data.success) {
      Utils.showNotification('Folder berhasil di-rename', 'success');
      loadFiles('', '', 'upload_date', 'DESC', window.currentFolderId);
    } else {
      Utils.showNotification(data.message || 'Gagal rename folder', 'error');
    }
  })
  .catch(() => {
    document.getElementById('rename-folder-modal').classList.add('hidden');
    Utils.showNotification('Network error', 'error');
  });
};
// JS: Set Password File
let currentSetPasswordFile = null;
function openSetFilePassword(filename) {
  currentSetPasswordFile = filename;
  document.getElementById('set-file-password-input').value = '';
  document.getElementById('set-file-password-modal').classList.remove('hidden');
}
document.getElementById('close-set-file-password').onclick = function() {
  document.getElementById('set-file-password-modal').classList.add('hidden');
};
document.getElementById('set-file-password-confirm').onclick = function() {
  const pwd = document.getElementById('set-file-password-input').value;
  fetch('/ManagementSistemFile/includes/get_files.php?action=set_file_password', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `filename=${encodeURIComponent(currentSetPasswordFile)}&password=${encodeURIComponent(pwd)}&csrf_token=${encodeURIComponent(getCSRFToken())}`
  })
  .then(r => r.json())
  .then(data => {
    document.getElementById('set-file-password-modal').classList.add('hidden');
    if (data.success) {
      Utils.showNotification('Password file berhasil diubah', 'success');
      loadFiles('', '', 'upload_date', 'DESC', window.currentFolderId);
    } else {
      Utils.showNotification(data.message || 'Gagal set password file', 'error');
    }
  })
  .catch(() => {
    document.getElementById('set-file-password-modal').classList.add('hidden');
    Utils.showNotification('Network error', 'error');
  });
};
// JS: Input Password File
let pendingOpenFile = null;
function openInputFilePassword(filename, action) {
  pendingOpenFile = {filename, action};
  document.getElementById('input-file-password-input').value = '';
  document.getElementById('input-file-password-modal').classList.remove('hidden');
}
document.getElementById('close-input-file-password').onclick = function() {
  document.getElementById('input-file-password-modal').classList.add('hidden');
};
document.getElementById('input-file-password-confirm').onclick = function() {
  const pwd = document.getElementById('input-file-password-input').value;
  fetch('/ManagementSistemFile/includes/get_files.php?action=check_file_password', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `filename=${encodeURIComponent(pendingOpenFile.filename)}&password=${encodeURIComponent(pwd)}&csrf_token=${encodeURIComponent(getCSRFToken())}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      document.getElementById('input-file-password-modal').classList.add('hidden');
      if (pendingOpenFile.action === 'preview') showPreview(pendingOpenFile.filename, '', '');
      if (pendingOpenFile.action === 'download') downloadFile(pendingOpenFile.filename);
    } else {
      Utils.showNotification(data.message || 'Password salah', 'error');
    }
  })
  .catch(() => {
    Utils.showNotification('Network error', 'error');
  });
};

// Toggle Grid/List View
function setFileManagerView(view) {
    const gridBtn = document.getElementById('grid-view');
    const listBtn = document.getElementById('list-view');
    const grid = document.getElementById('files-grid');
    const list = document.getElementById('files-list');
    if (view === 'grid') {
        grid.classList.remove('hidden');
        list.classList.add('hidden');
        gridBtn.classList.add('bg-white', 'shadow-sm', 'text-primary-600');
        gridBtn.classList.remove('text-secondary-500', 'hover:text-secondary-700');
        listBtn.classList.remove('bg-white', 'shadow-sm', 'text-primary-600');
        listBtn.classList.add('text-secondary-500', 'hover:text-secondary-700');
    } else {
        list.classList.remove('hidden');
        grid.classList.add('hidden');
        listBtn.classList.add('bg-white', 'shadow-sm', 'text-primary-600');
        listBtn.classList.remove('text-secondary-500', 'hover:text-secondary-700');
        gridBtn.classList.remove('bg-white', 'shadow-sm', 'text-primary-600');
        gridBtn.classList.add('text-secondary-500', 'hover:text-secondary-700');
    }
    localStorage.setItem('fileManagerView', view);
}
document.getElementById('grid-view').addEventListener('click', function() {
    setFileManagerView('grid');
});
document.getElementById('list-view').addEventListener('click', function() {
    setFileManagerView('list');
});
document.addEventListener('DOMContentLoaded', function() {
    const lastView = localStorage.getItem('fileManagerView');
    if (lastView === 'list') {
        setFileManagerView('list');
    } else {
        setFileManagerView('grid');
    }
});

</script>

<?php include '../components/footer.php'; ?>
