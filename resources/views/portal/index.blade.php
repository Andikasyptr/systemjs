@extends('layouts.panel')
@section('title', 'Akun Saya')
@section('content')
@php $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.'); @endphp

{{-- Kartu member --}}
<div class="relative overflow-hidden rounded-2xl bg-neutral-950 p-5 text-white shadow-lg sm:p-6">
  <div class="absolute -right-10 -top-10 h-44 w-44 rounded-full bg-red-600/40 blur-2xl"></div>

  <div class="relative flex items-start justify-between gap-3">
    <div class="min-w-0">
      <p class="text-[10px] uppercase tracking-widest text-red-400">Member Card</p>
      <p class="mt-1 truncate text-xl font-black sm:text-2xl">{{ $customer->name }}</p>
      <p class="truncate font-mono text-xs text-neutral-400">{{ $customer->member_code }}</p>
      <p class="truncate text-xs text-neutral-400">{{ $customer->phone }}</p>
    </div>
    <span class="shrink-0 rounded-full bg-red-600 px-3 py-1 text-xs font-bold sm:text-sm">{{ $customer->tier }}</span>
  </div>

  <div class="relative mt-5 grid grid-cols-3 gap-2 text-center sm:gap-4">
    <div class="rounded-xl bg-white/10 p-2.5 sm:p-3"><p class="text-lg font-extrabold sm:text-2xl">{{ number_format($customer->points) }}</p><p class="text-[11px] text-neutral-300">Poin</p></div>
    <div class="rounded-xl bg-white/10 p-2.5 sm:p-3"><p class="text-lg font-extrabold sm:text-2xl">{{ $customer->discount_percent }}%</p><p class="text-[11px] text-neutral-300">Diskon</p></div>
    <div class="rounded-xl bg-white/10 p-2.5 sm:p-3"><p class="text-lg font-extrabold sm:text-2xl">{{ $orders->count() }}</p><p class="text-[11px] text-neutral-300">Pesanan</p></div>
  </div>

  <div class="relative mt-4">
    @if ($nextTarget)
      <div class="h-2 overflow-hidden rounded-full bg-white/20"><div class="h-full rounded-full bg-red-500" style="width: {{ $progress }}%"></div></div>
      <p class="mt-2 text-xs text-neutral-300">Belanja {{ $rp($nextTarget - $customer->total_spent) }} lagi untuk naik ke <b>{{ $nextTier }}</b>.</p>
    @else
      <p class="text-xs text-neutral-300">Anda sudah di tier tertinggi. Terima kasih sudah setia! 🎉</p>
    @endif
  </div>
</div>

{{-- Aksi cepat --}}
<div class="grid grid-cols-2 gap-3">
  <a href="{{ route('portal.order.create') }}" class="flex items-center justify-center gap-2 rounded-2xl bg-red-600 py-4 text-sm font-bold text-white shadow-lg shadow-red-600/30 active:scale-95">➕ Pesan Cucian</a>
  <a href="{{ route('portal.orders') }}" class="flex items-center justify-center gap-2 rounded-2xl bg-white py-4 text-sm font-bold ring-1 ring-neutral-200 active:scale-95">🧺 Pesanan Saya</a>
</div>

{{-- Ringkasan --}}
<div class="grid grid-cols-2 gap-3">
  <div class="card !p-4"><p class="text-[11px] uppercase text-neutral-500">Total belanja</p><p class="mt-1 text-lg font-bold text-red-600">{{ $rp($customer->total_spent) }}</p></div>
  <div class="card !p-4"><p class="text-[11px] uppercase text-neutral-500">Sedang diproses</p><p class="mt-1 text-lg font-bold">{{ $active->count() }}</p></div>
  <div class="card col-span-2 !p-4"><p class="text-[11px] uppercase text-neutral-500">Cara dapat poin</p><p class="mt-1 text-sm">Tiap belanja <b>Rp 10.000</b> lunas = <b>1 poin</b>.</p></div>
</div>

{{-- Pesanan berjalan --}}
@if ($active->count())
  <div class="space-y-3">
    <h3 class="font-bold">Pesanan Berjalan</h3>
    @foreach ($active as $o)
      <a href="{{ route('portal.show', $o) }}" class="card block !p-4 transition active:scale-[.99]">
        <div class="flex items-start justify-between gap-2">
          <p class="font-mono text-sm font-semibold">{{ $o->invoice_no }}</p>
          <span class="badge shrink-0 {{ $o->status_color }}">{{ \App\Models\Order::STATUSES[$o->status] }}</span>
        </div>
        <p class="mt-1 line-clamp-2 text-xs text-neutral-500">{{ $o->items->pluck('item_name')->join(', ') }}</p>
        <div class="mt-3 flex items-center justify-between text-xs">
          <span class="text-neutral-500">Estimasi: {{ $o->estimated_done_at?->format('d M Y') ?? '-' }}</span>
          <b class="text-red-600">{{ $rp($o->total) }}</b>
        </div>
      </a>
    @endforeach
  </div>
@endif

{{-- Riwayat terbaru --}}
<div class="space-y-3">
  <div class="flex items-center justify-between">
    <h3 class="font-bold">Riwayat Terbaru</h3>
    <a href="{{ route('portal.orders') }}" class="text-sm font-semibold text-red-600">Lihat semua</a>
  </div>

  @forelse ($orders->take(5) as $o)
    <a href="{{ route('portal.show', $o) }}" class="card flex items-center justify-between gap-3 !p-4 active:scale-[.99]">
      <div class="min-w-0">
        <p class="font-mono text-xs font-semibold">{{ $o->invoice_no }}</p>
        <p class="truncate text-xs text-neutral-500">{{ $o->items->pluck('item_name')->join(', ') }}</p>
        <p class="text-[11px] text-neutral-400">{{ $o->created_at->format('d M Y') }}</p>
      </div>
      <div class="shrink-0 text-right">
        <p class="text-sm font-bold">{{ $rp($o->total) }}</p>
        <span class="badge {{ $o->status_color }}">{{ \App\Models\Order::STATUSES[$o->status] }}</span>
      </div>
    </a>
  @empty
    <div class="card py-10 text-center text-sm text-neutral-500">Belum ada pesanan.</div>
  @endforelse
</div>
@endsection