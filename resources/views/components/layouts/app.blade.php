<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 font-sans text-slate-800 antialiased" x-data="{ nav: false, newFolder: false }">
@php
    $f = request()->query();
    $isDocs = request()->routeIs('documents.index');
    $plain = $isDocs && ! array_intersect(array_keys($f), ['category', 'folder', 'starred', 'expiring', 'trash', 'tag']);
@endphp

{{-- Sidebar --}}
<div x-cloak x-show="nav" class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden" @click="nav = false"></div>
<aside :class="nav ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-40 flex w-72 flex-col border-r border-slate-200 bg-white transition-transform lg:translate-x-0">
    <div class="flex h-16 items-center gap-2 border-b border-slate-200 px-5">
        <span class="flex size-9 items-center justify-center rounded-lg bg-brand-600 text-white"><x-icon name="folder" class="size-5" /></span>
        <div>
            <div class="text-sm font-semibold text-slate-900">{{ config('app.name') }}</div>
            <div class="text-xs text-slate-500">Arsip digital kantor</div>
        </div>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4">
        <div class="space-y-1">
            <a href="{{ route('documents.create') }}" class="btn-primary mb-3 w-full"><x-icon name="upload" class="size-4" /> Upload Berkas</a>
            <a href="{{ route('dashboard') }}" @class(['nav-link', 'nav-active' => request()->routeIs('dashboard')])><x-icon name="home" /> Dashboard</a>
            <a href="{{ route('documents.index') }}" @class(['nav-link', 'nav-active' => $plain])><x-icon name="files" /> Semua Berkas</a>
            <a href="{{ route('documents.index', ['starred' => 1]) }}" @class(['nav-link', 'nav-active' => $isDocs && isset($f['starred'])])><x-icon name="star" /> Berbintang</a>
            <a href="{{ route('documents.index', ['expiring' => 1, 'sort' => 'expires']) }}" @class(['nav-link', 'nav-active' => $isDocs && isset($f['expiring'])])>
                <x-icon name="clock" /> Segera Kedaluwarsa
                @if ($sidebarExpiring > 0)<span class="ml-auto chip bg-amber-100 text-amber-800">{{ $sidebarExpiring }}</span>@endif
            </a>
            <a href="{{ route('documents.index', ['trash' => 1]) }}" @class(['nav-link', 'nav-active' => $isDocs && isset($f['trash'])])><x-icon name="trash" /> Sampah</a>
        </div>

        <div>
            <div class="mb-1 flex items-center justify-between px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">
                Kategori
                <a href="{{ route('categories.index') }}" class="hover:text-brand-600" title="Atur kategori"><x-icon name="cog" class="size-4" /></a>
            </div>
            @foreach ($sidebarCategories as $cat)
                <a href="{{ route('documents.index', ['category' => $cat->id]) }}"
                   @class(['nav-link', 'nav-active' => $isDocs && ($f['category'] ?? null) == $cat->id])>
                    <span class="size-2.5 rounded-full" style="background: {{ $cat->hex() }}"></span>
                    <span class="truncate">{{ $cat->name }}</span>
                    <span class="ml-auto text-xs text-slate-400">{{ $cat->documents_count }}</span>
                </a>
            @endforeach
        </div>

        <div>
            <div class="mb-1 flex items-center justify-between px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">
                Folder
                <button type="button" class="hover:text-brand-600" title="Folder baru" @click="newFolder = !newFolder"><x-icon name="plus" class="size-4" /></button>
            </div>
            <form x-cloak x-show="newFolder" method="POST" action="{{ route('folders.store') }}" class="mb-2 flex gap-2 px-3">
                @csrf
                <input name="name" required maxlength="100" class="input py-1.5" placeholder="Nama folder">
                <button class="btn-primary px-3 py-1.5">OK</button>
            </form>
            @forelse ($sidebarFolders as $folder)
                <a href="{{ route('documents.index', ['folder' => $folder->id]) }}"
                   @class(['nav-link', 'nav-active' => $isDocs && ($f['folder'] ?? null) == $folder->id])>
                    <x-icon name="folder" class="size-4 text-slate-400" />
                    <span class="truncate">{{ $folder->name }}</span>
                    <span class="ml-auto text-xs text-slate-400">{{ $folder->documents_count }}</span>
                </a>
            @empty
                <p class="px-3 text-xs text-slate-400">Belum ada folder. Folder cocok untuk memisahkan per divisi, proyek, atau tahun.</p>
            @endforelse
        </div>
    </nav>

    <div class="border-t border-slate-200 p-3">
        <div class="flex items-center gap-3 px-2">
            <span class="flex size-9 items-center justify-center rounded-full bg-slate-200 text-sm font-semibold text-slate-600">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <div class="min-w-0 flex-1">
                <div class="truncate text-sm font-medium">{{ auth()->user()->name }}</div>
                <div class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn-ghost p-2" title="Keluar"><x-icon name="logout" class="size-5" /></button>
            </form>
        </div>
    </div>
</aside>

{{-- Konten --}}
<div class="lg:pl-72">
    <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6">
        <button class="btn-ghost p-2 lg:hidden" @click="nav = true" aria-label="Menu"><x-icon name="menu" /></button>
        <form action="{{ route('documents.index') }}" class="relative max-w-xl flex-1">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
            <input type="search" name="q" value="{{ request('q') }}" class="input pl-9"
                   placeholder="Cari judul, nomor arsip, tag, atau isi dokumen…">
        </form>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6">
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 class="mb-4 flex items-center justify-between rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-200">
                {{ session('success') }}
                <button @click="show = false"><x-icon name="x" class="size-4" /></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-800 ring-1 ring-rose-200">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        {{ $slot }}
    </main>
</div>
</body>
</html>
