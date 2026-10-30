@extends('layouts.print')
@section('title', $order->invoice_no)

@section('content')
@php
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
@endphp

<div class="text-center border-b">
    <div class="font-bold" style="font-size: 13px;">JEJAK SUCI</div>
    <div>{{ $order->branch->name }}</div>
    <div style="font-size: 9px; color: #333;">{{ $order->branch->address }}</div>
    @if($order->branch->whatsapp)
      <div style="font-size: 9px;">WA: {{ $order->branch->whatsapp }}</div>
    @endif
</div>

<div class="border-b space-y-1" style="font-size: 10px;">
    <div class="flex"><span>No:</span><span class="font-bold">{{ $order->invoice_no }}</span></div>
    <div class="flex"><span>Tgl:</span><span>{{ $order->created_at->format('d/m/y H:i') }}</span></div>
    <div class="flex"><span>Pelanggan:</span><span>{{ $order->customer->name }}</span></div>
    <div class="flex"><span>No HP:</span><span>{{ $order->customer->phone }}</span></div>
    <div class="flex"><span>Kasir:</span><span>{{ auth()->user()->name }}</span></div>
</div>

<div class="border-b">
    <div class="font-bold mb-1">DETAIL LAYANAN:</div>
    @foreach ($order->items as $i)
      <div style="margin-bottom: 3px;">
        <div class="font-bold">{{ $i->item_name }}</div>
        <div class="flex" style="padding-left: 10px; font-size: 10px;">
          <span>{{ $i->qty }}x {{ $i->service->name }}</span>
          <span>{{ $rp($i->subtotal) }}</span>
        </div>
      </div>
    @endforeach
</div>

<div class="border-b space-y-1">
    <div class="flex"><span>Subtotal:</span><span>{{ $rp($order->subtotal) }}</span></div>
    @if($order->discount > 0)
      <div class="flex"><span>Diskon:</span><span>-{{ $rp($order->discount) }}</span></div>
    @endif
    <div class="flex font-bold pt-1" style="font-size: 12px;"><span>TOTAL:</span><span>{{ $rp($order->total) }}</span></div>
    <div class="flex"><span>Bayar:</span><span>{{ $rp($order->paid_amount) }}</span></div>
    <div class="flex font-bold"><span>SISA:</span><span>{{ $rp($order->remaining) }}</span></div>
</div>

<div class="border-b" style="font-size: 10px;">
    <div class="flex"><span>Status:</span><span class="font-bold">{{ strtoupper(str_replace('_', ' ', $order->payment_status)) }}</span></div>
    <div class="flex"><span>Estimasi:</span><span>{{ $order->estimated_done_at?->format('d M Y') ?? '-' }}</span></div>
</div>

<div class="text-center pt-2" style="font-size: 9px;">
    <div class="font-bold">TERIMA KASIH</div>
    <div>Barang tidak diambil >30 hari di luar tanggung jawab kami.</div>
    <div style="margin-top: 4px;">=== Layanan Cuci Sepatu & Tas ===</div>
</div>
@endsection