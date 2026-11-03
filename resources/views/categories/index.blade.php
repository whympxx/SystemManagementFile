<x-layouts.app title="Atur Kategori">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Atur Kategori</h1>
        <p class="text-sm text-slate-500">
            Kata kunci dipakai untuk mengelompokkan berkas secara otomatis. Contoh: berkas bernama
            <em>"Invoice PT Maju Maret.pdf"</em> masuk ke kategori yang punya kata kunci <em>invoice</em>.
        </p>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-3 lg:col-span-2">
            @foreach ($categories as $cat)
                <form method="POST" action="{{ route('categories.update', $cat) }}" class="card p-4" x-data="{ color: '{{ $cat->color }}' }">
                    @csrf @method('PUT')
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="size-3 rounded-full" :style="`background: ${@js($colors)[color]}`"></span>
                        <input name="name" value="{{ $cat->name }}" required maxlength="60" class="input w-auto flex-1 font-medium">
                        <select name="color" x-model="color" class="input w-32">
                            @foreach ($colors as $key => $hex)<option value="{{ $key }}">{{ ucfirst($key) }}</option>@endforeach
                        </select>
                        <a href="{{ route('documents.index', ['category' => $cat->id]) }}" class="text-sm text-slate-500 hover:text-brand-600">{{ $cat->documents_count }} berkas</a>
                    </div>
                    <div class="mt-3">
                        <input name="keywords" value="{{ $cat->keywords }}" class="input" placeholder="{{ $cat->is_fallback ? 'Kategori cadangan: berkas yang tidak cocok dengan kategori lain masuk ke sini' : 'kata kunci, dipisah koma' }}">
                    </div>
                    <div class="mt-3 flex justify-end gap-2">
                        @unless ($cat->is_fallback)
                            <button form="delete-{{ $cat->id }}" class="btn-ghost text-rose-600">Hapus</button>
                        @endunless
                        <button class="btn-secondary">Simpan</button>
                    </div>
                </form>
                @unless ($cat->is_fallback)
                    <form id="delete-{{ $cat->id }}" method="POST" action="{{ route('categories.destroy', $cat) }}" class="hidden"
                          onsubmit="return confirm('Hapus kategori {{ $cat->name }}? Berkasnya dipindah ke Lainnya.')">
                        @csrf @method('DELETE')
                    </form>
                @endunless
            @endforeach
        </div>

        <form method="POST" action="{{ route('categories.store') }}" class="card h-fit space-y-4 p-5">
            @csrf
            <h2 class="font-semibold text-slate-900">Kategori baru</h2>
            <div><label class="label">Nama</label><input name="name" required maxlength="60" class="input" placeholder="mis. Proposal Proyek"></div>
            <div>
                <label class="label">Warna</label>
                <select name="color" class="input">@foreach ($colors as $key => $hex)<option value="{{ $key }}">{{ ucfirst($key) }}</option>@endforeach</select>
            </div>
            <div><label class="label">Kata kunci</label><input name="keywords" class="input" placeholder="proposal, penawaran, rfq"></div>
            <button class="btn-primary w-full"><x-icon name="plus" class="size-4" /> Tambah kategori</button>
        </form>
    </div>
</x-layouts.app>
