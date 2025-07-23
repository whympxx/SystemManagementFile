<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Sistem Manajemen File'; ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo strpos($_SERVER['REQUEST_URI'], '/pages/') !== false ? '../' : ''; ?>css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        },
                        secondary: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    },
                    fontFamily: {
                        'sans': ['Inter', 'ui-sans-serif', 'system-ui'],
                    },
                }
            }
        }
    </script>
</head>
<body class="h-full bg-secondary-50 font-sans">
    <!-- Navigation Header -->
    <nav class="bg-white border-b border-secondary-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo and Brand -->
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-bold text-secondary-900">
                            <i class="fas fa-folder-open text-primary-600 mr-2"></i>
                            FileManager Pro
                        </h1>
                    </div>
                    
                    <!-- Desktop Navigation -->
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <?php $basePath = strpos($_SERVER['REQUEST_URI'], '/pages/') !== false ? '../' : ''; ?>
                        <a href="<?php echo $basePath; ?>index.php" class="text-secondary-900 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                            <i class="fas fa-home mr-1"></i> Dashboard
                        </a>
                        <a href="<?php echo $basePath; ?>pages/file-manager.php" class="text-secondary-500 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                            <i class="fas fa-folder mr-1"></i> File Manager
                        </a>
                        <a href="<?php echo $basePath; ?>pages/upload.php" class="text-secondary-500 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                            <i class="fas fa-upload mr-1"></i> Upload
                        </a>
                    </div>
                </div>
                
                <!-- Right side -->
                <div class="flex items-center space-x-4">
                    <!-- Search Bar -->
                    <div class="hidden md:block">
                        <div class="relative">
                            <input type="text" placeholder="Cari file..." 
                                   class="w-64 pl-10 pr-4 py-2 border border-secondary-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-2.5 text-secondary-400"></i>
                        </div>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="relative">
                        <button class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" id="user-menu-button">
                            <?php 
                            require_once __DIR__ . '/../config/auth.php';
                            $currentUser = getCurrentUser();
                            $profilePhoto = null;
                            if ($currentUser && isset($currentUser['id'])) {
                                // Ambil profile_photo dari database
                                $pdo = getDBConnection();
                                if ($pdo) {
                                    $stmt = $pdo->prepare('SELECT profile_photo FROM users WHERE id = ?');
                                    $stmt->execute([$currentUser['id']]);
                                    $row = $stmt->fetch();
                                    if ($row && !empty($row['profile_photo'])) {
                                        // Perbaiki path agar dinamis sesuai lokasi file
                                        if (strpos($_SERVER['REQUEST_URI'], '/pages/') !== false) {
                                            $profilePhoto = '../uploads/' . $row['profile_photo'];
                                        } else {
                                            $profilePhoto = 'uploads/' . $row['profile_photo'];
                                        }
                                    }
                                }
                            }
                            ?>
                            <div class="h-8 w-8 rounded-full bg-primary-600 flex items-center justify-center overflow-hidden">
                                <?php if ($profilePhoto): ?>
                                    <img src="<?= htmlspecialchars($profilePhoto) ?>" alt="Profile Photo" class="w-8 h-8 object-cover rounded-full" />
                                <?php else: ?>
                                    <i class="fas fa-user text-white text-xs"></i>
                                <?php endif; ?>
                            </div>
                            <span class="ml-2 text-secondary-700 font-medium hidden md:block">
                                <?= $currentUser ? htmlspecialchars($currentUser['username']) : 'User'; ?>
                            </span>
                            <i class="fas fa-chevron-down ml-1 text-secondary-400 text-xs hidden md:block"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 hidden" id="user-menu">
                            <a href="<?php echo $basePath; ?>pages/profile.php" class="flex items-center px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-100">
                                <i class="fas fa-user-circle mr-3"></i> Profile
                            </a>
                            <a href="<?php echo $basePath; ?>pages/settings.php" class="flex items-center px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-100">
                                <i class="fas fa-cog mr-3"></i> Settings
                            </a>
                            <hr class="my-1">
                            <a href="<?php echo $basePath; ?>logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-3"></i> Logout
                            </a>
                        </div>
                    </div>
                    
                    <!-- Mobile menu button -->
                    <button class="md:hidden p-2 rounded-md text-secondary-400 hover:text-secondary-500 hover:bg-secondary-100" id="mobile-menu-button">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Navigation -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 border-t border-secondary-200">
                <a href="<?php echo $basePath; ?>index.php" class="block px-3 py-2 rounded-md text-base font-medium text-secondary-900 hover:text-primary-600 hover:bg-secondary-100">
                    <i class="fas fa-home mr-2"></i> Dashboard
                </a>
                <a href="<?php echo $basePath; ?>pages/file-manager.php" class="block px-3 py-2 rounded-md text-base font-medium text-secondary-500 hover:text-primary-600 hover:bg-secondary-100">
                    <i class="fas fa-folder mr-2"></i> File Manager
                </a>
                <a href="<?php echo $basePath; ?>pages/upload.php" class="block px-3 py-2 rounded-md text-base font-medium text-secondary-500 hover:text-primary-600 hover:bg-secondary-100">
                    <i class="fas fa-upload mr-2"></i> Upload
                </a>
                <!-- Mobile Search -->
                <div class="px-3 py-2">
                    <div class="relative">
                        <input type="text" placeholder="Cari file..." 
                               class="w-full pl-10 pr-4 py-2 border border-secondary-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-2.5 text-secondary-400"></i>
                    </div>
                </div>
            </div>
        </div>
    </nav>
