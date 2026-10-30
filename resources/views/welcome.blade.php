@php
    $brand     = $site['brand'];
    $openHours = $site['open_hours'];

    // Rapikan nomor: 0812... -> 62812...
    $normPhone = function ($n) {
        $n = preg_replace('/\D/', '', (string) $n);
        return str_starts_with($n, '0') ? '62' . substr($n, 1) : $n;
    };

    // Nomor utama = WhatsApp cabang pusat (cadangan: nomor di Teks & Kontak)
    $mainBranch = $branches->firstWhere('is_main', true) ?? $branches->first();
    $waNumber   = $normPhone($mainBranch?->whatsapp) ?: $normPhone($site['wa_number']);

    $waLink = fn (string $text, ?string $num = null) => 'https://wa.me/' . ($num ?: $waNumber) . '?text=' . rawurlencode($text);

    $catMeta = [
        'sepatu' => ['label' => 'Sepatu', 'icon' => '👟', 'desc' => 'Sneakers, kulit, suede, canvas, hingga sepatu formal.'],
        'tas'    => ['label' => 'Tas',    'icon' => '👜', 'desc' => 'Tas ransel, tote, kulit, dan tas kerja harian.'],
        'helm'   => ['label' => 'Helm',   'icon' => '⛑️', 'desc' => 'Bagian luar, busa dalam, hingga visor kembali jernih.'],
    ];

    $estimatorServices = $services->flatten()->map(fn ($s) => [
        'id' => $s->id, 'name' => $s->name, 'category' => $s->category,
        'price' => (int) $s->price, 'days' => (int) $s->duration_days,
    ])->values();

    $promo = $site['promo_active'] == '1' && $site['promo_text'];

    // Staf (admin/kasir/owner) diarahkan ke dashboard, customer & tamu ke form pesan
    $isStaff = auth()->check() && auth()->user()->role !== 'customer';
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $brand }} | Jasa Cuci Sepatu, Tas & Helm</title>
    <meta name="description" content="{{ $site['meta_description'] }}">
    <meta name="google-site-verification" content="tErh83ez1uHx09wPUAWDr4h3k3GFrlcHD-hBuFTP2No" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <script>document.documentElement.classList.add('js');</script>
    <style>
        [x-cloak] { display: none !important; }
        .js .reveal { opacity: 0; transform: translateY(24px); transition: opacity .7s ease, transform .7s ease; }
        .js .reveal.in { opacity: 1; transform: none; }
        .js .reveal[data-d="1"] { transition-delay: .1s } .js .reveal[data-d="2"] { transition-delay: .2s } .js .reveal[data-d="3"] { transition-delay: .3s }
        @keyframes floaty { 0%,100% { transform: translateY(0) } 50% { transform: translateY(-10px) } }
        .floaty { animation: floaty 5s ease-in-out infinite; }
        .hero-bg { background: radial-gradient(circle at 15% 10%, rgba(220,38,38,.55) 0%, transparent 40%), radial-gradient(circle at 95% 85%, rgba(153,27,27,.5) 0%, transparent 45%), #0a0a0a; }
        .dotgrid { background-image: radial-gradient(rgba(255,255,255,.10) 1px, transparent 1px); background-size: 22px 22px; }
        @media (prefers-reduced-motion: reduce) { .floaty { animation: none } .js .reveal { transition: none; opacity: 1; transform: none } }
    </style>
</head>

<body class="bg-white text-neutral-800 antialiased"
      x-data="{ open: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 20">

{{-- ================= NAVBAR ================= --}}
<header class="fixed inset-x-0 top-0 z-50 transition-all duration-300"
        :class="scrolled ? 'bg-white/90 shadow-sm backdrop-blur border-b border-neutral-200' : 'bg-transparent'">
    @if ($promo)
        <div class="bg-red-600 px-4 py-2 text-center text-xs font-semibold text-white sm:text-sm">{{ $site['promo_text'] }}</div>
    @endif
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5" :class="scrolled ? 'text-neutral-950' : 'text-white'">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-red-600 text-xl text-white shadow">👟</span>
            <span class="leading-none">
                <span class="block text-xl font-black uppercase tracking-tight">Clean Shoes</span>
                <span class="mt-1 block text-[10px] font-semibold uppercase tracking-[0.3em]" :class="scrolled ? 'text-red-600' : 'text-red-300'">Jejak Suci</span>
            </span>
        </a>

        <nav class="hidden items-center gap-7 text-sm font-medium lg:flex" :class="scrolled ? 'text-neutral-700' : 'text-neutral-200'">
            <a href="#layanan" class="hover:text-red-500">Layanan</a>
            @if ($gallery->count())<a href="#galeri" class="hover:text-red-500">Galeri</a>@endif
            <a href="#alur" class="hover:text-red-500">Alur Kerja</a>
            <a href="#estimasi" class="hover:text-red-500">Estimasi Harga</a>
            <a href="#member" class="hover:text-red-500">Membership</a>
            <a href="#cabang" class="hover:text-red-500">Cabang</a>
            <a href="#faq" class="hover:text-red-500">FAQ</a>
        </nav>

        <div class="hidden items-center gap-3 lg:flex">
            <a href="{{ route('track') }}" class="text-sm font-semibold" :class="scrolled ? 'text-neutral-700 hover:text-red-600' : 'text-white hover:text-red-300'">Cek Pesanan</a>
            @auth
                <a href="{{ route('dashboard') }}" class="rounded-full bg-red-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-red-700">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-semibold" :class="scrolled ? 'text-neutral-700 hover:text-red-600' : 'text-white hover:text-red-300'">Masuk</a>
                <a href="{{ route('register') }}" class="rounded-full bg-red-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-red-700">Daftar Member</a>
            @endauth
        </div>

        <button @click="open = !open" class="rounded-lg p-2 lg:hidden" :class="scrolled ? 'text-neutral-900' : 'text-white'" aria-label="Menu">
            <svg x-show="!open" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            <svg x-show="open" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </div>

    <div x-show="open" x-cloak x-transition @click.outside="open = false" class="border-t bg-white px-4 py-4 shadow-lg lg:hidden">
        <div class="grid gap-1 text-sm font-medium text-neutral-700">
            @foreach ([['#layanan','Layanan'],['#alur','Alur Kerja'],['#estimasi','Estimasi Harga'],['#member','Membership'],['#cabang','Cabang'],['#faq','FAQ']] as [$href, $lbl])
                <a href="{{ $href }}" @click="open = false" class="rounded-lg px-3 py-2 hover:bg-red-50">{{ $lbl }}</a>
            @endforeach
            @if ($gallery->count())<a href="#galeri" @click="open = false" class="rounded-lg px-3 py-2 hover:bg-red-50">Galeri</a>@endif
            <a href="{{ route('track') }}" class="rounded-lg px-3 py-2 hover:bg-red-50">Cek Pesanan</a>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-2">
            @auth
                <a href="{{ route('dashboard') }}" class="col-span-2 rounded-lg bg-red-600 py-2 text-center text-sm font-semibold text-white">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="rounded-lg border py-2 text-center text-sm font-semibold">Masuk</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-red-600 py-2 text-center text-sm font-semibold text-white">Daftar</a>
            @endauth
        </div>
    </div>
</header>

<main>
{{-- ================= HERO ================= --}}
<section class="hero-bg relative overflow-hidden pb-20 text-white lg:pb-28 {{ $promo ? 'pt-36 sm:pt-44' : 'pt-28 sm:pt-36' }}">
    <div class="dotgrid absolute inset-0"></div>
    <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2">
        <div>
            <span class="reveal inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-medium ring-1 ring-white/20 backdrop-blur">
                <span class="h-2 w-2 animate-pulse rounded-full bg-red-500"></span> {{ $site['hero_badge'] }}
            </span>
            <h1 class="reveal mt-5 text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl" data-d="1">
                {{ $site['hero_title_1'] }}<br>
                <span class="text-red-500">{{ $site['hero_title_2'] }}</span>
            </h1>
            <p class="reveal mt-5 max-w-xl text-lg text-neutral-300" data-d="2">{{ $site['hero_subtitle'] }}</p>
            <div class="reveal mt-8 flex flex-wrap gap-3" data-d="3">
                <a href="#estimasi" class="rounded-full bg-red-600 px-6 py-3 font-semibold text-white shadow-lg shadow-red-900/40 transition hover:-translate-y-0.5 hover:bg-red-700">Hitung Estimasi</a>
            </div>

            <dl class="reveal mt-12 grid max-w-md grid-cols-3 gap-6" data-d="3">
                <div><dt class="text-xs uppercase tracking-wider text-neutral-400">Pelanggan</dt><dd class="mt-1 text-3xl font-extrabold"><span data-count="{{ (int) $site['stat_customers'] }}">0</span>+</dd></div>
                <div><dt class="text-xs uppercase tracking-wider text-neutral-400">Item dicuci</dt><dd class="mt-1 text-3xl font-extrabold"><span data-count="{{ (int) $site['stat_items'] }}">0</span>+</dd></div>
                <div><dt class="text-xs uppercase tracking-wider text-neutral-400">Cabang</dt><dd class="mt-1 text-3xl font-extrabold"><span data-count="{{ max($branches->count(), 1) }}">0</span></dd></div>
            </dl>
        </div>

        {{-- Kartu tracking --}}
        <div class="reveal relative mx-auto w-full max-w-md" data-d="2"
             x-data="{ step: 1, steps: ['Diterima','Sedang Dicuci','Selesai','Diambil'] }"
             x-init="setInterval(() => step = step >= 4 ? 1 : step + 1, 2600)">
            <div class="floaty rounded-3xl bg-white p-6 text-neutral-800 shadow-2xl ring-1 ring-black/5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-neutral-400">Invoice</p>
                        <p class="font-mono text-sm font-semibold">INV-260101-001</p>
                    </div>
                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700" x-text="steps[step-1]"></span>
                </div>
                <div class="mt-5 flex items-center gap-3 rounded-2xl bg-neutral-50 p-3">
                    <div class="grid h-12 w-12 place-items-center rounded-xl bg-neutral-950 text-2xl">👟</div>
                    <div>
                        <p class="text-sm font-semibold">Nike Air Force 1 Putih</p>
                        <p class="text-xs text-neutral-500">Deep Clean Sepatu · 1x</p>
                    </div>
                </div>
                <ol class="mt-6 space-y-4">
                    <template x-for="(s, i) in steps" :key="i">
                        <li class="flex items-center gap-3">
                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full text-xs font-bold transition-all duration-500"
                                  :class="step > i ? 'bg-red-600 text-white' : 'bg-neutral-100 text-neutral-400'" x-text="step > i ? '✓' : i + 1"></span>
                            <span class="text-sm transition-colors" :class="step > i ? 'font-semibold text-neutral-900' : 'text-neutral-400'" x-text="s"></span>
                        </li>
                    </template>
                </ol>
                <div class="mt-5 h-2 overflow-hidden rounded-full bg-neutral-100">
                    <div class="h-full rounded-full bg-red-600 transition-all duration-700" :style="`width:${step*25}%`"></div>
                </div>
                <p class="mt-4 text-xs text-neutral-500">Setiap perubahan status kami kabari lewat WhatsApp.</p>
            </div>
            <div class="absolute -bottom-5 -left-5 hidden rounded-2xl bg-white px-4 py-3 text-sm font-bold text-red-600 shadow-xl ring-1 ring-black/5 sm:block">⭐ Diskon member s/d 10%</div>
        </div>
    </div>
</section>

{{-- ================= KEUNGGULAN ================= --}}
@if ($features->count())
<section class="border-b bg-white">
    <div class="mx-auto grid max-w-7xl gap-6 px-4 py-10 sm:grid-cols-2 sm:px-6 lg:grid-cols-4">
        @foreach ($features as $i => $f)
            <div class="reveal flex gap-4" data-d="{{ $i % 3 + 1 }}">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-red-50 text-2xl">{{ $f->icon }}</div>
                <div><h3 class="font-bold">{{ $f->title }}</h3><p class="mt-1 text-sm text-neutral-600">{{ $f->description }}</p></div>
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- ================= LAYANAN ================= --}}
<section id="layanan" class="scroll-mt-24 bg-neutral-50 py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6" x-data="{ tab: '{{ $services->keys()->first() ?? 'sepatu' }}' }">
        <div class="reveal mx-auto max-w-2xl text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-red-600">Layanan Kami</p>
            <h2 class="mt-2 text-3xl font-extrabold sm:text-4xl">{{ $site['services_title'] }}</h2>
            <p class="mt-3 text-neutral-600">{{ $site['services_subtitle'] }}</p>
        </div>

        <div class="reveal mt-8 flex justify-center">
            <div class="inline-flex rounded-full bg-white p-1 shadow-sm ring-1 ring-neutral-200">
                @foreach ($catMeta as $key => $m)
                    <button @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'bg-red-600 text-white shadow' : 'text-neutral-600 hover:text-red-600'"
                            class="rounded-full px-5 py-2 text-sm font-semibold transition sm:px-7">{{ $m['icon'] }} {{ $m['label'] }}</button>
                @endforeach
            </div>
        </div>

        @foreach ($catMeta as $key => $m)
            <div x-show="tab === '{{ $key }}'" x-cloak x-transition.opacity.duration.300ms class="mt-10">
                <p class="mb-6 text-center text-sm text-neutral-500">{{ $m['desc'] }}</p>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($services[$key] ?? [] as $s)
                        <article class="group rounded-2xl bg-white p-6 shadow-sm ring-1 ring-neutral-200 transition hover:-translate-y-1 hover:shadow-xl hover:ring-red-500">
                            <div class="flex items-start justify-between">
                                <div class="grid h-12 w-12 place-items-center rounded-xl bg-neutral-950 text-2xl transition group-hover:bg-red-600">{{ $m['icon'] }}</div>
                                <span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-medium text-neutral-600">±{{ $s->duration_days }} hari</span>
                            </div>
                            <h3 class="mt-4 text-lg font-bold">{{ $s->name }}</h3>
                            @if ($s->description)<p class="mt-1 text-sm text-neutral-600">{{ $s->description }}</p>@endif
                            <div class="mt-5 flex items-end justify-between">
                                <p class="text-2xl font-extrabold text-red-600"><span class="text-sm font-semibold">Rp</span> {{ number_format($s->price, 0, ',', '.') }}</p>
                                <a href="{{ $isStaff ? route('dashboard') : route('portal.order.create', ['service' => $s->id]) }}"
                                   class="rounded-full bg-neutral-950 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-600">Pesan</a>
                            </div>
                        </article>
                    @empty
                        <p class="col-span-full rounded-2xl bg-white p-10 text-center text-neutral-500 ring-1 ring-neutral-200">Layanan kategori ini segera hadir.</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</section>

