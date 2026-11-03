<x-layouts.app title="Dashboard">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Halo, {{ explode(' ', auth()->user()->name)[0] }} 👋</h1>
            <p class="text-sm text-slate-500">{{ now()->translatedFormat('l, j F Y') }} · Ringkasan arsip Anda</p>
        </div>
        <a href="{{ route('documents.create') }}" class="btn-primary"><x-icon name="upload" class="size-4" /> Upload Berkas</a>
    </div>

    @unless ($aiEnabled)
        <div class="mb-6 flex gap-3 rounded-xl bg-brand-50 p-4 text-sm text-brand-700 ring-1 ring-brand-100">
            <x-icon name="sparkles" class="size-5 shrink-0" />
            <div>
                Auto-kategori kata kunci sudah aktif. Untuk ringkasan otomatis dan klasifikasi yang lebih pintar
                isi <code class="rounded bg-white px-1">AI_API_KEY</code> di file <code class="rounded bg-white px-1">.env</code>.
            </div>
        </div>
    @endunless

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([
            ['Total berkas', number_format($total, 0, ',', '.'), 'files', route('documents.index')],
            ['Diunggah bulan ini', number_format($thisMonth, 0, ',', '.'), 'upload', route('documents.index', ['from' => now()->startOfMonth()->toDateString()])],
            ['Berbintang', number_format($starred, 0, ',', '.'), 'star', route('documents.index', ['starred' => 1])],
            ['Segera kedaluwarsa', $expiring->count(), 'clock', route('documents.index', ['expiring' => 1, 'sort' => 'expires'])],
        ] as [$label, $value, $icon, $url])
            <a href="{{ $url }}" class="card p-4 transition hover:ring-brand-300">
                <div class="flex items-center justify-between text-slate-400"><span class="text-sm text-slate-500">{{ $label }}</span><x-icon :name="$icon" /></div>
                <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $value }}</div>
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Baru diunggah</h2>
                <a href="{{ route('documents.index') }}" class="text-sm text-brand-600 hover:underline">Lihat semua</a>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($recent as $doc)
                    <li>
                        <a href="{{ route('documents.show', $doc) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50">
                            <x-file-badge :ext="$doc->extension" />
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm font-medium text-slate-900">{{ $doc->title }}</div>
                                <div class="text-xs text-slate-500">{{ $doc->archive_number }} · {{ $doc->created_at->diffForHumans() }}</div>
                            </div>
                            <x-category-chip :category="$doc->category" class="hidden sm:inline-flex" />
                        </a>
                    </li>
                @empty
                    <li class="px-5 py-12 text-center text-sm text-slate-500">
                        Belum ada berkas. <a href="{{ route('documents.create') }}" class="text-brand-600 hover:underline">Upload berkas pertama</a>.
                    </li>
                @endforelse
            </ul>
        </div>

        <div class="space-y-6">
            <div class="card p-5">
                <h2 class="font-semibold text-slate-900">Penyimpanan</h2>
                @php $pct = $quotaBytes ? min(100, round($totalSize / $quotaBytes * 100, 1)) : 0; @endphp
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand-600" style="width: {{ $pct }}%"></div></div>
                <p class="mt-2 text-sm text-slate-500">{{ \App\Models\Document::formatBytes($totalSize) }} dari {{ \App\Models\Document::formatBytes($quotaBytes) }} ({{ $pct }}%)</p>
            </div>

            <div class="card p-5">
                <h2 class="mb-3 font-semibold text-slate-900">Per kategori</h2>
                @php $max = max(1, $categories->max('documents_count')); @endphp
                <ul class="space-y-3">
                    @foreach ($categories as $cat)
                        <li>
                            <a href="{{ route('documents.index', ['category' => $cat->id]) }}" class="block">
                                <div class="flex justify-between text-sm"><span>{{ $cat->name }}</span><span class="text-slate-500">{{ $cat->documents_count }}</span></div>
                                <div class="mt-1 h-1.5 rounded-full bg-slate-100"><div class="h-full rounded-full" style="width: {{ $cat->documents_count / $max * 100 }}%; background: {{ $cat->hex() }}"></div></div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card p-5">
                <h2 class="mb-3 flex items-center gap-2 font-semibold text-slate-900"><x-icon name="clock" class="size-5 text-amber-500" /> Segera kedaluwarsa</h2>
                <ul class="space-y-2">
                    @forelse ($expiring as $doc)
                        <li>
                            <a href="{{ route('documents.show', $doc) }}" class="flex items-center justify-between gap-2 text-sm hover:text-brand-600">
                                <span class="truncate">{{ $doc->title }}</span>
                                <span @class(['chip shrink-0', 'bg-rose-100 text-rose-700' => $doc->expires_at->isPast(), 'bg-amber-100 text-amber-800' => ! $doc->expires_at->isPast()])>
                                    {{ $doc->expires_at->isPast() ? 'Lewat' : $doc->expires_at->translatedFormat('j M Y') }}
                                </span>
                            </a>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">Tidak ada dokumen yang kedaluwarsa dalam 30 hari.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-layouts.app>
