<x-layouts.app :title="$document->title">
    <div class="mb-4">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('documents.index') }}" class="text-sm text-slate-500 hover:text-slate-900">← Kembali</a>
    </div>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div class="flex min-w-0 items-start gap-4">
            <x-file-badge :ext="$document->extension" size="size-12" />
            <div class="min-w-0">
                <h1 class="text-xl font-semibold text-slate-900">{{ $document->title }}</h1>
                <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                    <span class="font-mono">{{ $document->archive_number }}</span> ·
                    <span>{{ $document->sizeHuman() }}</span> ·
                    <span>{{ $document->original_name }}</span>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <form method="POST" action="{{ route('documents.star', $document) }}">
                @csrf
                <button class="btn-secondary {{ $document->is_starred ? 'text-amber-600' : '' }}">
                    <x-icon name="star" class="size-4" /> {{ $document->is_starred ? 'Berbintang' : 'Bintangi' }}
                </button>
            </form>
            @if ($document->isPreviewable())
                <a href="{{ route('documents.preview', $document) }}" target="_blank" class="btn-secondary"><x-icon name="eye" class="size-4" /> Buka</a>
            @endif
            <a href="{{ route('documents.download', $document) }}" class="btn-primary"><x-icon name="download" class="size-4" /> Unduh</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-5">
        <div class="space-y-6 lg:col-span-3">
            {{-- Pratinjau --}}
            <div class="card overflow-hidden">
                @if ($document->isImage())
                    <img src="{{ route('documents.preview', $document) }}" alt="{{ $document->title }}" class="mx-auto max-h-[70vh]">
                @elseif ($document->extension === 'pdf')
                    <iframe src="{{ route('documents.preview', $document) }}" class="h-[70vh] w-full" title="Pratinjau PDF"></iframe>
                @elseif ($document->content_text)
                    <pre class="max-h-[70vh] overflow-auto whitespace-pre-wrap p-5 text-xs leading-relaxed text-slate-700">{{ \Illuminate\Support\Str::limit($document->content_text, 8000) }}</pre>
                @else
                    <div class="px-6 py-16 text-center text-sm text-slate-500">
                        <x-file-badge :ext="$document->extension" size="size-16" class="mx-auto mb-3" />
                        Pratinjau tidak tersedia untuk jenis berkas ini. Silakan unduh untuk membuka.
                    </div>
                @endif
            </div>

            {{-- Ringkasan AI --}}
            <div class="card p-5">
                <div class="mb-2 flex items-center justify-between">
                    <h2 class="flex items-center gap-2 font-semibold text-slate-900"><x-icon name="sparkles" class="size-5 text-violet-500" /> Ringkasan</h2>
                    <form method="POST" action="{{ route('documents.reanalyze', $document) }}">
                        @csrf
                        <button class="btn-ghost py-1 text-xs">Analisis ulang</button>
                    </form>
                </div>
                @if ($document->ai_summary)
                    <p class="text-sm leading-relaxed text-slate-700">{{ $document->ai_summary }}</p>
                @elseif ($aiEnabled)
                    <p class="text-sm text-slate-500">Belum ada ringkasan. Klik "Analisis ulang" untuk membuatnya.</p>
                @else
                    <p class="text-sm text-slate-500">Ringkasan otomatis aktif jika <code>AI_API_KEY</code> diisi di <code>.env</code>.</p>
                @endif
                @if ($document->analysis_error)
                    <p class="mt-2 text-xs text-amber-700">Catatan: {{ $document->analysis_error }} (kategori memakai kata kunci).</p>
                @endif
                <p class="mt-3 text-xs text-slate-400">
                    Kategori ditentukan:
                    {{ ['ai' => 'otomatis oleh AI', 'keyword' => 'otomatis dari kata kunci', 'manual' => 'manual'][$document->classified_by] ?? '—' }}
                    · {{ $document->content_text ? 'isi dokumen terindeks untuk pencarian' : 'isi dokumen tidak bisa dibaca (mis. hasil scan)' }}
                </p>
            </div>
        </div>

        {{-- Metadata --}}
        <div class="space-y-6 lg:col-span-2">
            <form method="POST" action="{{ route('documents.update', $document) }}" class="card space-y-4 p-5">
                @csrf @method('PUT')
                <h2 class="font-semibold text-slate-900">Informasi berkas</h2>
                <div>
                    <label class="label" for="title">Judul</label>
                    <input id="title" name="title" value="{{ old('title', $document->title) }}" required maxlength="200" class="input">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="category_id">Kategori</label>
                        <select id="category_id" name="category_id" class="input">
                            <option value="">—</option>
                            @foreach ($categories as $cat)<option value="{{ $cat->id }}" @selected($document->category_id === $cat->id)>{{ $cat->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="folder_id">Folder</label>
                        <select id="folder_id" name="folder_id" class="input">
                            <option value="">—</option>
                            @foreach ($folders as $folder)<option value="{{ $folder->id }}" @selected($document->folder_id === $folder->id)>{{ $folder->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="document_date">Tanggal dokumen</label>
                        <input id="document_date" type="date" name="document_date" value="{{ old('document_date', $document->document_date?->toDateString()) }}" class="input">
                    </div>
                    <div>
                        <label class="label" for="expires_at">Berlaku s/d</label>
                        <input id="expires_at" type="date" name="expires_at" value="{{ old('expires_at', $document->expires_at?->toDateString()) }}" class="input">
                    </div>
                </div>
                <div>
                    <label class="label" for="tags">Tag <span class="font-normal text-slate-400">(pisahkan dengan koma)</span></label>
                    <input id="tags" name="tags" value="{{ old('tags', $document->tags->pluck('name')->implode(', ')) }}" class="input" placeholder="mis. vendor, 2026, urgent">
                </div>
                <div>
                    <label class="label" for="description">Catatan</label>
                    <textarea id="description" name="description" rows="3" class="input" placeholder="Keterangan tambahan, nomor surat, pengirim…">{{ old('description', $document->description) }}</textarea>
                </div>
                <button class="btn-primary w-full">Simpan perubahan</button>
            </form>

            <div class="card divide-y divide-slate-100 text-sm">
                @foreach ([
                    'Nomor arsip' => $document->archive_number,
                    'Nama file asli' => $document->original_name,
                    'Jenis' => strtoupper($document->extension).' · '.$document->mime_type,
                    'Diunggah' => $document->created_at->translatedFormat('j F Y, H:i'),
                    'Terakhir diubah' => $document->updated_at->diffForHumans(),
                ] as $label => $value)
                    <div class="flex justify-between gap-4 px-5 py-2.5"><span class="text-slate-500">{{ $label }}</span><span class="truncate text-right text-slate-800">{{ $value }}</span></div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('Pindahkan berkas ini ke Sampah?')">
                @csrf @method('DELETE')
                <button class="btn-ghost w-full text-rose-600"><x-icon name="trash" class="size-4" /> Pindahkan ke Sampah</button>
            </form>
        </div>
    </div>
</x-layouts.app>
