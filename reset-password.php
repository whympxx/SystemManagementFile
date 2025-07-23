<?php
require_once 'config/auth.php';
require_once 'config/database.php';

$errors = [];
$success = '';
$showForm = false;
$token = $_GET['token'] ?? '';

if ($token) {
    $pdo = getDBConnection();
    // Cari token di tabel password_resets dan join ke user
    $stmt = $pdo->prepare("SELECT pr.id AS reset_id, pr.user_id, pr.expires_at, u.username, u.email FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = ?");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    if ($reset && strtotime($reset['expires_at']) > time()) {
        $showForm = true;
        // Handle form submit
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $password2 = $_POST['password2'] ?? '';
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!verifyCSRFToken($csrfToken)) {
                $errors[] = 'Invalid security token. Please try again.';
            }
            if (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters.';
            }
            if ($password !== $password2) {
                $errors[] = 'Password confirmation does not match.';
            }
            if (empty($errors)) {
                // Update password user
                $hash = hashPassword($password);
                $stmtUpdate = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmtUpdate->execute([$hash, $reset['user_id']]);
                // Hapus token reset
                $pdo->prepare("DELETE FROM password_resets WHERE id = ?")->execute([$reset['reset_id']]);
                $success = 'Password has been reset successfully. <a href="login.php" class="text-blue-700 underline">Login</a>';
                $showForm = false;
            }
        }
    } else {
        $errors[] = 'Invalid or expired reset token.';
    }
} else {
    $errors[] = 'No reset token provided.';
}
$csrfToken = generateCSRFToken();

// Notifikasi jika diarahkan dari forgot-password.php
define('RESET_NOTIF_MSG', 'Link reset juga dikirim ke email Anda (jika email terdaftar).');
$showResetNotif = isset($_GET['notif']) || isset($_GET['reset-notif']);
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - FileManager Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'float': 'float 6s ease-in-out infinite',
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            backdrop-filter: blur(16px);
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="h-full font-sans bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <div class="absolute inset-0 bg-white bg-opacity-50">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, rgba(59,130,246,0.1) 1px, transparent 0); background-size: 40px 40px;"></div>
    </div>
    <div class="relative min-h-full flex">
        <div class="hidden lg:flex lg:w-1/2 gradient-bg items-center justify-center p-12 relative overflow-hidden">
            <div class="absolute top-10 left-10 w-20 h-20 bg-white bg-opacity-10 rounded-full animate-float"></div>
            <div class="absolute bottom-20 right-20 w-16 h-16 bg-white bg-opacity-10 rounded-full animate-float" style="animation-delay: -2s;"></div>
            <div class="absolute top-1/3 right-1/4 w-12 h-12 bg-white bg-opacity-10 rounded-full animate-float" style="animation-delay: -4s;"></div>
            <div class="text-center text-black z-10">
                <div class="mb-8 animate-slide-up">
                    <div class="w-24 h-24 mx-auto mb-6 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-unlock-alt text-4xl"></i>
                    </div>
                    <h1 class="text-5xl font-bold mb-4">Reset Password</h1>
                    <p class="text-xl text-black mb-8">Set a new password for your FileManager Pro account</p>
                </div>
                <div class="grid grid-cols-1 gap-6 max-w-md mx-auto animate-fade-in" style="animation-delay: 0.2s;">
                    <div class="glass-effect p-4 rounded-xl">
                        <i class="fas fa-key text-2xl mb-2 text-black"></i>
                        <h3 class="font-semibold mb-1">Strong Password</h3>
                        <p class="text-sm text-black">Choose a secure password for your account</p>
                    </div>
                    <div class="glass-effect p-4 rounded-xl">
                        <i class="fas fa-shield-alt text-2xl mb-2 text-black"></i>
                        <h3 class="font-semibold mb-1">Safe & Secure</h3>
                        <p class="text-sm text-black">Your account security is our priority</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
            <div class="max-w-md w-full space-y-8 animate-fade-in">
                <div class="text-center">
                    <div class="lg:hidden mb-8">
                        <div class="w-16 h-16 mx-auto mb-4 bg-primary-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-unlock-alt text-2xl text-white"></i>
                        </div>
                        <h1 class="text-2xl font-bold text-secondary-900">Reset Password</h1>
                    </div>
                    <h2 class="text-3xl font-bold text-secondary-900">Set New Password</h2>
                    <p class="mt-2 text-secondary-600">Enter and confirm your new password below</p>
                </div>
                <?php if ($showResetNotif): ?>
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded-xl mb-4 animate-fade-in">
                    <i class="fas fa-info-circle mr-2"></i> <?php echo RESET_NOTIF_MSG; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl animate-slide-up">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span><?php echo $success; ?></span>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl animate-slide-up">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle mr-2 mt-0.5 flex-shrink-0"></i>
                        <div>
                            <?php if (count($errors) === 1): ?>
                                <span><?php echo htmlspecialchars($errors[0]); ?></span>
                            <?php else: ?>
                                <ul class="list-disc list-inside space-y-1">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($showForm): ?>
                <form method="POST" class="space-y-6" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <div class="space-y-1">
                        <label for="password" class="block text-sm font-medium text-secondary-900">
                            New Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-secondary-700"></i>
                            </div>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="w-full pl-10 pr-4 py-3 border border-secondary-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition duration-200 bg-white text-secondary-900"
                                placeholder="Enter new password"
                                required
                                autocomplete="new-password"
                            >
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label for="password2" class="block text-sm font-medium text-secondary-900">
                            Confirm New Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-secondary-700"></i>
                            </div>
                            <input 
                                type="password" 
                                id="password2" 
                                name="password2" 
                                class="w-full pl-10 pr-4 py-3 border border-secondary-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition duration-200 bg-white text-secondary-900"
                                placeholder="Confirm new password"
                                required
                                autocomplete="new-password"
                            >
                        </div>
                    </div>
                    <button 
                        type="submit"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition duration-200 transform hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-key text-primary-500 group-hover:text-primary-400"></i>
                        </span>
                        Reset Password
                    </button>
                </form>
                <?php endif; ?>
                <div class="text-center">
                    <p class="text-secondary-800">
                        Remember your password?
                        <a href="login.php" class="font-medium text-primary-700 hover:text-primary-900 transition duration-150">
                            Back to login
                        </a>
                    </p>
                </div>
                <div class="text-center text-xs text-secondary-700">
                    <p>&copy; <?php echo date('Y'); ?> FileManager Pro. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var pw = document.getElementById('password');
            if (pw) pw.focus();
        });
    </script>
</body>
</html> 