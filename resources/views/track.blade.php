<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Cek Status Pesanan - Clean Shoes Jejak Suci</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        [x-cloak]{display:none!important}
        .safe-b{padding-bottom:env(safe-area-inset-bottom)}
        .hero-bg{background:radial-gradient(circle at 15% 10%,rgba(220,38,38,.55) 0%,transparent 45%),radial-gradient(circle at 95% 90%,rgba(153,27,27,.5) 0%,transparent 45%),#0a0a0a}
    </style>
</head>
<body class="min-h-screen bg-neutral-100 text-neutral-800 antialiased">
@php
    $rp    = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $steps = ['diterima' => 'Diterima', 'diproses' => 'Dicuci', 'selesai' => 'Selesai', 'diambil' => 'Diambil'];
    $searched = request()->filled('invoice');
@endphp

{{-- Header --}}
<header class="hero-bg text-white">
    <div class="mx-auto flex max-w-xl items-center justify-between px-4 py-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <span class="grid h-9 w-9 place-items-center rounded-xl bg-red-600 text-lg">👟</span>
            <span class="leading-none">
                <span class="block text-base font-black uppercase tracking-tight">Clean Shoes</span>
                <span class="mt-0.5 block text-[9px] font-semibold uppercase tracking-[0.3em] text-red-300">Jejak Suci</span>
            </span>
        </a>
        <a href="{{ route('home') }}" class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold ring-1 ring-white/20">← Beranda</a>
    </div>

    <div class="mx-auto max-w-xl px-4 pb-16 pt-6 text-center">
        <p class="text-4xl">🔎</p>
        <h1 class="mt-2 text-2xl font-extrabold sm:text-3xl">Cek Status Pesanan</h1>
        <p class="mt-1 text-sm text-neutral-300">Masukkan nomor invoice dan nomor HP yang terdaftar.</p>
    </div>
</header>

<main class="mx-auto -mt-10 max-w-xl space-y-4 px-4 pb-16">

    {{-- Form --}}
    <form method="GET" action="{{ route('track') }}" class="space-y-4 rounded-2xl bg-white p-5 shadow-xl ring-1 ring-black/5"
          x-data="{ loading: false }" @submit="loading = true">
        <div>
            <label for="invoice" class="mb-1.5 block text-sm font-semibold">No. Invoice</label>
            <input id="invoice" name="invoice" value="{{ request('invoice') }}" required
                   placeholder="INV-260101-001" autocapitalize="characters" autocomplete="off" inputmode="text"
                   class="block w-full rounded-xl border-neutral-300 px-4 py-3.5 font-mono text-base uppercase shadow-sm placeholder:normal-case placeholder:text-neutral-400 focus:border-red-500 focus:ring-red-500">
        </div>
        <div>
            <label for="phone" class="mb-1.5 block text-sm font-semibold">No. HP / WhatsApp</label>
            <input id="phone" name="phone" value="{{ request('phone') }}" required
                   placeholder="08xxxxxxxxxx" inputmode="tel" autocomplete="tel"
                   class="block w-full rounded-xl border-neutral-300 px-4 py-3.5 text-base shadow-sm placeholder:text-neutral-400 focus:border-red-500 focus:ring-red-500">
        </div>
        <button :disabled="loading"
                class="w-full rounded-xl bg-red-600 py-3.5 text-base font-bold text-white shadow-lg shadow-red-600/30 transition active:scale-[.98] disabled:opacity-70">
            <span x-text="loading ? 'Mencari...' : 'Cek Pesanan'">Cek Pesanan</span>
        </button>
    </form>

    {{-- Hasil --}}
    @if ($searched)
        @if ($order)
            <section class="space-y-4">
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-neutral-200">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-mono text-sm font-bold">{{ $order->invoice_no }}</p>
                            <p class="truncate text-lg font-extrabold">{{ $order->customer->name }}</p>
                            <p class="truncate text-xs text-neutral-500">📍 {{ $order->branch->name }}</p>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1">
                            <span class="badge {{ $order->status_color }}">{{ \App\Models\Order::STATUSES[$order->status] }}</span>
                            <span class="badge {{ $order->payment_color }}">{{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}</span>
                        </div>
                    </div>

                    {{-- Tahapan --}}
                    @if ($order->status === 'batal')
                        <p class="mt-4 rounded-xl bg-red-50 p-3 text-sm font-semibold text-red-700">Pesanan ini dibatalkan.</p>
                    @elseif ($order->status === 'menunggu')
                        <p class="mt-4 rounded-xl bg-yellow-50 p-3 text-sm text-yellow-800">Menunggu konfirmasi admin cabang. Kami akan mengabari lewat WhatsApp.</p>
                    @else
                        @php $idx = array_search($order->status, array_keys($steps)); @endphp
                        <ol class="relative mt-5 grid grid-cols-4 text-center">
                            <div class="absolute left-[12.5%] right-[12.5%] top-4 h-0.5 bg-neutral-200"></div>
                            <div class="absolute left-[12.5%] top-4 h-0.5 bg-red-600 transition-all" style="width: {{ $idx === false ? 0 : ($idx / 3) * 75 }}%"></div>
                            @foreach ($steps as $k => $label)
                                @php $i = array_search($k, array_keys($steps)); $done = $idx !== false && $i <= $idx; @endphp
                                <li class="relative">
                                    <span class="mx-auto grid h-8 w-8 place-items-center rounded-full text-xs font-bold ring-4 ring-white {{ $done ? 'bg-red-600 text-white' : 'bg-neutral-200 text-neutral-500' }}">{{ $done ? '✓' : $i + 1 }}</span>
                                    <p class="mt-1.5 text-[11px] leading-tight {{ $done ? 'font-bold text-neutral-900' : 'text-neutral-400' }}">{{ $label }}</p>
                                </li>
                            @endforeach
                        </ol>
                    @endif

                    <p class="mt-4 rounded-xl bg-neutral-50 p-3 text-center text-sm">
                        Estimasi selesai <b>{{ $order->estimated_done_at?->format('d M Y') ?? '-' }}</b>
                    </p>
                </div>

                {{-- Barang --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-neutral-200">
                    <h2 class="mb-3 font-bold">Barang</h2>
                    <ul class="divide-y">
                        @foreach ($order->items as $i)
                            <li class="flex items-start justify-between gap-3 py-2.5 text-sm">
                                <span class="min-w-0 break-words">{{ $i->item_name }} <span class="text-neutral-400">×{{ $i->qty }}</span></span>
                                <span class="shrink-0 font-semibold">{{ $rp($i->subtotal) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Tagihan --}}
                <div class="space-y-1.5 rounded-2xl bg-white p-5 text-sm shadow-sm ring-1 ring-neutral-200">
                    <div class="flex justify-between"><span class="text-neutral-500">Subtotal</span><span>{{ $rp($order->subtotal) }}</span></div>
                    <div class="flex justify-between"><span class="text-neutral-500">Diskon member</span><span class="text-red-600">- {{ $rp($order->discount) }}</span></div>
                    <div class="flex justify-between border-t pt-2 text-base font-extrabold"><span>Total</span><span>{{ $rp($order->total) }}</span></div>
                    <div class="flex justify-between"><span class="text-neutral-500">Sudah dibayar</span><span>{{ $rp($order->paid_amount) }}</span></div>
                    <div class="flex justify-between font-bold {{ $order->remaining > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                        <span>Sisa tagihan</span><span>{{ $order->remaining > 0 ? $rp($order->remaining) : 'Lunas ✓' }}</span>
                    </div>
                </div>

                {{-- Hubungi cabang --}}
                @php
                    $wa = preg_replace('/\D/', '', (string) $order->branch->whatsapp);
                    if (str_starts_with($wa, '0')) { $wa = '62' . substr($wa, 1); }
                    $waText = "Halo Admin {$order->branch->name}, saya ingin menanyakan pesanan {$order->invoice_no}.";
                @endphp
                @if ($wa)
                    <a href="https://wa.me/{{ $wa }}?text={{ rawurlencode($waText) }}" target="_blank" rel="noopener"
                       class="flex items-center justify-center gap-2 rounded-xl bg-green-500 py-3.5 font-bold text-white shadow-lg active:scale-[.98]">
                        💬 Tanya Admin {{ $order->branch->is_main ? 'Pusat' : 'Cabang' }}
                    </a>
                @endif

                @auth
                    @if (auth()->user()->role === 'customer')
                        <a href="{{ route('portal.orders') }}" class="block rounded-xl bg-white py-3.5 text-center text-sm font-bold ring-1 ring-neutral-200 active:scale-[.98]">Buka Pesanan Saya</a>
                    @endif
                @endauth
            </section>
        @else
            <div class="rounded-2xl bg-white p-6 text-center shadow-sm ring-1 ring-neutral-200">
                <p class="text-4xl">😕</p>
                <p class="mt-2 font-bold">Pesanan tidak ditemukan</p>
                <p class="mt-1 text-sm text-neutral-500">Periksa kembali nomor invoice dan nomor HP. Gunakan nomor HP yang sama saat pesanan dibuat.</p>
            </div>
        @endif
    @endif
</main>

<footer class="safe-b pb-6 text-center text-xs text-neutral-500">
    © {{ date('Y') }} Clean Shoes Jejak Suci · Jonggol & Cikarang
</footer>
</body>
</html>