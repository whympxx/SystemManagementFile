<?php

return [
    // Awalan nomor arsip, contoh: ARS/2026/11/0001
    'prefix' => env('ARCHIVE_PREFIX', 'ARS'),

    // Ukuran maksimal per berkas (KB). Pastikan upload_max_filesize & post_max_size di php.ini >= nilai ini.
    'max_upload_kb' => (int) env('ARCHIVE_MAX_UPLOAD_KB', 20480),

    // Kuota penyimpanan per user (MB), hanya untuk tampilan di dashboard
    'quota_mb' => (int) env('ARCHIVE_QUOTA_MB', 2048),
];
