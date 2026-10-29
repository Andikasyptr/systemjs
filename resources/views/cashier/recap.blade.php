@extends('layouts.panel')
@section('title', 'Rekap Pembayaran')
@section('content')
@php $rp = fn($n) => 'Rp ' . number_format($n, 0, ',', '.'); @endphp
<form class="flex gap-2"><input type="date" name="date" value="{{ $date }}" class="input w-48"><button class="btn">Tampilkan</button></form>

<div class="grid gap-4 sm:grid-cols-4">
  <div class="card"><p class="text-xs uppercase text-slate-500">Total Masuk</p><p class="text-2xl font-bold text-teal-700">{{ $rp($payments->sum('amount')) }}</p></div>
  @foreach (['tunai', 'transfer', 'qris'] as $m)
    <div class="card"><p class="text-xs uppercase text-slate-500">{{ $m }}</p><p class="text-xl font-bold">{{ $rp($byMethod[$m] ?? 0) }}</p></div>
  @endforeach
</div>

<div class="card overflow-x-auto p-0">
  <h3 class="px-4 pt-4 font-semibold">Pembayaran Masuk</h3>
  <table class="min-w-full divide-y">
    <thead><tr><th class="th">Jam</th><th class="th">Invoice</th><th class="th">Pelanggan</th><th class="th">Metode</th><th class="th">Kasir</th><th class="th text-right">Nominal</th></tr></thead>
    <tbody class="divide-y">
      @forelse ($payments as $p)
        <tr><td class="td">{{ $p->paid_at->format('H:i') }}</td><td class="td"><a class="text-teal-700 underline" href="{{ route('orders.show', $p->order) }}">{{ $p->order->invoice_no }}</a></td>
          <td class="td">{{ $p->order->customer->name }}</td><td class="td uppercase">{{ $p->method }}</td><td class="td">{{ $p->receiver->name }}</td><td class="td text-right">{{ $rp($p->amount) }}</td></tr>
      @empty
        <tr><td colspan="6" class="td text-center text-slate-500">Belum ada pembayaran.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="card overflow-x-auto p-0">
  <h3 class="px-4 pt-4 font-semibold">Belum Lunas ({{ $unpaid->count() }})</h3>
  <table class="min-w-full divide-y">
    <tbody class="divide-y">
      @foreach ($unpaid as $o)
        <tr><td class="td">{{ $o->invoice_no }}</td><td class="td">{{ $o->customer->name }}</td>
          <td class="td"><span class="badge {{ $o->payment_color }}">{{ str_replace('_', ' ', $o->payment_status) }}</span></td>
          <td class="td text-right font-medium">Sisa {{ $rp($o->remaining) }}</td>
          <td class="td text-right"><a href="{{ route('orders.show', $o) }}" class="btn-light">Buka</a></td></tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection