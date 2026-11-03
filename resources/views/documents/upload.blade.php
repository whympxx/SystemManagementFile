<x-layouts.app title="Upload Berkas">
    <div x-data="uploader({ url: '{{ route('documents.store') }}', maxBytes: {{ $maxKb * 1024 }}, extensions: @js($extensions) })"
         @dragover.window.prevent="dragging = true" @drop.window.prevent="drop($event)">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-slate-900">Upload Berkas</h1>
            <p class="text-sm text-slate-500">
                Seret banyak berkas sekaligus. Setiap berkas otomatis mendapat nomor arsip dan
                {{ $aiEnabled ? 'dikategorikan serta diringkas oleh AI.' : 'dikategorikan berdasarkan nama & isinya.' }}
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <label @dragleave.prevent="dragging = false"
                       :class="dragging ? 'border-brand-500 bg-brand-50' : 'border-slate-300 bg-white hover:border-brand-400'"
                       class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-6 py-14 text-center transition">
                    <span class="flex size-14 items-center justify-center rounded-full bg-brand-100 text-brand-600"><x-icon name="upload" class="size-7" /></span>
                    <span class="mt-4 text-base font-medium text-slate-900">Seret & lepas berkas di sini</span>
                    <span class="mt-1 text-sm text-slate-500">atau <span class="text-brand-600 underline">pilih dari komputer</span></span>
                    <span class="mt-3 text-xs text-slate-400">PDF, Word, Excel, PowerPoint, gambar/scan, ZIP · maks {{ \App\Models\Document::formatBytes($maxKb * 1024) }} per berkas</span>
                    <input type="file" multiple class="sr-only" @change="pick($event)" accept="{{ collect($extensions)->map(fn ($e) => '.'.$e)->implode(',') }}">
                </label>

                <div x-cloak x-show="items.length" class="card">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                        <span class="text-sm font-medium"><span x-text="doneCount"></span> / <span x-text="items.length"></span> berkas selesai</span>
                        <button type="button" class="text-sm text-slate-500 hover:text-slate-800" @click="clearDone()" x-show="!running">Bersihkan daftar</button>
                    </div>
                    <ul class="divide-y divide-slate-100">
                        <template x-for="item in items" :key="item.id">
                            <li class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="truncate text-sm font-medium text-slate-900" x-text="item.result?.title || item.name"></span>
                                            <span class="shrink-0 text-xs text-slate-400" x-text="formatSize(item.size)"></span>
                                        </div>
                                        <template x-if="item.state === 'uploading'">
                                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full bg-brand-600 transition-all" :style="`width: ${item.progress}%`"></div></div>
                                        </template>
                                        <p x-show="item.state === 'queued'" class="mt-1 text-xs text-slate-500">Menunggu…</p>
                                        <p x-show="item.state === 'analyzing'" class="mt-1 flex items-center gap-1 text-xs text-brand-600"><x-icon name="sparkles" class="size-4 animate-pulse" /> Menganalisis isi dokumen…</p>
                                        <p x-show="item.state === 'error'" class="mt-1 text-xs text-rose-600" x-text="item.error"></p>
                                        <template x-if="item.state === 'done'">
                                            <div class="mt-1 space-y-1">
                                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                                    <span class="font-mono text-slate-500" x-text="item.result.archive_number"></span>
                                                    <span class="chip" :style="`background: ${item.result.category_color}1a; color: ${item.result.category_color}`" x-text="item.result.category || 'Tanpa kategori'"></span>
                                                    <span x-show="item.result.classified_by === 'ai'" class="chip bg-violet-100 text-violet-700">AI</span>
                                                    <template x-for="tag in item.result.tags"><span class="chip bg-slate-100 text-slate-600" x-text="'#' + tag"></span></template>
                                                </div>
                                                <p x-show="item.result.summary" class="text-xs text-slate-600" x-text="item.result.summary"></p>
                                            </div>
                                        </template>
                                    </div>
                                    <a x-show="item.state === 'done'" :href="item.result?.url" class="btn-secondary px-3 py-1.5 text-xs">Buka</a>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            <div class="card h-fit space-y-4 p-5">
                <h2 class="font-semibold text-slate-900">Pengaturan upload</h2>
                <p class="text-xs text-slate-500">Berlaku untuk berkas yang ditambahkan setelah pengaturan diubah.</p>
                <div>
                    <label class="label">Kategori</label>
                    <select x-model="categoryId" class="input">
                        <option value="">✨ Otomatis (disarankan)</option>
                        @foreach ($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Simpan ke folder</label>
                    <select x-model="folderId" class="input">
                        <option value="">— Tanpa folder —</option>
                        @foreach ($folders as $folder)<option value="{{ $folder->id }}">{{ $folder->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Berlaku sampai <span class="font-normal text-slate-400">(opsional)</span></label>
                    <input type="date" x-model="expiresAt" class="input">
                    <p class="mt-1 text-xs text-slate-500">Untuk kontrak, izin, atau tagihan. Anda akan diingatkan 30 hari sebelumnya.</p>
                </div>
                <a href="{{ route('documents.index') }}" class="btn-secondary w-full">Lihat semua berkas</a>
            </div>
        </div>
    </div>
</x-layouts.app>