{{-- ================= GALERI ================= --}}
@if ($gallery->count())
<section id="galeri" class="scroll-mt-24 py-20" x-data="{ f: 'semua', img: null }" @keydown.escape.window="img = null">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="reveal mx-auto max-w-2xl text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-red-600">Galeri</p>
            <h2 class="mt-2 text-3xl font-extrabold sm:text-4xl">Hasil Cucian Kami</h2>
        </div>

        <div class="reveal mt-8 flex flex-wrap justify-center gap-2">
            @foreach (['semua' => 'Semua', 'sepatu' => 'Sepatu', 'tas' => 'Tas', 'helm' => 'Helm'] as $k => $lbl)
                <button @click="f = '{{ $k }}'"
                        :class="f === '{{ $k }}' ? 'bg-red-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'"
                        class="rounded-full px-5 py-2 text-sm font-semibold transition">{{ $lbl }}</button>
            @endforeach
        </div>

        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($gallery as $g)
                <figure x-show="f === 'semua' || f === '{{ $g->category }}'" x-transition.opacity
                        @click="img = { src: @js($g->url), t: @js($g->title), c: @js($g->caption) }"
                        class="group relative cursor-zoom-in overflow-hidden rounded-2xl bg-neutral-100 ring-1 ring-neutral-200">
                    <img src="{{ $g->url }}" alt="{{ $g->title }}" loading="lazy" class="aspect-square w-full object-cover transition duration-500 group-hover:scale-110">
                    <figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent p-4 text-sm font-semibold text-white opacity-0 transition group-hover:opacity-100">{{ $g->title }}</figcaption>
                </figure>
            @endforeach
        </div>
    </div>

    <div x-show="img" x-cloak x-transition.opacity @click="img = null" class="fixed inset-0 z-[60] grid place-items-center bg-black/90 p-4">
        <figure class="max-h-full max-w-3xl" @click.stop>
            <img :src="img?.src" :alt="img?.t" class="max-h-[80vh] rounded-xl object-contain">
            <figcaption class="mt-3 text-center text-white">
                <p class="font-bold" x-text="img?.t"></p>
                <p class="text-sm text-neutral-300" x-text="img?.c"></p>
            </figcaption>
        </figure>
        <button @click="img = null" class="absolute right-5 top-5 text-3xl text-white" aria-label="Tutup">✕</button>
    </div>
