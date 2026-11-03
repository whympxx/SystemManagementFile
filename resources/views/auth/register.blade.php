<x-layouts.guest title="Daftar">
    <h2 class="text-2xl font-semibold text-slate-900">Buat akun</h2>
    <p class="mt-1 text-sm text-slate-500">Sudah punya akun? <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:underline">Masuk</a></p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
        @csrf
        <div>
            <label class="label" for="name">Nama lengkap</label>
            <input id="name" name="name" value="{{ old('name') }}" required autofocus class="input">
            @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required class="input">
            @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="password">Password</label>
            <input id="password" name="password" type="password" required class="input">
            <p class="mt-1 text-xs text-slate-500">Minimal 8 karakter, berisi huruf dan angka.</p>
            @error('password')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="password_confirmation">Ulangi password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required class="input">
        </div>
        <button class="btn-primary w-full">Daftar</button>
    </form>
</x-layouts.guest>
