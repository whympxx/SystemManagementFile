<x-layouts.guest title="Masuk">
    <h2 class="text-2xl font-semibold text-slate-900">Masuk</h2>
    <p class="mt-1 text-sm text-slate-500">Belum punya akun? <a href="{{ route('register') }}" class="font-medium text-brand-600 hover:underline">Daftar</a></p>

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-4">
        @csrf
        <div>
            <label class="label" for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="input">
            @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="password">Password</label>
            <input id="password" name="password" type="password" required class="input">
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" class="rounded border-slate-300"> Ingat saya
        </label>
        <button class="btn-primary w-full">Masuk</button>
    </form>
</x-layouts.guest>