</section>
@endif

{{-- ================= ALUR KERJA ================= --}}
<section id="alur" class="scroll-mt-24 bg-neutral-50 py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="reveal mx-auto max-w-2xl text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-red-600">Alur Kerja</p>
            <h2 class="mt-2 text-3xl font-extrabold sm:text-4xl">Mudah, Transparan, Terpantau</h2>
        </div>
        <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['1','Antar atau Chat','Bawa barang ke cabang atau hubungi kami via WhatsApp.'],
                ['2','Cek & Nota','Kondisi barang dicek, treatment dipilih, nota diterbitkan.'],
                ['3','Proses Pencucian','Dicuci sesuai material. Anda dapat kabar tiap tahap.'],
                ['4','Ambil & Bayar','Barang siap, bayar tunai, transfer, atau QRIS.'],
            ] as $i => [$n, $t, $d])
                <div class="reveal rounded-2xl bg-white p-6 ring-1 ring-neutral-200 transition hover:shadow-lg hover:ring-red-300" data-d="{{ $i % 3 + 1 }}">
                    <span class="grid h-11 w-11 place-items-center rounded-full bg-red-600 text-lg font-extrabold text-white shadow-lg shadow-red-600/30">{{ $n }}</span>
                    <h3 class="mt-4 text-lg font-bold">{{ $t }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ $d }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ================= ESTIMASI ================= --}}
