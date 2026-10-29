@extends('layouts.panel')
@section('title', 'Pesanan ' . $order->invoice_no)
@section('content')
@php
  $role      = auth()->user()->role;
  $canPay    = in_array($role, ['admin', 'kasir']);
  $rp        = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
@endphp

<a href="{{ route('orders.index') }}" class="text-sm text-neutral-600 hover:text-red-600">← Daftar Pesanan</a>

{{-- ADMIN: konfirmasi pesanan online --}}
@if ($order->status === 'menunggu' && $role === 'admin')
  <div class="rounded-xl bg-yellow-50 p-5 ring-1 ring-yellow-300">
    <p class="font-bold text-yellow-900">🔔 Pesanan online menunggu konfirmasi</p>
    <p class="mt-1 text-sm text-yellow-800">Pastikan barang sudah diterima dan kondisinya dicek, lalu konfirmasi. Customer akan melihat statusnya berubah.</p>

    <div class="mt-4 flex flex-wrap items-end gap-3">
      <form method="POST" action="{{ route('orders.status', $order) }}" class="flex flex-wrap items-end gap-3">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="diterima">
        <div>
          <label class="label">Estimasi selesai</label>
          <input type="date" name="estimated_done_at" value="{{ $order->estimated_done_at?->format('Y-m-d') }}" class="input">
        </div>
        <button class="btn">✓ Konfirmasi Pesanan</button>
      </form>

      <form method="POST" action="{{ route('orders.status', $order) }}" onsubmit="return confirm('Tolak pesanan ini?')">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="batal">
        <button class="btn-danger">Tolak</button>
      </form>
    </div>
  </div>
@endif

