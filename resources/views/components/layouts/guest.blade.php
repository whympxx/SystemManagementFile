<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Masuk' }} · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
<div class="grid min-h-screen lg:grid-cols-2">
    <div class="hidden flex-col justify-between bg-brand-700 p-12 text-white lg:flex">
        <div class="flex items-center gap-2 text-lg font-semibold">
            <span class="flex size-9 items-center justify-center rounded-lg bg-white/15"><x-icon name="folder" /></span>
            {{ config('app.name') }}
        </div>
        <div class="space-y-6">
            <h1 class="text-3xl font-semibold leading-tight">Simpan, pisahkan, dan temukan berkas kantor dalam hitungan detik.</h1>
            <ul class="space-y-3 text-brand-100">
                <li class="flex gap-3"><x-icon name="sparkles" class="size-5 shrink-0" /> Berkas otomatis dikategorikan: surat, faktur, kontrak, laporan, dan lainnya.</li>
                <li class="flex gap-3"><x-icon name="search" class="size-5 shrink-0" /> Cari sampai ke isi dokumen PDF & Word, bukan hanya nama file.</li>
                <li class="flex gap-3"><x-icon name="clock" class="size-5 shrink-0" /> Pengingat dokumen yang akan kedaluwarsa (kontrak, izin, tagihan).</li>
            </ul>
        </div>
        <p class="text-sm text-brand-100">Nomor arsip otomatis · Folder & tag · Sampah yang bisa dipulihkan</p>
    </div>
    <div class="flex items-center justify-center p-6">
        <div class="w-full max-w-sm">{{ $slot }}</div>
    </div>
</div>
</body>
</html>