<section id="estimasi" class="scroll-mt-24 bg-neutral-950 py-20 text-white">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-2">
        <div class="reveal">
            <p class="text-sm font-bold uppercase tracking-widest text-red-500">Kalkulator</p>
            <h2 class="mt-2 text-3xl font-extrabold sm:text-4xl">Hitung Estimasi Biaya Cucian Anda</h2>
            <p class="mt-3 text-neutral-300">Pilih treatment, jumlah, dan tier member. Diskon langsung terhitung, lalu kirim estimasinya ke kami lewat WhatsApp.</p>
            <ul class="mt-6 space-y-2 text-sm text-neutral-300">
                <li><span class="text-red-500">✓</span> Harga mengikuti daftar layanan terbaru</li>
                <li><span class="text-red-500">✓</span> Tambah beberapa item sekaligus</li>
                <li><span class="text-red-500">✓</span> Harga final ditetapkan setelah pengecekan kondisi barang</li>
            </ul>
        </div>

        <div class="reveal rounded-3xl bg-white p-6 text-neutral-800 shadow-2xl sm:p-8" data-d="1"
             x-data="estimator(@js($estimatorServices), '{{ $waNumber }}', @js($brand))">
            <p x-show="services.length === 0" class="text-sm text-neutral-500">Belum ada layanan aktif.</p>

            <div x-show="services.length > 0" class="space-y-4">
                <template x-for="(row, idx) in rows" :key="idx">
                    <div class="grid grid-cols-12 items-center gap-2">
                        <select x-model="row.id" class="col-span-7 rounded-xl border-neutral-300 text-sm focus:border-red-500 focus:ring-red-500">
                            <option value="">Pilih treatment</option>
                            <template x-for="s in services" :key="s.id">
                                <option :value="s.id" x-text="`${s.name} (Rp ${fmt(s.price)})`"></option>
                            </template>
                        </select>
                        <div class="col-span-4 flex items-center justify-between rounded-xl border border-neutral-300 px-1">
                            <button type="button" @click="row.qty = Math.max(1, row.qty - 1)" class="h-9 w-9 rounded-lg text-lg font-bold text-red-600 hover:bg-red-50">−</button>
                            <span class="text-sm font-semibold" x-text="row.qty"></span>
                            <button type="button" @click="row.qty++" class="h-9 w-9 rounded-lg text-lg font-bold text-red-600 hover:bg-red-50">+</button>
                        </div>
                        <button type="button" @click="rows.length > 1 && rows.splice(idx, 1)" class="col-span-1 text-neutral-400 hover:text-red-600" title="Hapus">✕</button>
                    </div>
                </template>
                <button type="button" @click="rows.push({id:'', qty:1})" class="text-sm font-semibold text-red-600 hover:underline">+ Tambah item</button>

                <div>
                    <p class="mb-2 text-sm font-medium">Tier member</p>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="t in tiers" :key="t.name">
                            <button type="button" @click="tier = t" class="rounded-xl border px-2 py-2 text-center text-sm transition"
                                    :class="tier.name === t.name ? 'border-red-600 bg-red-50 font-bold text-red-700' : 'border-neutral-200 text-neutral-600 hover:border-red-300'">
                                <span x-text="t.name"></span>
                                <span class="block text-xs font-normal" x-text="t.pct + '% off'"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <dl class="space-y-2 rounded-2xl bg-neutral-50 p-4 text-sm">
                    <div class="flex justify-between"><dt>Subtotal</dt><dd x-text="'Rp ' + fmt(subtotal)"></dd></div>
                    <div class="flex justify-between text-red-600"><dt>Diskon <span x-text="tier.name"></span></dt><dd x-text="'- Rp ' + fmt(discount)"></dd></div>
                    <div class="flex justify-between border-t pt-2 text-lg font-extrabold"><dt>Estimasi Total</dt><dd class="text-red-600" x-text="'Rp ' + fmt(total)"></dd></div>
                    <p class="text-xs text-neutral-500" x-show="maxDays > 0">Estimasi selesai ±<span x-text="maxDays"></span> hari kerja.</p>
                </dl>

                <a :href="waUrl" target="_blank" rel="noopener" :class="subtotal === 0 ? 'pointer-events-none opacity-50' : ''"
                   class="block rounded-xl bg-green-500 py-3 text-center font-semibold text-white transition hover:bg-green-600">Kirim Estimasi via WhatsApp</a>
            </div>
        </div>
    </div>
