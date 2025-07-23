<?php
require_once '../config/auth.php';
require_once '../config/database.php';
requireAuth();

// Ambil data user dari database secara real-time
$pdo = getDBConnection();
$user = [
    'name' => '',
    'email' => '',
    'profile_photo' => null
];
if ($pdo && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT full_name, email, profile_photo FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();
    if ($row) {
        $user['name'] = $row['full_name'];
        $user['email'] = $row['email'];
        $user['profile_photo'] = $row['profile_photo'];
    }
}

// Handle update profile
$profileMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $profilePhotoName = $user['profile_photo'];
    // Handle profile photo upload
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file = $_FILES['profile_photo'];
        $mimeType = mime_content_type($file['tmp_name']);
        if (in_array($mimeType, $allowedTypes)) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $newFileName = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/profile_photos/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $uploadPath = $uploadDir . $newFileName;
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $profilePhotoName = 'profile_photos/' . $newFileName;
            } else {
                $profileMsg = '<div style="color:red">Failed to upload profile photo.</div>';
            }
        } else {
            $profileMsg = '<div style="color:red">Invalid image type. Only JPG, PNG, GIF, WEBP allowed.</div>';
        }
    }
    if ($name && $email && empty($profileMsg)) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare('UPDATE users SET full_name = ?, email = ?, profile_photo = ? WHERE id = ?');
        if ($stmt->execute([$name, $email, $profilePhotoName, $_SESSION['user_id']])) {
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $profileMsg = '<div style="color:green">Profile updated successfully.</div>';
        } else {
            $profileMsg = '<div style="color:red">Failed to update profile.</div>';
        }
    } elseif (empty($profileMsg)) {
        $profileMsg = '<div style="color:red">Name and email cannot be empty.</div>';
    }
}
// Handle change password
$pwdMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($new !== $confirm) {
        $pwdMsg = '<div style="color:red">New passwords do not match.</div>';
    } else {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch();
        if ($row && password_verify($old, $row['password_hash'])) {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            if ($stmt->execute([$newHash, $_SESSION['user_id']])) {
                $pwdMsg = '<div style="color:green">Password changed successfully.</div>';
            } else {
                $pwdMsg = '<div style="color:red">Failed to change password.</div>';
            }
        } else {
            $pwdMsg = '<div style="color:red">Old password is incorrect.</div>';
        }
    }
}
$page_title = 'Profile';
include '../components/header.php';
?>
<div class="max-w-lg mx-auto mt-10 bg-white rounded-xl shadow p-8">
    <h2 class="text-2xl font-bold mb-6">Profile</h2>
    <?php if (!empty($user['profile_photo'])): ?>
        <div class="flex justify-center mb-4">
            <img src="../uploads/<?= htmlspecialchars($user['profile_photo']) ?>" alt="Profile Photo" class="w-24 h-24 rounded-full object-cover border" />
        </div>
    <?php endif; ?>
    <?= $profileMsg ?>
    <form method="post" class="mb-8" enctype="multipart/form-data">
        <div class="mb-4">
            <label class="block mb-1 font-medium">Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="w-full border rounded p-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-medium">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full border rounded p-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-medium">Foto Profil</label>
            <input type="file" name="profile_photo" accept="image/*" class="w-full border rounded p-2">
        </div>
        <button type="submit" name="update_profile" class="btn-primary w-full">Simpan</button>
    </form>
    <h3 class="text-xl font-bold mb-4">Ganti Password</h3>
    <?= $pwdMsg ?>
    <form method="post">
        <div class="mb-4">
            <label class="block mb-1 font-medium">Password Lama</label>
            <input type="password" name="old_password" class="w-full border rounded p-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-medium">Password Baru</label>
            <input type="password" name="new_password" class="w-full border rounded p-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-medium">Konfirmasi Password Baru</label>
            <input type="password" name="confirm_password" class="w-full border rounded p-2" required>
        </div>
        <button type="submit" name="change_password" class="btn-secondary w-full">Ganti Password</button>
    </form>
</div>
<?php include '../components/footer.php'; ?> 