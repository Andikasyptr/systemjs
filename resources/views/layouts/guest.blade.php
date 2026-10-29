<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Masuk' }} - {{ config('app.name', 'Clean Shoes Jejak Suci') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .auth-bg { background: radial-gradient(circle at 15% 10%, rgba(220,38,38,.55) 0%, transparent 45%), radial-gradient(circle at 95% 90%, rgba(153,27,27,.5) 0%, transparent 45%), #0a0a0a; }
        .dotgrid { background-image: radial-gradient(rgba(255,255,255,.10) 1px, transparent 1px); background-size: 22px 22px; }
    </style>
</head>
<body class="bg-white font-sans text-neutral-800 antialiased">
<div class="grid min-h-screen lg:grid-cols-2">

    {{-- Panel brand (desktop) --}}
    <aside class="auth-bg relative hidden overflow-hidden text-white lg:flex lg:flex-col lg:justify-between lg:p-12">
        <div class="dotgrid absolute inset-0"></div>

        <a href="{{ url('/') }}" class="relative flex items-center gap-3">
            <span class="grid h-12 w-12 place-items-center rounded-xl bg-red-600 text-2xl shadow-lg">👟</span>
            <span class="leading-none">
                <span class="block text-2xl font-black uppercase tracking-tight">Clean Shoes</span>
                <span class="mt-1 block text-[11px] font-semibold uppercase tracking-[0.3em] text-red-300">Jejak Suci</span>
            </span>
        </a>

        <div class="relative max-w-md">
            <h2 class="text-4xl font-extrabold leading-tight">
                Sepatu, Tas & Helm<br><span class="text-red-500">Bersih Seperti Baru.</span>
            </h2>
            <p class="mt-4 text-neutral-300">
                Masuk untuk memantau pesanan, melihat poin member, dan mendapatkan diskon eksklusif.
            </p>

            <ul class="mt-8 space-y-4 text-sm">
                @foreach ([
                    ['📲', 'Update status via WhatsApp', 'Dikabari di setiap tahap pencucian.'],
                    ['🏅', 'Poin & diskon member', 'Naik tier otomatis sampai diskon 10%.'],
                    ['🔎', 'Riwayat pesanan lengkap', 'Pantau kapan saja dari akun Anda.'],
                ] as [$icon, $title, $desc])
                    <li class="flex items-start gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white/10 ring-1 ring-white/20">{{ $icon }}</span>
                        <span><b class="block">{{ $title }}</b><span class="text-neutral-400">{{ $desc }}</span></span>
                    </li>
                @endforeach
            </ul>
        </div>

        <p class="relative text-xs text-neutral-500">© {{ date('Y') }} Clean Shoes Jejak Suci · Jonggol & Cikarang</p>
    </aside>

    {{-- Panel form --}}
    <main class="flex flex-col justify-center px-6 py-10 sm:px-12 lg:px-16">
        <div class="mx-auto w-full max-w-md">
            {{-- Logo (mobile) --}}
            <a href="{{ url('/') }}" class="mb-8 flex items-center gap-3 lg:hidden">
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-red-600 text-xl text-white">👟</span>
                <span class="leading-none">
                    <span class="block text-xl font-black uppercase tracking-tight text-neutral-950">Clean Shoes</span>
                    <span class="mt-1 block text-[10px] font-semibold uppercase tracking-[0.3em] text-red-600">Jejak Suci</span>
                </span>
            </a>

            {{ $slot }}

            <p class="mt-10 text-center text-sm text-neutral-500">
                <a href="{{ url('/') }}" class="font-medium text-neutral-600 hover:text-red-600">← Kembali ke beranda</a>
            </p>
        </div>
    </main>
</div>
</body>
</html>