</section>

{{-- ================= MEMBERSHIP ================= --}}
<section id="member" class="scroll-mt-24 py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="reveal mx-auto max-w-2xl text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-red-600">Membership</p>
            <h2 class="mt-2 text-3xl font-extrabold sm:text-4xl">{{ $site['member_title'] }}</h2>
            <p class="mt-3 text-neutral-600">{{ $site['member_subtitle'] }}</p>
        </div>

        <div class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach ([
                ['Reguler','Gratis daftar','0%','ring-neutral-200','bg-white',false,['Poin tiap transaksi lunas','Riwayat pesanan online','Cek status kapan saja']],
                ['Silver','Total belanja Rp 500.000','5%','ring-neutral-400','bg-white',false,['Semua benefit Reguler','Diskon 5% tiap pesanan','Prioritas informasi promo']],
                ['Gold','Total belanja Rp 1.500.000','10%','ring-red-600','bg-neutral-950 text-white',true,['Semua benefit Silver','Diskon 10% tiap pesanan','Penawaran eksklusif member']],
            ] as $i => [$name, $syarat, $pct, $ring, $bg, $pop, $perks])
                <div class="reveal relative rounded-3xl {{ $bg }} p-8 shadow-sm ring-2 {{ $ring }} transition hover:-translate-y-1 hover:shadow-xl" data-d="{{ $i + 1 }}">
                    @if ($pop)<span class="absolute -top-3 right-6 rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">Terbaik</span>@endif
                    <p class="text-sm font-semibold {{ $pop ? 'text-neutral-400' : 'text-neutral-500' }}">{{ $syarat }}</p>
                    <h3 class="mt-1 text-2xl font-extrabold">{{ $name }}</h3>
                    <p class="mt-4 text-5xl font-extrabold text-red-600">{{ $pct }}<span class="text-base font-semibold {{ $pop ? 'text-neutral-400' : 'text-neutral-500' }}"> diskon</span></p>
                    <ul class="mt-6 space-y-2 text-sm {{ $pop ? 'text-neutral-300' : 'text-neutral-600' }}">
                        @foreach ($perks as $p)<li class="flex gap-2"><span class="text-red-600">✓</span>{{ $p }}</li>@endforeach
                    </ul>
                </div>
            @endforeach
        </div>
        <div class="reveal mt-10 text-center">
            <a href="{{ route('register') }}" class="inline-block rounded-full bg-red-600 px-8 py-3 font-semibold text-white shadow-lg shadow-red-600/30 transition hover:-translate-y-0.5 hover:bg-red-700">Daftar Member Gratis</a>
        </div>
    </div>
