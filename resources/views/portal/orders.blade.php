@extends('layouts.panel')
@section('title', 'Pesanan Saya')
@section('content')
@php
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');

  // Cadangan jika sebuah cabang belum punya QRIS: public/images/qris.png
  $fallbackQris = file_exists(public_path('images/qris.png')) ? asset('images/qris.png') : null;

  $fallbackWa = \App\Models\Setting::forSite()['wa_number'] ?? '';
  $normWa = function ($n) {
      $n = preg_replace('/\D/', '', (string) $n);
      return str_starts_with($n, '0') ? '62' . substr($n, 1) : $n;
  };
@endphp

<div class="flex flex-wrap items-center justify-between gap-3">
  <div class="inline-flex flex-wrap gap-1 rounded-xl bg-white p-1 ring-1 ring-neutral-200">
    @foreach (['semua' => 'Semua', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai', 'batal' => 'Dibatalkan'] as $k => $label)
      <a href="{{ route('portal.orders', ['tab' => $k]) }}"
         class="rounded-lg px-4 py-2 text-sm font-semibold {{ $tab === $k ? 'bg-red-600 text-white' : 'text-neutral-600 hover:bg-neutral-100' }}">
        {{ $label }} <span class="text-xs opacity-75">({{ $counts[$k] }})</span>
      </a>
    @endforeach
  </div>
  <a href="{{ route('portal.order.create') }}" class="btn">+ Pesan Cucian</a>
</div>

@forelse ($orders as $o)
  @php
    $canPay  = ! in_array($o->status, ['menunggu', 'batal']) && $o->remaining > 0;

    // QRIS dan WhatsApp milik cabang pesanan ini
    $qrisUrl = $o->branch->qris_url ?: $fallbackQris;
    $adminWa = $normWa($o->branch->whatsapp) ?: $normWa($fallbackWa);

    $waText = "Halo Admin {$o->branch->name} 👋\n"
            . "Saya {$customer->name} sudah membayar via QRIS.\n"
            . "Invoice: {$o->invoice_no}\n"
            . "Nominal: " . $rp($o->remaining) . "\n"
            . "Bukti pembayaran saya lampirkan di chat ini. Mohon dikonfirmasi 🙏";
    $waUrl = $adminWa ? 'https://wa.me/' . $adminWa . '?text=' . rawurlencode($waText) : null;
  @endphp

  <div class="card" x-data="{ qris: false, copied: false }" @keydown.escape.window="qris = false">
    <div class="flex flex-wrap items-start justify-between gap-2">
      <div>
        <p class="font-mono text-sm font-semibold">{{ $o->invoice_no }}
          @if ($o->source === 'online')<span class="badge bg-red-600 text-white">Online</span>@endif
        </p>
        <p class="text-xs text-neutral-500">{{ $o->created_at->format('d M Y H:i') }} · {{ $o->branch->name }}</p>
      </div>
      <div class="text-right">
        <span class="badge {{ $o->status_color }}">{{ \App\Models\Order::STATUSES[$o->status] }}</span>
        <span class="badge {{ $o->payment_color }}">{{ str_replace('_', ' ', $o->payment_status) }}</span>
      </div>
    </div>

    <ul class="mt-3 space-y-1 text-sm text-neutral-700">
      @foreach ($o->items as $it)
        <li>• {{ $it->item_name }} <span class="text-neutral-400">×{{ $it->qty }}</span></li>
      @endforeach
    </ul>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t pt-3">
      <div class="text-sm">
        <span class="text-neutral-500">Total</span> <b class="text-red-600">{{ $rp($o->total) }}</b>
        @if ($o->remaining > 0 && $o->status !== 'batal')
          <span class="ml-2 text-xs text-neutral-500">sisa {{ $rp($o->remaining) }}</span>
        @endif
        <p class="text-xs text-neutral-500">Estimasi selesai: {{ $o->estimated_done_at?->format('d M Y') ?? '-' }}</p>
        @if ($o->status === 'menunggu')
          <p class="text-xs text-yellow-700">Pembayaran bisa dilakukan setelah admin mengonfirmasi pesanan.</p>
        @endif
      </div>

      <div class="flex flex-wrap gap-2">
        @if ($o->status === 'menunggu')
          <form method="POST" action="{{ route('portal.cancel', $o) }}" onsubmit="return confirm('Batalkan pesanan ini?')">
            @csrf @method('PATCH')
            <button class="btn-light">Batalkan</button>
          </form>
        @endif

        @if ($canPay)
          <button type="button" @click="qris = true" class="btn-wa">💳 Bayar QRIS</button>
        @endif

        <a href="{{ route('portal.show', $o) }}" class="btn">Detail</a>
      </div>
    </div>

    {{-- Jendela pembayaran QRIS (milik cabang pesanan) --}}
    @if ($canPay)
      <div x-show="qris" x-cloak x-transition.opacity @click="qris = false"
           class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-black/70 p-4">
        <div @click.stop class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between">
            <div>
              <h3 class="text-lg font-extrabold">Bayar via QRIS</h3>
              <p class="text-xs text-neutral-500">{{ $o->branch->name }}</p>
              <p class="font-mono text-xs text-neutral-500">{{ $o->invoice_no }}</p>
            </div>
            <button type="button" @click="qris = false" class="text-2xl leading-none text-neutral-400 hover:text-neutral-800" aria-label="Tutup">✕</button>
          </div>

          <div class="mt-4 rounded-xl bg-neutral-950 p-4 text-center text-white">
            <p class="text-xs uppercase tracking-widest text-red-400">Nominal yang dibayar</p>
            <p class="mt-1 text-3xl font-black">{{ $rp($o->remaining) }}</p>
            <button type="button"
                    @click="navigator.clipboard.writeText('{{ $o->remaining }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    class="mt-2 text-xs text-neutral-300 underline hover:text-white"
                    x-text="copied ? 'Tersalin ✓' : 'Salin nominal'">Salin nominal</button>
          </div>

          <div class="mt-4 flex justify-center">
            @if ($qrisUrl)
              <img src="{{ $qrisUrl }}" alt="QRIS {{ $o->branch->name }}" class="max-h-80 w-auto rounded-xl border border-neutral-200 object-contain p-2">
            @else
              <div class="rounded-xl border-2 border-dashed border-neutral-300 p-6 text-center text-sm text-neutral-500">
                QRIS cabang ini belum dipasang.<br>Hubungi admin atau bayar langsung di kasir.
              </div>
            @endif
          </div>

          <ol class="mt-4 list-inside list-decimal space-y-1 text-sm text-neutral-700">
            <li>Scan QRIS dengan aplikasi bank atau e-wallet.</li>
            <li>Masukkan nominal <b>{{ $rp($o->remaining) }}</b>, lalu bayar.</li>
            <li>Screenshot bukti pembayaran.</li>
            <li>Klik tombol di bawah, lalu <b>lampirkan screenshot</b> di chat WhatsApp admin.</li>
          </ol>

          @if ($waUrl)
            <a href="{{ $waUrl }}" target="_blank" rel="noopener"
               class="mt-5 flex items-center justify-center gap-2 rounded-xl bg-green-500 py-3 font-bold text-white transition hover:bg-green-600">
              💬 Konfirmasi ke WhatsApp Admin
            </a>
            <p class="mt-2 text-center text-xs text-neutral-500">Pesan sudah terisi otomatis. Anda hanya perlu melampirkan bukti pembayaran.</p>
          @else
            <p class="mt-5 rounded-lg bg-yellow-50 p-3 text-center text-sm text-yellow-800">Nomor WhatsApp admin cabang belum diatur.</p>
          @endif
        </div>
      </div>
    @endif
  </div>
@empty
  <div class="card py-12 text-center">
    <p class="text-4xl">🧺</p>
    <p class="mt-2 font-semibold">Belum ada pesanan di sini.</p>
    <a href="{{ route('portal.order.create') }}" class="btn mt-4">+ Pesan Cucian Sekarang</a>
  </div>
@endforelse
@endsection