<div class="grid gap-4 lg:grid-cols-3">
  <div class="space-y-4 lg:col-span-2">
    <div class="card">
      <div class="flex flex-wrap justify-between gap-2">
        <div>
          <p class="font-mono text-sm text-neutral-500">
            {{ $order->invoice_no }}
            @if ($order->source === 'online')<span class="badge bg-red-600 text-white">Online</span>@endif
          </p>
          <p class="text-xl font-bold">{{ $order->customer->name }}</p>
          <p class="text-sm text-neutral-600">{{ $order->customer->phone }} · {{ $order->branch->name }}</p>
          <p class="text-xs text-neutral-500">Dibuat {{ $order->created_at->format('d M Y H:i') }}</p>
        </div>
        <div class="text-right">
          <span class="badge {{ $order->status_color }}">{{ \App\Models\Order::STATUSES[$order->status] }}</span>
          <span class="badge {{ $order->payment_color }}">{{ str_replace('_', ' ', $order->payment_status) }}</span>
          <p class="mt-2 text-xs text-neutral-500">Estimasi: {{ $order->estimated_done_at?->format('d M Y') ?? '-' }}</p>
        </div>
      </div>
      @if ($order->notes)
        <p class="mt-3 rounded-lg bg-neutral-50 p-3 text-sm"><b>Catatan:</b> {{ $order->notes }}</p>
      @endif
    </div>

    @if ($order->photos->count())
      <div class="card" x-data="{ img: null }" @keydown.escape.window="img = null">
        <h3 class="mb-3 font-semibold">Foto Barang</h3>
        <div class="grid grid-cols-3 gap-2 sm:grid-cols-5">
          @foreach ($order->photos as $p)
            <img src="{{ $p->url }}" @click="img = '{{ $p->url }}'" class="aspect-square w-full cursor-zoom-in rounded-lg border object-cover" alt="Foto barang">
          @endforeach
        </div>
        <div x-show="img" x-cloak @click="img = null" class="fixed inset-0 z-50 grid place-items-center bg-black/90 p-4">
          <img :src="img" class="max-h-[85vh] rounded-xl object-contain" alt="Foto barang">
        </div>
      </div>
    @endif

    <div class="card overflow-x-auto p-0">
      <table class="min-w-full divide-y">
        <thead><tr><th class="th">Barang</th><th class="th">Treatment</th><th class="th">Qty</th><th class="th text-right">Subtotal</th></tr></thead>
        <tbody class="divide-y">
          @foreach ($order->items as $i)
            <tr><td class="td">{{ $i->item_name }}</td><td class="td">{{ $i->service->name }}</td><td class="td">{{ $i->qty }}</td><td class="td text-right">{{ $rp($i->subtotal) }}</td></tr>
          @endforeach
        </tbody>
        <tfoot class="text-sm">
          <tr><td colspan="3" class="td text-right">Subtotal</td><td class="td text-right">{{ $rp($order->subtotal) }}</td></tr>
          <tr><td colspan="3" class="td text-right">Diskon member</td><td class="td text-right text-red-600">- {{ $rp($order->discount) }}</td></tr>
          <tr class="font-bold"><td colspan="3" class="td text-right">Total</td><td class="td text-right">{{ $rp($order->total) }}</td></tr>
          <tr><td colspan="3" class="td text-right">Sudah dibayar</td><td class="td text-right">{{ $rp($order->paid_amount) }}</td></tr>
          <tr class="font-bold text-red-600"><td colspan="3" class="td text-right">Sisa</td><td class="td text-right">{{ $rp($order->remaining) }}</td></tr>
        </tfoot>
      </table>
    </div>

    <div class="card">
      <h3 class="mb-3 font-semibold">Riwayat Pembayaran</h3>
      @forelse ($order->payments as $p)
        <div class="flex justify-between border-b py-2 text-sm last:border-0">
          <span>{{ $p->paid_at->format('d/m/y H:i') }} · {{ strtoupper($p->method) }} · oleh {{ $p->receiver->name }}</span>
          <b>{{ $rp($p->amount) }}</b>
        </div>
      @empty
        <p class="text-sm text-neutral-500">Belum ada pembayaran.</p>
      @endforelse
    </div>
  </div>

  <div class="space-y-4">
    {{-- ADMIN: ubah status + WhatsApp --}}
    @if ($role === 'admin')
      <div class="card space-y-3">
        <h3 class="font-semibold">Update Status</h3>
        <form method="POST" action="{{ route('orders.status', $order) }}" class="space-y-2">
          @csrf @method('PATCH')
          <select name="status" class="input">
            @foreach (\App\Models\Order::STATUSES as $k => $v)
              <option value="{{ $k }}" @selected($order->status == $k)>{{ $v }}</option>
            @endforeach
          </select>
          <input type="date" name="estimated_done_at" value="{{ $order->estimated_done_at?->format('Y-m-d') }}" class="input">
          <button class="btn w-full">Simpan</button>
        </form>

        <h3 class="pt-2 font-semibold">Kirim Info WhatsApp</h3>
        <div class="grid gap-2">
          <a target="_blank" href="{{ $order->waLink('diterima') }}" class="btn-wa">Pesanan diterima</a>
          <a target="_blank" href="{{ $order->waLink('diproses') }}" class="btn-wa">Sedang diproses</a>
          <a target="_blank" href="{{ $order->waLink('selesai') }}" class="btn-wa">Selesai, siap diambil</a>
          <a target="_blank" href="{{ $order->waLink('tagihan') }}" class="btn-wa">Tagihan pembayaran</a>
          <a target="_blank" href="{{ $order->waLink('lunas') }}" class="btn-wa">Terima kasih (lunas)</a>
        </div>
      </div>
    @endif

    {{-- ADMIN + KASIR: terima pembayaran --}}
    @if ($canPay)
      @if ($order->status === 'menunggu')
        <div class="card text-sm text-yellow-800">Pesanan ini belum dikonfirmasi, jadi belum bisa menerima pembayaran.</div>
      @elseif ($order->status === 'batal')
        <div class="card text-sm text-neutral-500">Pesanan dibatalkan.</div>
      @elseif ($order->remaining > 0)
        <form method="POST" action="{{ route('payments.store', $order) }}" class="card space-y-3">
          @csrf
          <h3 class="font-semibold">Terima Pembayaran</h3>
          <div><label class="label">Nominal</label><input type="number" name="amount" value="{{ $order->remaining }}" max="{{ $order->remaining }}" class="input" required></div>
          <div><label class="label">Metode</label>
            <select name="method" class="input"><option value="tunai">Tunai</option><option value="transfer">Transfer</option><option value="qris">QRIS</option></select></div>
          <div><label class="label">Catatan</label><input name="note" class="input"></div>
          <button class="btn w-full">Catat Pembayaran</button>
        </form>
      @else
        <div class="card text-sm font-semibold text-emerald-700">✓ Pesanan sudah lunas.</div>
      @endif
    @endif
  </div>
</div>
@endsection