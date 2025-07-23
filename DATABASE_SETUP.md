# Database Setup Documentation - FileManager Pro

## Status Database ✅

Database telah berhasil disetup dan terkoneksi dengan baik! 

### Konfigurasi Database

- **Host**: localhost
- **Database**: filemanager_pro
- **User**: root
- **Password**: (kosong/empty)
- **Charset**: utf8mb4

### Tabel yang Dibuat

1. **users** - Menyimpan data pengguna
   - Columns: id, username, email, password_hash, full_name, is_active, created_at, updated_at

2. **files** - Menyimpan informasi file yang diupload
   - Columns: id, filename, original_name, file_path, file_size, file_type, mime_type, upload_date, is_deleted, created_at, updated_at

3. **file_shares** - Menyimpan informasi sharing file
   - Columns: id, file_id, share_token, expires_at, download_count, max_downloads, is_active, created_at

### Konfigurasi Upload

- **Upload Path**: ../uploads/
- **Max Upload Size**: 10 MB
- **Allowed Types**: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx, txt, zip, mp3, mp4, dll.

## Test User

Sistem telah membuat test user dengan kredensial:
- **Username**: testuser
- **Email**: test@example.com
- **Password**: password123

## Cara Menggunakan

### 1. Akses Login
- Buka browser dan akses: `http://localhost/ManagementSistemFile/login.php`
- Login dengan kredensial test user atau daftar akun baru

### 2. Fitur yang Tersedia
- **Dashboard**: Lihat statistik file dan storage
- **Upload File**: Upload file baru dengan drag & drop
- **File Manager**: Kelola semua file yang diupload
- **Search**: Cari file berdasarkan nama atau tipe

### 3. Struktur Direktori
```
ManagementSistemFile/
├── config/
│   ├── database.php    # Konfigurasi database
│   └── auth.php        # Fungsi autentikasi
├── components/
│   ├── header.php      # Header template
│   └── footer.php      # Footer template
├── pages/
│   ├── upload.php      # Halaman upload
│   └── file-manager.php # Halaman file manager
├── includes/
│   ├── upload.php      # Handler upload
│   └── get_files.php   # Handler get files
├── uploads/            # Direktori file upload
├── index.php           # Dashboard utama
├── login.php           # Halaman login
├── register.php        # Halaman registrasi
└── logout.php          # Handler logout
```

### 4. Keamanan
- Password di-hash menggunakan PHP password_hash()
- CSRF protection untuk semua form
- Rate limiting untuk login attempts
- Validasi file type dan size

## Troubleshooting

### Jika Database Error
1. Pastikan XAMPP MySQL service berjalan
2. Jalankan script test: `php test_database.php`
3. Cek error log di XAMPP control panel

### Jika Upload Error
1. Cek permission folder uploads/
2. Cek setting upload_max_filesize di php.ini
3. Cek setting post_max_size di php.ini

## Maintenance

- Database backup otomatis direkomendasikan
- Monitor ukuran direktori uploads/
- Log error disimpan di error_log PHP

---
✅ **Database siap digunakan!** Sistem file management sudah bisa digunakan dengan normal.