</section>

{{-- ================= CABANG ================= --}}
<section id="cabang" class="scroll-mt-24 bg-neutral-50 py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="reveal mx-auto max-w-2xl text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-red-600">Lokasi</p>
            <h2 class="mt-2 text-3xl font-extrabold sm:text-4xl">Kunjungi Cabang Kami</h2>
            <p class="mt-3 text-neutral-600">{{ $openHours }}</p>
        </div>
        <div class="mt-10 grid gap-6 md:grid-cols-2">
            @foreach ($branches as $i => $b)
                @php
                    $bWa = $normPhone($b->whatsapp);
                    $bIg = ltrim((string) $b->instagram, '@');
                @endphp
                <div class="reveal rounded-3xl bg-white p-7 shadow-sm ring-1 ring-neutral-200 transition hover:shadow-lg" data-d="{{ $i % 3 + 1 }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-red-50 text-2xl">📍</div>
                        @if ($b->is_main)<span class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">Pusat</span>@endif
                    </div>
                    <h3 class="mt-4 text-lg font-bold">{{ $b->name }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ $b->address }}</p>

                    <ul class="mt-4 space-y-1.5 text-sm text-neutral-700">
                        @if ($bWa)<li>💬 +{{ $bWa }}</li>@endif
                        @if ($bIg)<li>📸 &#64;{{ $bIg }}</li>@endif
                    </ul>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <a target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query={{ urlencode($b->name . ' ' . $b->address) }}"
                           class="rounded-full bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Buka Maps</a>
                        @if ($bWa)
                            <a target="_blank" rel="noopener" href="{{ $waLink('Halo, saya mau tanya soal ' . $b->name, $bWa) }}"
                               class="rounded-full bg-green-500 px-4 py-2 text-sm font-semibold text-white hover:bg-green-600">WhatsApp</a>
                        @endif
                        @if ($bIg)
                            <a target="_blank" rel="noopener" href="https://instagram.com/{{ $bIg }}"
                               class="rounded-full border border-neutral-300 px-4 py-2 text-sm font-semibold hover:bg-neutral-50">Instagram</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ================= FAQ ================= --}}
