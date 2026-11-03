@php
    $activeCategory = $categories->firstWhere('id', $filters['category'] ?? null);
    $activeFolder = $folders->firstWhere('id', $filters['folder'] ?? null);
    $heading = match (true) {
        $trash => 'Sampah',
        ! empty($filters['starred']) => 'Berbintang',
        ! empty($filters['expiring']) => 'Segera Kedaluwarsa',
        (bool) $activeCategory => $activeCategory->name,
        (bool) $activeFolder => 'Folder: '.$activeFolder->name,
        ! empty($filters['tag']) => '#'.$filters['tag'],
        ! empty($filters['q']) => 'Hasil pencarian "'.$filters['q'].'"',
        default => 'Semua Berkas',
    };
    $hasAdvanced = ! empty($filters['type']) || ! empty($filters['from']) || ! empty($filters['to']);
    $query = request()->except('page');
@endphp
<x-layouts.app :title="$heading">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ $heading }}</h1>
            <p class="text-sm text-slate-500">{{ $documents->total() }} berkas{{ $trash ? ' · berkas di sini bisa dipulihkan atau dihapus permanen' : '' }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if ($activeFolder)
                <div x-data="{ rename: false }" class="flex items-center gap-2">
                    <form x-show="rename" x-cloak method="POST" action="{{ route('folders.update', $activeFolder) }}" class="flex gap-2">
                        @csrf @method('PUT')
                        <input name="name" value="{{ $activeFolder->name }}" class="input py-1.5" required>
                        <button class="btn-primary py-1.5">Simpan</button>
                    </form>
                    <button x-show="!rename" @click="rename = true" class="btn-secondary">Ganti nama</button>
                    <form method="POST" action="{{ route('folders.destroy', $activeFolder) }}" onsubmit="return confirm('Hapus folder ini? Berkas di dalamnya tetap tersimpan.')">
                        @csrf @method('DELETE')
                        <button class="btn-ghost text-rose-600">Hapus folder</button>
                    </form>
                </div>
            @endif
            <div class="flex rounded-lg ring-1 ring-slate-300">
                <a href="{{ route('documents.index', array_merge($query, ['view' => 'list'])) }}" @class(['rounded-l-lg p-2', 'bg-slate-100 text-slate-900' => $view === 'list', 'bg-white text-slate-400' => $view !== 'list']) title="Daftar"><x-icon name="list" class="size-4" /></a>
                <a href="{{ route('documents.index', array_merge($query, ['view' => 'grid'])) }}" @class(['rounded-r-lg p-2', 'bg-slate-100 text-slate-900' => $view === 'grid', 'bg-white text-slate-400' => $view !== 'grid']) title="Kotak"><x-icon name="grid" class="size-4" /></a>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('documents.index') }}" class="card mb-4 p-4" x-data="{ more: {{ $hasAdvanced ? 'true' : 'false' }} }">
        @foreach (['starred', 'expiring', 'trash', 'view'] as $keep)
            @if (request()->filled($keep))<input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}">@endif
        @endforeach
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Kata kunci…" class="input lg:col-span-2">
            <select name="category" class="input" onchange="this.form.submit()">
                <option value="">Semua kategori</option>
                @foreach ($categories as $cat)<option value="{{ $cat->id }}" @selected(($filters['category'] ?? '') == $cat->id)>{{ $cat->name }}</option>@endforeach
                <option value="none" @selected(($filters['category'] ?? '') === 'none')>Belum dikategorikan</option>
            </select>
            <select name="folder" class="input" onchange="this.form.submit()">
                <option value="">Semua folder</option>
                @foreach ($folders as $folder)<option value="{{ $folder->id }}" @selected(($filters['folder'] ?? '') == $folder->id)>{{ $folder->name }}</option>@endforeach
            </select>
            <select name="sort" class="input" onchange="this.form.submit()">
                @foreach (['newest' => 'Terbaru', 'oldest' => 'Terlama', 'title' => 'Judul A–Z', 'size' => 'Ukuran terbesar', 'expires' => 'Kedaluwarsa terdekat', 'archive' => 'Nomor arsip'] as $key => $label)
                    <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div x-show="more" x-cloak class="mt-3 grid gap-3 sm:grid-cols-3">
            <div>
                <label class="label">Jenis berkas</label>
                <select name="type" class="input">
                    <option value="">Semua jenis</option>
                    @foreach (\App\Models\Document::TYPE_GROUPS as $key => $group)<option value="{{ $key }}" @selected(($filters['type'] ?? '') === $key)>{{ $group['label'] }}</option>@endforeach
                </select>
            </div>
            <div><label class="label">Diunggah dari</label><input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="input"></div>
            <div><label class="label">Sampai</label><input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="input"></div>
        </div>
        <div class="mt-3 flex flex-wrap items-center gap-2">
            <button class="btn-primary py-1.5"><x-icon name="search" class="size-4" /> Terapkan</button>
            <button type="button" class="btn-ghost py-1.5" @click="more = !more"><x-icon name="filter" class="size-4" /> <span x-text="more ? 'Sembunyikan filter' : 'Filter lainnya'"></span></button>
            @if (array_filter($filters))
                <a href="{{ route('documents.index', array_intersect_key($query, array_flip(['trash', 'view']))) }}" class="text-sm text-slate-500 hover:text-slate-900">Reset filter</a>
            @endif
        </div>
        @if ($tags->isNotEmpty())
            <div class="mt-3 flex flex-wrap gap-1.5 border-t border-slate-100 pt-3">
                @foreach ($tags as $tag)
                    <a href="{{ route('documents.index', array_merge($query, ['tag' => ($filters['tag'] ?? null) === $tag->name ? null : $tag->name])) }}"
                       @class(['chip', 'bg-brand-600 text-white' => ($filters['tag'] ?? null) === $tag->name, 'bg-slate-100 text-slate-600 hover:bg-slate-200' => ($filters['tag'] ?? null) !== $tag->name])>
                        #{{ $tag->name }} <span class="opacity-60">{{ $tag->documents_count }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </form>

    @if ($documents->isEmpty())
        <div class="card px-6 py-16 text-center">
            <x-icon name="files" class="mx-auto size-10 text-slate-300" />
            <p class="mt-3 text-slate-600">{{ $trash ? 'Sampah kosong.' : 'Tidak ada berkas yang cocok.' }}</p>
            @unless ($trash)<a href="{{ route('documents.create') }}" class="btn-primary mt-4">Upload berkas</a>@endunless
        </div>
    @else
        <form id="bulk" method="POST" action="{{ route('documents.bulk') }}" x-data="bulkSelect" @submit="if ($event.submitter?.dataset.confirm && !confirm($event.submitter.dataset.confirm)) $event.preventDefault()">
            @csrf
            <template x-for="id in selected" :key="id"><input type="hidden" name="ids[]" :value="id"></template>

            {{-- Aksi massal --}}
            <div x-cloak x-show="selected.length" class="sticky top-16 z-10 mb-3 flex flex-wrap items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm text-white shadow-lg">
                <span class="mr-2 font-medium"><span x-text="selected.length"></span> dipilih</span>
                @if ($trash)
                    <button name="action" value="restore" class="btn-secondary py-1">Pulihkan</button>
                    <button name="action" value="force" data-confirm="Hapus permanen berkas terpilih? Tindakan ini tidak bisa dibatalkan." class="btn-danger py-1">Hapus permanen</button>
                @else
                    <select name="category_id" class="input w-auto py-1 text-slate-900">
                        @foreach ($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                    </select>
                    <button name="action" value="category" class="btn-secondary py-1">Ubah kategori</button>
                    <select name="folder_id" class="input w-auto py-1 text-slate-900">
                        <option value="">Tanpa folder</option>
                        @foreach ($folders as $folder)<option value="{{ $folder->id }}">{{ $folder->name }}</option>@endforeach
                    </select>
                    <button name="action" value="folder" class="btn-secondary py-1">Pindah folder</button>
                    <button name="action" value="star" class="btn-secondary py-1">Bintangi</button>
                    <button name="action" value="delete" data-confirm="Pindahkan berkas terpilih ke Sampah?" class="btn-danger py-1">Hapus</button>
                @endif
                <button type="button" class="ml-auto text-slate-300 hover:text-white" @click="selected = []">Batal</button>
            </div>

            @if ($view === 'grid')
                <div class="mb-2 flex items-center gap-2 px-1 text-sm text-slate-500">
                    <input type="checkbox" class="rounded border-slate-300" @change="toggleAll($event)"> Pilih semua di halaman ini
                </div>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($documents as $doc)
                        <div class="card group relative overflow-hidden" :class="selected.includes('{{ $doc->id }}') && 'ring-2 ring-brand-500'">
                            <input type="checkbox" value="{{ $doc->id }}" data-doc-id x-model="selected" class="absolute left-3 top-3 z-10 rounded border-slate-300">
                            <a href="{{ $trash ? '#' : route('documents.show', $doc) }}" class="block">
                                <div class="flex aspect-[4/3] items-center justify-center bg-slate-100">
                                    @if ($doc->isImage() && ! $trash)
                                        <img src="{{ route('documents.preview', $doc) }}" alt="" loading="lazy" class="size-full object-cover">
                                    @else
                                        <x-file-badge :ext="$doc->extension" size="size-14" class="text-xs" />
                                    @endif
                                </div>
                                <div class="space-y-1.5 p-3">
                                    <div class="truncate text-sm font-medium text-slate-900" title="{{ $doc->title }}">{{ $doc->title }}</div>
                                    <div class="font-mono text-xs text-slate-400">{{ $doc->archive_number }}</div>
                                    <x-category-chip :category="$doc->category" />
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-medium uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="w-10 px-4 py-3"><input type="checkbox" class="rounded border-slate-300" @change="toggleAll($event)"></th>
                                <th class="px-2 py-3">Berkas</th>
                                <th class="hidden px-4 py-3 md:table-cell">Kategori</th>
                                <th class="hidden px-4 py-3 lg:table-cell">Folder</th>
                                <th class="hidden px-4 py-3 sm:table-cell">{{ $sort === 'expires' || ! empty($filters['expiring']) ? 'Berlaku s/d' : 'Diunggah' }}</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($documents as $doc)
                                <tr class="hover:bg-slate-50" :class="selected.includes('{{ $doc->id }}') && 'bg-brand-50'">
                                    <td class="px-4 py-3"><input type="checkbox" value="{{ $doc->id }}" data-doc-id x-model="selected" class="rounded border-slate-300"></td>
                                    <td class="px-2 py-3">
                                        <div class="flex items-center gap-3">
                                            <x-file-badge :ext="$doc->extension" />
                                            <div class="min-w-0">
                                                <a href="{{ $trash ? '#' : route('documents.show', $doc) }}" class="block max-w-md truncate font-medium text-slate-900 hover:text-brand-600">
                                                    @if ($doc->is_starred)<span class="text-amber-500">★</span>@endif {{ $doc->title }}
                                                </a>
                                                <div class="flex flex-wrap items-center gap-x-2 text-xs text-slate-500">
                                                    <span class="font-mono">{{ $doc->archive_number }}</span>
                                                    <span>{{ $doc->sizeHuman() }}</span>
                                                    @foreach ($doc->tags->take(3) as $tag)<span class="text-slate-400">#{{ $tag->name }}</span>@endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="hidden px-4 py-3 md:table-cell"><x-category-chip :category="$doc->category" /></td>
                                    <td class="hidden px-4 py-3 text-slate-500 lg:table-cell">{{ $doc->folder?->name ?? '—' }}</td>
                                    <td class="hidden whitespace-nowrap px-4 py-3 text-slate-500 sm:table-cell">
                                        @if ($sort === 'expires' || ! empty($filters['expiring']))
                                            @if ($doc->expires_at)
                                                <span @class(['chip', 'bg-rose-100 text-rose-700' => $doc->expires_at->isPast(), 'bg-amber-100 text-amber-800' => ! $doc->expires_at->isPast() && $doc->isExpiringSoon(), 'bg-slate-100 text-slate-600' => ! $doc->isExpiringSoon()])>{{ $doc->expires_at->translatedFormat('j M Y') }}</span>
                                            @else — @endif
                                        @else
                                            {{ $doc->created_at->translatedFormat('j M Y') }}
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        @if ($trash)
                                            <button form="restore-{{ $doc->id }}" class="btn-ghost p-1.5" title="Pulihkan"><x-icon name="restore" class="size-4" /></button>
                                        @else
                                            @if ($doc->isPreviewable())
                                                <a href="{{ route('documents.preview', $doc) }}" target="_blank" class="btn-ghost p-1.5" title="Pratinjau"><x-icon name="eye" class="size-4" /></a>
                                            @endif
                                            <a href="{{ route('documents.download', $doc) }}" class="btn-ghost p-1.5" title="Unduh"><x-icon name="download" class="size-4" /></a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </form>

        @if ($trash)
            @foreach ($documents as $doc)
                <form id="restore-{{ $doc->id }}" method="POST" action="{{ route('documents.restore', $doc) }}" class="hidden">@csrf</form>
            @endforeach
        @endif

        <div class="mt-4">{{ $documents->links() }}</div>
    @endif
</x-layouts.app>
