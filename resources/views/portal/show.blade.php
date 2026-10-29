@extends('layouts.panel')
@section('title', 'Pesanan ' . $order->invoice_no)
@section('content')
@php
  $rp    = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
  $steps = ['diterima' => 'Diterima', 'diproses' => 'Sedang Dicuci', 'selesai' => 'Selesai', 'diambil' => 'Sudah Diambil'];
  $idx   = array_search($order->status, array_keys($steps));
@endphp

<a href="{{ route('portal.orders') }}" class="text-sm text-neutral-600 hover:text-red-600">← Pesanan Saya</a>

@if ($order->status === 'menunggu')
  <div class="rounded-xl bg-yellow-50 p-4 text-sm text-yellow-800 ring-1 ring-yellow-200">
    <p><b>Menunggu konfirmasi admin.</b> Antar barang ke cabang, dan kami akan mengabari lewat WhatsApp.</p>
    <form method="POST" action="{{ route('portal.cancel', $order) }}" class="mt-3" onsubmit="return confirm('Batalkan pesanan ini?')">
      @csrf @method('PATCH')
      <button class="btn-light">Batalkan Pesanan</button>
    </form>
  </div>
@endif

<div class="card">
  <div class="flex flex-wrap items-start justify-between gap-2">
    <div>
      <p class="font-mono text-sm text-neutral-500">{{ $order->invoice_no }}</p>
      <p class="text-lg font-bold">{{ $order->branch->name }}</p>
      <p class="text-xs text-neutral-500">Dibuat {{ $order->created_at->format('d M Y H:i') }} · Estimasi {{ $order->estimated_done_at?->format('d M Y') ?? '-' }}</p>
    </div>
    <div class="text-right">
      <span class="badge {{ $order->status_color }}">{{ \App\Models\Order::STATUSES[$order->status] }}</span>
      <span class="badge {{ $order->payment_color }}">{{ str_replace('_', ' ', $order->payment_status) }}</span>
    </div>
  </div>

  @if (! in_array($order->status, ['batal', 'menunggu']))
    <ol class="mt-6 grid grid-cols-4 gap-2 text-center text-xs">
      @foreach ($steps as $k => $label)
        @php $i = array_search($k, array_keys($steps)); $done = $idx !== false && $i <= $idx; @endphp
        <li>
          <span class="mx-auto grid h-8 w-8 place-items-center rounded-full font-bold {{ $done ? 'bg-red-600 text-white' : 'bg-neutral-200 text-neutral-500' }}">{{ $done ? '✓' : $i + 1 }}</span>
          <p class="mt-1 {{ $done ? 'font-semibold' : 'text-neutral-400' }}">{{ $label }}</p>
        </li>
      @endforeach
    </ol>
  @endif
</div>

<div class="card overflow-x-auto p-0">
  <table class="min-w-full divide-y">
    <thead><tr><th class="th">Barang</th><th class="th">Treatment</th><th class="th">Qty</th><th class="th text-right">Subtotal</th></tr></thead>
    <tbody class="divide-y">
      @foreach ($order->items as $it)
        <tr><td class="td">{{ $it->item_name }}</td><td class="td">{{ $it->service->name }}</td><td class="td">{{ $it->qty }}</td><td class="td text-right">{{ $rp($it->subtotal) }}</td></tr>
      @endforeach
    </tbody>
    <tfoot class="text-sm">
      <tr><td colspan="3" class="td text-right">Subtotal</td><td class="td text-right">{{ $rp($order->subtotal) }}</td></tr>
      <tr><td colspan="3" class="td text-right">Diskon member</td><td class="td text-right text-red-600">- {{ $rp($order->discount) }}</td></tr>
      <tr class="font-bold"><td colspan="3" class="td text-right">Total</td><td class="td text-right">{{ $rp($order->total) }}</td></tr>
      <tr><td colspan="3" class="td text-right">Sudah dibayar</td><td class="td text-right">{{ $rp($order->paid_amount) }}</td></tr>
      <tr class="font-bold text-red-600"><td colspan="3" class="td text-right">Sisa tagihan</td><td class="td text-right">{{ $rp($order->remaining) }}</td></tr>
    </tfoot>
  </table>
</div>

@if ($order->payment_status === 'lunas')
  <div class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800 ring-1 ring-emerald-200">
    Pesanan lunas. Anda mendapat <b>{{ intdiv($order->total, 10000) }} poin</b> dari transaksi ini. 🎉
  </div>
@endif

@if ($order->payments->count())
  <div class="card">
    <h3 class="mb-2 font-bold">Riwayat Pembayaran</h3>
    @foreach ($order->payments as $p)
      <div class="flex justify-between border-b py-2 text-sm last:border-0">
        <span>{{ $p->paid_at->format('d/m/y H:i') }} · {{ strtoupper($p->method) }}</span><b>{{ $rp($p->amount) }}</b>
      </div>
    @endforeach
  </div>
@endif
@endsection