@if ($faqs->count())
<section id="faq" class="scroll-mt-24 py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6" x-data="{ active: 0 }">
        <div class="reveal text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-red-600">FAQ</p>
            <h2 class="mt-2 text-3xl font-extrabold sm:text-4xl">Pertanyaan yang Sering Diajukan</h2>
        </div>
        <div class="mt-10 space-y-3">
            @foreach ($faqs as $i => $f)
                <div class="reveal overflow-hidden rounded-2xl ring-1 ring-neutral-200">
                    <button @click="active = active === {{ $i + 1 }} ? 0 : {{ $i + 1 }}" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left font-semibold hover:bg-neutral-50">
                        {{ $f->question }}
                        <span class="text-xl text-red-600 transition-transform" :class="active === {{ $i + 1 }} && 'rotate-45'">+</span>
                    </button>
                    <div x-show="active === {{ $i + 1 }}" x-cloak x-transition>
                        <p class="px-5 pb-5 text-sm text-neutral-600">{{ $f->answer }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ================= KONTAK ================= --}}
<section id="kontak" class="hero-bg relative scroll-mt-24 overflow-hidden py-20 text-white">
    <div class="dotgrid absolute inset-0"></div>
    <div class="relative mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-2">
        <div class="reveal">
            <p class="text-sm font-bold uppercase tracking-widest text-red-500">Hubungi Kami</p>
            <h2 class="mt-2 text-3xl font-extrabold sm:text-4xl">{{ $site['contact_title'] }}</h2>
            <p class="mt-3 text-neutral-300">{{ $site['contact_text'] }}</p>

            <div class="mt-8 space-y-4 text-sm">
                @foreach ($branches as $b)
                    @php
                        $bWa = $normPhone($b->whatsapp);
                        $bIg = ltrim((string) $b->instagram, '@');
                    @endphp
                    <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/20">
                        <p class="font-bold">{{ $b->name }}</p>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @if ($bWa)
                                <a href="{{ $waLink('Halo, saya mau tanya soal ' . $b->name, $bWa) }}" target="_blank" rel="noopener"
                                   class="flex items-center gap-2 rounded-xl bg-green-500 px-4 py-2.5 font-semibold text-white transition hover:bg-green-600">
                                    💬 <span class="truncate">+{{ $bWa }}</span>
                                </a>
                            @endif
                            @if ($bIg)
                                <a href="https://instagram.com/{{ $bIg }}" target="_blank" rel="noopener"
                                   class="flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 font-semibold ring-1 ring-white/20 transition hover:bg-white/20">
                                    📸 <span class="truncate">&#64;{{ $bIg }}</span>
                                </a>
                            @endif
                            @if (! $bWa && ! $bIg)
                                <p class="text-neutral-400 sm:col-span-2">Kontak belum diisi.</p>
                            @endif
                        </div>
                    </div>
                @endforeach

                <a href="{{ route('track') }}" class="flex items-center gap-3 rounded-2xl bg-white/10 p-4 ring-1 ring-white/20 transition hover:bg-white/20">
                    <span class="text-2xl">🔎</span><span><b class="block">Cek Status Pesanan</b>Masukkan nomor invoice dan HP</span>
                </a>
            </div>
        </div>

        <div class="reveal rounded-3xl bg-white p-6 text-neutral-800 shadow-2xl sm:p-8" data-d="1">
            @if (session('success'))
                <div class="mb-4 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800 ring-1 ring-emerald-200">{{ session('success') }}</div>
            @endif
            <form method="POST" action="{{ route('inquiry.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium" for="f-name">Nama</label>
                    <input id="f-name" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border-neutral-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium" for="f-phone">No. WhatsApp</label>
                    <input id="f-phone" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx" class="w-full rounded-xl border-neutral-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
                <div x-data="{ n: {{ strlen(old('message', '')) }} }">
                    <label class="mb-1 block text-sm font-medium" for="f-msg">Pertanyaan</label>
                    <textarea id="f-msg" name="message" rows="4" maxlength="1000" required @input="n = $event.target.value.length"
                              class="w-full rounded-xl border-neutral-300 text-sm focus:border-red-500 focus:ring-red-500">{{ old('message') }}</textarea>
                    <p class="mt-1 text-right text-xs text-neutral-400"><span x-text="n"></span>/1000</p>
                </div>
                @if ($errors->any())<p class="text-sm text-red-600">{{ $errors->first() }}</p>@endif
                <button class="w-full rounded-xl bg-red-600 py-3 font-semibold text-white transition hover:bg-red-700">Kirim Pertanyaan</button>
            </form>
        </div>
    </div>
