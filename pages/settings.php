<?php
require_once '../config/auth.php';
requireAuth();
require_once '../config/database.php';

$pdo = getDBConnection();
$user_id = $_SESSION['user_id'] ?? null;
$settings = [
    'dark_mode' => false,
    'language' => 'id',
    'notifications' => true
];
$msg = '';
if ($pdo && $user_id) {
    // Handle update settings
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $dark_mode = isset($_POST['dark_mode']) ? 1 : 0;
        $language = $_POST['language'] ?? 'id';
        $notifications = isset($_POST['notifications']) ? 1 : 0;
        // Cek apakah sudah ada baris settings untuk user ini
        $stmt = $pdo->prepare('SELECT id FROM user_settings WHERE user_id = ?');
        $stmt->execute([$user_id]);
        if ($stmt->fetch()) {
            // Update
            $stmt = $pdo->prepare('UPDATE user_settings SET dark_mode = ?, language = ?, notifications = ? WHERE user_id = ?');
            $stmt->execute([$dark_mode, $language, $notifications, $user_id]);
        } else {
            // Insert
            $stmt = $pdo->prepare('INSERT INTO user_settings (user_id, dark_mode, language, notifications) VALUES (?, ?, ?, ?)');
            $stmt->execute([$user_id, $dark_mode, $language, $notifications]);
        }
        $msg = '<div style="color:green">Settings updated successfully.</div>';
    }
    // Ambil settings dari database
    $stmt = $pdo->prepare('SELECT dark_mode, language, notifications FROM user_settings WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    if ($row) {
        $settings['dark_mode'] = (bool)$row['dark_mode'];
        $settings['language'] = $row['language'];
        $settings['notifications'] = (bool)$row['notifications'];
    } else {
        // Insert default jika belum ada
        $stmt = $pdo->prepare('INSERT IGNORE INTO user_settings (user_id, dark_mode, language, notifications) VALUES (?, 0, "id", 1)');
        $stmt->execute([$user_id]);
    }
}
$page_title = 'Settings';
include '../components/header.php';
?>
<div class="max-w-lg mx-auto mt-10 bg-white rounded-xl shadow p-8">
    <h2 class="text-2xl font-bold mb-6">Settings</h2>
    <?= $msg ?>
    <form method="post">
        <div class="mb-6 flex items-center justify-between">
            <label class="font-medium">Dark Mode</label>
            <input type="checkbox" name="dark_mode" id="dark_mode_toggle" <?= $settings['dark_mode'] ? 'checked' : '' ?> class="h-5 w-5">
        </div>
        <div class="mb-6">
            <label class="font-medium block mb-2">Bahasa</label>
            <select name="language" class="w-full border rounded p-2">
                <option value="id" <?= $settings['language'] === 'id' ? 'selected' : '' ?>>Indonesia</option>
                <option value="en" <?= $settings['language'] === 'en' ? 'selected' : '' ?>>English</option>
            </select>
        </div>
        <div class="mb-6 flex items-center justify-between">
            <label class="font-medium">Notifikasi</label>
            <input type="checkbox" name="notifications" <?= $settings['notifications'] ? 'checked' : '' ?> class="h-5 w-5">
        </div>
        <button type="submit" class="btn-primary w-full">Simpan</button>
    </form>
</div>
<script>
// Terapkan dark mode langsung
const darkMode = <?= $settings['dark_mode'] ? 'true' : 'false' ?>;
if (darkMode) {
    document.documentElement.classList.add('dark');
    document.body.style.background = '#18181b';
} else {
    document.documentElement.classList.remove('dark');
    document.body.style.background = '';
}
document.getElementById('dark_mode_toggle').addEventListener('change', function() {
    if (this.checked) {
        document.documentElement.classList.add('dark');
        document.body.style.background = '#18181b';
    } else {
        document.documentElement.classList.remove('dark');
        document.body.style.background = '';
    }
});
</script>
<?php include '../components/footer.php'; ?> 