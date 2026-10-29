@extends('layouts.panel')
@section('title', 'Pesanan Saya')
@section('content')
@php
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');

  $fallbackQris = file_exists(public_path('images/qris.png')) ? asset('images/qris.png') : null;
  $fallbackWa   = \App\Models\Setting::forSite()['wa_number'] ?? '';
  $normWa = function ($n) {
      $n = preg_replace('/\D/', '', (string) $n);
      return str_starts_with($n, '0') ? '62' . substr($n, 1) : $n;
  };
@endphp

{{-- Tab filter (bisa digeser di HP) --}}
<div class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
  <div class="flex w-max gap-2 sm:w-auto">
    @foreach (['semua' => 'Semua', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai', 'batal' => 'Dibatalkan'] as $k => $label)
      <a href="{{ route('portal.orders', ['tab' => $k]) }}"
         class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold ring-1 {{ $tab === $k ? 'bg-red-600 text-white ring-red-600' : 'bg-white text-neutral-600 ring-neutral-200' }}">
        {{ $label }} <span class="text-xs opacity-75">{{ $counts[$k] }}</span>
      </a>
    @endforeach
  </div>
</div>

@forelse ($orders as $o)
  @php
    $canPay  = ! in_array($o->status, ['menunggu', 'batal']) && $o->remaining > 0;
    $qrisUrl = $o->branch->qris_url ?: $fallbackQris;
    $adminWa = $normWa($o->branch->whatsapp) ?: $normWa($fallbackWa);

    $waText = "Halo Admin {$o->branch->name} 👋\n"
            . "Saya {$customer->name} sudah membayar via QRIS.\n"
            . "Invoice: {$o->invoice_no}\n"
            . "Nominal: " . $rp($o->remaining) . "\n"
            . "Bukti pembayaran saya lampirkan di chat ini. Mohon dikonfirmasi 🙏";
    $waUrl = $adminWa ? 'https://wa.me/' . $adminWa . '?text=' . rawurlencode($waText) : null;
  @endphp

  <div class="card !p-4 sm:!p-5" x-data="{ qris: false, copied: false }" @keydown.escape.window="qris = false">
    <div class="flex items-start justify-between gap-2">
      <div class="min-w-0">
        <p class="font-mono text-sm font-semibold">{{ $o->invoice_no }}
          @if ($o->source === 'online')<span class="badge bg-red-600 text-white">Online</span>@endif
        </p>
        <p class="text-xs text-neutral-500">{{ $o->created_at->format('d M Y H:i') }}</p>
        <p class="truncate text-xs text-neutral-500">📍 {{ $o->branch->name }}</p>
      </div>
      <div class="flex shrink-0 flex-col items-end gap-1">
        <span class="badge {{ $o->status_color }}">{{ \App\Models\Order::STATUSES[$o->status] }}</span>
        <span class="badge {{ $o->payment_color }}">{{ str_replace('_', ' ', $o->payment_status) }}</span>
      </div>
    </div>

    <ul class="mt-3 space-y-1 text-sm text-neutral-700">
      @foreach ($o->items as $it)
        <li class="flex gap-1.5"><span>•</span><span class="min-w-0 break-words">{{ $it->item_name }} <span class="text-neutral-400">×{{ $it->qty }}</span></span></li>
      @endforeach
    </ul>

    <div class="mt-3 flex items-end justify-between gap-3 border-t pt-3">
      <div class="text-sm">
        <p class="text-neutral-500">Total <b class="text-base text-red-600">{{ $rp($o->total) }}</b></p>
        @if ($o->remaining > 0 && $o->status !== 'batal')
          <p class="text-xs text-neutral-500">Sisa {{ $rp($o->remaining) }}</p>
        @endif
        <p class="text-xs text-neutral-500">Estimasi: {{ $o->estimated_done_at?->format('d M Y') ?? '-' }}</p>
      </div>
    </div>

    @if ($o->status === 'menunggu')
      <p class="mt-2 rounded-lg bg-yellow-50 p-2 text-xs text-yellow-800">Pembayaran bisa dilakukan setelah admin mengonfirmasi pesanan.</p>
    @endif

    <div class="mt-3 grid gap-2 {{ ($canPay || $o->status === 'menunggu') ? 'grid-cols-2' : 'grid-cols-1' }}">
      @if ($canPay)
        <button type="button" @click="qris = true" class="btn-wa justify-center py-3">💳 Bayar QRIS</button>
      @endif
      @if ($o->status === 'menunggu')
        <form method="POST" action="{{ route('portal.cancel', $o) }}" onsubmit="return confirm('Batalkan pesanan ini?')">
          @csrf @method('PATCH')
          <button class="btn-light w-full justify-center py-3">Batalkan</button>
        </form>
      @endif
      <a href="{{ route('portal.show', $o) }}" class="btn justify-center py-3 {{ $canPay || $o->status === 'menunggu' ? '' : '' }}">Lihat Detail</a>
    </div>

    {{-- Panel pembayaran QRIS --}}
    @if ($canPay)
      <div x-show="qris" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center">
        <div x-show="qris" x-transition.opacity @click="qris = false" class="absolute inset-0 bg-black/70"></div>

        <div x-show="qris" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-full sm:translate-y-4 sm:opacity-0"
             x-transition:enter-end="translate-y-0 sm:opacity-100"
             class="safe-b relative max-h-[92vh] w-full overflow-y-auto rounded-t-3xl bg-white p-5 shadow-2xl sm:max-w-md sm:rounded-2xl sm:p-6">
          <div class="mx-auto mb-3 h-1.5 w-12 rounded-full bg-neutral-300 sm:hidden"></div>

          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <h3 class="text-lg font-extrabold">Bayar via QRIS</h3>
              <p class="truncate text-xs text-neutral-500">{{ $o->branch->name }}</p>
              <p class="font-mono text-xs text-neutral-500">{{ $o->invoice_no }}</p>
            </div>
            <button type="button" @click="qris = false" class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-neutral-100 text-lg" aria-label="Tutup">✕</button>
          </div>

          <div class="mt-4 rounded-xl bg-neutral-950 p-4 text-center text-white">
            <p class="text-[11px] uppercase tracking-widest text-red-400">Nominal yang dibayar</p>
            <p class="mt-1 text-3xl font-black">{{ $rp($o->remaining) }}</p>
            <button type="button"
                    @click="navigator.clipboard.writeText('{{ $o->remaining }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    class="mt-2 text-xs text-neutral-300 underline" x-text="copied ? 'Tersalin ✓' : 'Salin nominal'">Salin nominal</button>
          </div>

          <div class="mt-4 flex justify-center">
            @if ($qrisUrl)
              <img src="{{ $qrisUrl }}" alt="QRIS {{ $o->branch->name }}" class="max-h-72 w-auto max-w-full rounded-xl border border-neutral-200 object-contain p-2">
            @else
              <div class="w-full rounded-xl border-2 border-dashed border-neutral-300 p-6 text-center text-sm text-neutral-500">
                QRIS cabang ini belum dipasang.<br>Hubungi admin atau bayar langsung di kasir.
              </div>
            @endif
          </div>

          <ol class="mt-4 list-inside list-decimal space-y-1 text-sm text-neutral-700">
            <li>Screenshot / simpan gambar QRIS, lalu scan lewat aplikasi bank atau e-wallet.</li>
            <li>Masukkan nominal <b>{{ $rp($o->remaining) }}</b>, lalu bayar.</li>
            <li>Screenshot bukti pembayaran.</li>
            <li>Tekan tombol di bawah dan <b>lampirkan screenshot</b> di chat WhatsApp admin.</li>
          </ol>

          @if ($waUrl)
            <a href="{{ $waUrl }}" target="_blank" rel="noopener"
               class="mt-5 flex items-center justify-center gap-2 rounded-xl bg-green-500 py-3.5 font-bold text-white active:scale-95">
              💬 Konfirmasi ke WhatsApp Admin
            </a>
            <p class="mt-2 text-center text-xs text-neutral-500">Pesan sudah terisi otomatis, tinggal lampirkan bukti.</p>
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