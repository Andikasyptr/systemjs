<x-guest-layout>
    <h1 class="text-3xl font-extrabold text-neutral-950">Daftar Member</h1>
    <p class="mt-2 text-sm text-neutral-500">Gratis. Kumpulkan poin dan dapatkan diskon di setiap cucian.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5"
          x-data="{ show: false, loading: false }" @submit="loading = true">
        @csrf

        @foreach ([
            ['name',  'Nama lengkap', 'text',  'Nama Anda',      'name'],
            ['email', 'Email',        'email', 'nama@email.com', 'username'],
            ['phone', 'No. WhatsApp', 'text',  '08xxxxxxxxxx',   'tel'],
        ] as [$field, $label, $type, $ph, $ac])
            <div>
                <label for="{{ $field }}" class="mb-1.5 block text-sm font-semibold text-neutral-700">{{ $label }}</label>
                <input id="{{ $field }}" type="{{ $type }}" name="{{ $field }}" value="{{ old($field) }}" required
                       autocomplete="{{ $ac }}" placeholder="{{ $ph }}" @if ($loop->first) autofocus @endif
                       class="block w-full rounded-xl border-neutral-300 px-4 py-3 text-sm shadow-sm placeholder:text-neutral-400 focus:border-red-500 focus:ring-red-500 @error($field) border-red-500 @enderror">
                @error($field)<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        @endforeach

        <div>
            <label for="address" class="mb-1.5 block text-sm font-semibold text-neutral-700">Alamat</label>
            <textarea id="address" name="address" rows="2" required autocomplete="street-address"
                      placeholder="Jalan, nomor rumah, kelurahan, kota"
                      class="block w-full rounded-xl border-neutral-300 px-4 py-3 text-sm shadow-sm placeholder:text-neutral-400 focus:border-red-500 focus:ring-red-500 @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
            @error('address')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="branch_id" class="mb-1.5 block text-sm font-semibold text-neutral-700">Daftar sebagai member cabang</label>
            <select id="branch_id" name="branch_id" required
                    class="block w-full rounded-xl border-neutral-300 px-4 py-3 text-sm shadow-sm focus:border-red-500 focus:ring-red-500 @error('branch_id') border-red-500 @enderror">
                <option value="">- pilih cabang -</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->is_main ? 'Pusat' : 'Cabang' }} · {{ $b->name }}</option>
                @endforeach
            </select>
            @error('branch_id')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold text-neutral-700">Password</label>
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="new-password" placeholder="Min. 8 karakter"
                       class="block w-full rounded-xl border-neutral-300 px-4 py-3 text-sm shadow-sm placeholder:text-neutral-400 focus:border-red-500 focus:ring-red-500 @error('password') border-red-500 @enderror">
            </div>
            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-neutral-700">Ulangi password</label>
                <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" placeholder="Ketik ulang"
                       class="block w-full rounded-xl border-neutral-300 px-4 py-3 text-sm shadow-sm placeholder:text-neutral-400 focus:border-red-500 focus:ring-red-500">
            </div>
        </div>
        @error('password')<p class="-mt-3 text-sm text-red-600">{{ $message }}</p>@enderror

        <label class="flex cursor-pointer items-center gap-2 text-sm text-neutral-600">
            <input type="checkbox" x-model="show" class="h-4 w-4 rounded border-neutral-300 text-red-600 focus:ring-red-500">
            Tampilkan password
        </label>

        <button type="submit" :disabled="loading"
                class="flex w-full items-center justify-center rounded-xl bg-red-600 py-3 text-sm font-bold text-white shadow-lg shadow-red-600/30 transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70">
            <span x-text="loading ? 'Memproses...' : 'Buat Akun'">Buat Akun</span>
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-neutral-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-bold text-red-600 hover:text-red-700">Masuk</a>
    </p>
</x-guest-layout>