</section>
</main>

{{-- ================= FOOTER ================= --}}
<footer class="border-t-4 border-red-600 bg-black py-10 text-sm text-neutral-400">
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 sm:px-6 md:flex-row">
        <p class="leading-none text-white">
            <span class="block text-lg font-black uppercase tracking-tight">👟 Clean Shoes</span>
            <span class="mt-1 block text-[10px] font-semibold uppercase tracking-[0.3em] text-red-500">Jejak Suci</span>
        </p>
        <p>© {{ date('Y') }} {{ $site['footer_text'] }}</p>
        <div class="flex gap-4">
            <a href="{{ route('track') }}" class="hover:text-red-500">Cek Pesanan</a>
            <a href="{{ route('login') }}" class="hover:text-red-500">Masuk</a>
            <a href="{{ route('register') }}" class="hover:text-red-500">Daftar</a>
        </div>
    </div>
</footer>

<a href="{{ $waLink('Halo ' . $brand . ', saya mau tanya layanan.') }}" target="_blank" rel="noopener" aria-label="Chat WhatsApp"
   class="fixed bottom-5 right-5 z-40 grid h-14 w-14 place-items-center rounded-full bg-green-500 text-2xl text-white shadow-xl transition hover:scale-110">💬</a>
<button x-show="scrolled" x-cloak x-transition @click="window.scrollTo({top: 0, behavior: 'smooth'})" aria-label="Ke atas"
        class="fixed bottom-5 left-5 z-40 grid h-11 w-11 place-items-center rounded-full bg-red-600 text-white shadow-lg hover:bg-red-700">↑</button>

<script>
    function estimator(services, wa, brand) {
        return {
            services, rows: [{ id: '', qty: 1 }],
            tiers: [{ name: 'Reguler', pct: 0 }, { name: 'Silver', pct: 5 }, { name: 'Gold', pct: 10 }],
            tier: { name: 'Reguler', pct: 0 },
            fmt: n => new Intl.NumberFormat('id-ID').format(n),
            find(id) { return this.services.find(s => s.id == id); },
            get subtotal() { return this.rows.reduce((t, r) => t + (this.find(r.id)?.price || 0) * r.qty, 0); },
            get discount() { return Math.floor(this.subtotal * this.tier.pct / 100); },
            get total() { return this.subtotal - this.discount; },
            get maxDays() { return Math.max(0, ...this.rows.map(r => this.find(r.id)?.days || 0)); },
            get waUrl() {
                const lines = this.rows.filter(r => this.find(r.id)).map(r => `- ${this.find(r.id).name} x${r.qty}`);
                const text = `Halo ${brand}, saya mau cuci:\n${lines.join('\n')}\nTier: ${this.tier.name}\nEstimasi total: Rp ${this.fmt(this.total)}`;
                return `https://wa.me/${wa}?text=${encodeURIComponent(text)}`;
            },
        };
    }

    document.addEventListener('DOMContentLoaded', () => {
        const io = new IntersectionObserver((entries) => {
            entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
        }, { threshold: .12 });
        document.querySelectorAll('.reveal').forEach(el => io.observe(el));

        document.querySelectorAll('[data-count]').forEach(el => {
            const target = +el.dataset.count, dur = 1400, t0 = performance.now();
            const tick = now => {
                const p = Math.min((now - t0) / dur, 1);
                el.textContent = new Intl.NumberFormat('id-ID').format(Math.floor(target * (1 - Math.pow(1 - p, 3))));
                if (p < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        });
    });
</script>
</body>
</html>