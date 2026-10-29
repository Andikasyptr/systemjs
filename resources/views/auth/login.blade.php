<x-guest-layout title="Masuk">
    <h1 class="text-3xl font-extrabold text-neutral-950">Selamat datang kembali</h1>
    <p class="mt-2 text-sm text-neutral-500">Masuk ke akun Anda untuk melanjutkan.</p>

    <x-auth-session-status class="mt-5 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5"
          x-data="{ show: false, loading: false }" @submit="loading = true">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="mb-1.5 block text-sm font-semibold text-neutral-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   placeholder="nama@email.com"
                   class="block w-full rounded-xl border-neutral-300 px-4 py-3 text-sm shadow-sm placeholder:text-neutral-400 focus:border-red-500 focus:ring-red-500 @error('email') border-red-500 @enderror">
            @error('email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- Password --}}
        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <label for="password" class="block text-sm font-semibold text-neutral-700">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-red-600 hover:text-red-700">Lupa password?</a>
                @endif
            </div>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                       placeholder="Masukkan password"
                       class="block w-full rounded-xl border-neutral-300 py-3 pl-4 pr-20 text-sm shadow-sm placeholder:text-neutral-400 focus:border-red-500 focus:ring-red-500 @error('password') border-red-500 @enderror">
                <button type="button" @click="show = !show"
                        class="absolute inset-y-0 right-0 px-4 text-xs font-semibold text-neutral-500 hover:text-red-600"
                        x-text="show ? 'Sembunyikan' : 'Tampilkan'">Tampilkan</button>
            </div>
            @error('password')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- Remember --}}
        <label for="remember_me" class="flex cursor-pointer items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border-neutral-300 text-red-600 focus:ring-red-500">
            <span class="text-sm text-neutral-600">Ingat saya di perangkat ini</span>
        </label>

        {{-- Submit --}}
        <button type="submit" :disabled="loading"
                class="flex w-full items-center justify-center rounded-xl bg-red-600 py-3 text-sm font-bold text-white shadow-lg shadow-red-600/30 transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70">
            <span x-text="loading ? 'Memproses...' : 'Masuk'">Masuk</span>
        </button>
    </form>

    @if (Route::has('register'))
        <div class="relative my-8">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-neutral-200"></div></div>
            <p class="relative mx-auto w-fit bg-white px-3 text-xs uppercase tracking-wider text-neutral-400">Belum punya akun?</p>
        </div>
        <a href="{{ route('register') }}"
           class="flex w-full items-center justify-center rounded-xl border-2 border-neutral-950 py-3 text-sm font-bold text-neutral-950 transition hover:bg-neutral-950 hover:text-white">
            Daftar Member Gratis
        </a>
    @endif
</x-guest-layout>