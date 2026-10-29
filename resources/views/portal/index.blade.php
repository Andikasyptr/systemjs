@extends('layouts.panel')
@section('title', 'Akun Saya')
@section('content')
@php $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.'); @endphp

{{-- Kartu member --}}
<div class="relative overflow-hidden rounded-2xl bg-neutral-950 p-6 text-white shadow-lg">
  <div class="absolute -right-10 -top-10 h-44 w-44 rounded-full bg-red-600/40 blur-2xl"></div>
  <div class="relative flex flex-wrap items-start justify-between gap-4">
    <div>
      <p class="text-xs uppercase tracking-widest text-red-400">Member Card</p>
      <p class="mt-1 text-2xl font-black">{{ $customer->name }}</p>
      <p class="font-mono text-sm text-neutral-400">{{ $customer->member_code }} · {{ $customer->phone }}</p>
    </div>
    <span class="rounded-full bg-red-600 px-4 py-1 text-sm font-bold">{{ $customer->tier }}</span>
  </div>

  <div class="relative mt-6 grid grid-cols-3 gap-4 text-center">
    <div class="rounded-xl bg-white/10 p-3"><p class="text-2xl font-extrabold">{{ number_format($customer->points) }}</p><p class="text-xs text-neutral-300">Poin</p></div>
    <div class="rounded-xl bg-white/10 p-3"><p class="text-2xl font-extrabold">{{ $customer->discount_percent }}%</p><p class="text-xs text-neutral-300">Diskon member</p></div>
    <div class="rounded-xl bg-white/10 p-3"><p class="text-2xl font-extrabold">{{ $orders->count() }}</p><p class="text-xs text-neutral-300">Total pesanan</p></div>
  </div>

  <div class="relative mt-5">
    @if ($nextTarget)
      <div class="h-2 overflow-hidden rounded-full bg-white/20"><div class="h-full rounded-full bg-red-500" style="width: {{ $progress }}%"></div></div>
      <p class="mt-2 text-xs text-neutral-300">Belanja {{ $rp($nextTarget - $customer->total_spent) }} lagi untuk naik ke <b>{{ $nextTier }}</b>.</p>
    @else
      <p class="text-xs text-neutral-300">Anda sudah di tier tertinggi. Terima kasih sudah setia! 🎉</p>
    @endif
  </div>
</div>

<a href="{{ route('portal.order.create') }}" class="btn w-full py-3 text-base">+ Pesan Cucian</a>

<div class="grid gap-3 sm:grid-cols-3">
  <div class="card"><p class="text-xs uppercase text-neutral-500">Total belanja</p><p class="mt-1 text-xl font-bold text-red-600">{{ $rp($customer->total_spent) }}</p></div>
  <div class="card"><p class="text-xs uppercase text-neutral-500">Pesanan diproses</p><p class="mt-1 text-xl font-bold">{{ $active->count() }}</p></div>
  <div class="card"><p class="text-xs uppercase text-neutral-500">Cara dapat poin</p><p class="mt-1 text-sm">Tiap belanja <b>Rp 10.000</b> lunas = <b>1 poin</b>.</p></div>
</div>

{{-- Pesanan berjalan --}}
@if ($active->count())
  <div class="card">
    <h3 class="mb-3 font-bold">Pesanan Berjalan</h3>
    <div class="space-y-3">
      @foreach ($active as $o)
        <a href="{{ route('portal.show', $o) }}" class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-neutral-200 p-4 hover:border-red-400">
          <div>
            <p class="font-mono text-sm font-semibold">{{ $o->invoice_no }}</p>
            <p class="text-xs text-neutral-500">{{ $o->items->pluck('item_name')->join(', ') }}</p>
          </div>
          <div class="text-right">
            <span class="badge {{ $o->status_color }}">{{ \App\Models\Order::STATUSES[$o->status] }}</span>
            <p class="mt-1 text-xs text-neutral-500">Estimasi: {{ $o->estimated_done_at?->format('d M Y') ?? '-' }}</p>
          </div>
        </a>
      @endforeach
    </div>
  </div>
@endif

{{-- Riwayat --}}
<div class="card overflow-x-auto p-0">
  <div class="flex items-center justify-between px-4 pt-4">
    <h3 class="font-bold">Riwayat Pesanan</h3>
    <a href="{{ route('portal.orders') }}" class="text-sm font-semibold text-red-600 hover:underline">Lihat semua</a>
  </div>
  <table class="min-w-full divide-y">
    <thead><tr><th class="th">Invoice</th><th class="th">Barang</th><th class="th">Status</th><th class="th">Bayar</th><th class="th text-right">Total</th><th class="th"></th></tr></thead>
    <tbody class="divide-y">
      @forelse ($orders->take(5) as $o)
        <tr>
          <td class="td font-mono text-xs">{{ $o->invoice_no }}<br><span class="text-neutral-400">{{ $o->created_at->format('d/m/y') }}</span></td>
          <td class="td text-xs">{{ $o->items->pluck('item_name')->join(', ') }}</td>
          <td class="td"><span class="badge {{ $o->status_color }}">{{ \App\Models\Order::STATUSES[$o->status] }}</span></td>
          <td class="td"><span class="badge {{ $o->payment_color }}">{{ str_replace('_', ' ', $o->payment_status) }}</span></td>
          <td class="td text-right">{{ $rp($o->total) }}</td>
          <td class="td text-right"><a href="{{ route('portal.show', $o) }}" class="btn-light">Detail</a></td>
        </tr>
      @empty
        <tr><td colspan="6" class="td py-10 text-center text-neutral-500">Belum ada pesanan.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection