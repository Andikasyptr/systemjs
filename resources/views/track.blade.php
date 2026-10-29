@extends('layouts.public')
@section('content')
<div class="mx-auto max-w-xl px-4 py-16">
  <h1 class="text-2xl font-bold">Cek Status Pesanan</h1>
  <form class="card mt-6 space-y-4">
    <div><label class="label">No. Invoice</label><input name="invoice" value="{{ request('invoice') }}" class="input" placeholder="INV-260101-001" required></div>
    <div><label class="label">No. HP (yang terdaftar)</label><input name="phone" value="{{ request('phone') }}" class="input" required></div>
    <button class="btn w-full">Cek</button>
  </form>

  @if (request()->filled('invoice'))
    <div class="card mt-6">
      @if ($order)
        <p class="font-semibold">{{ $order->invoice_no }} · {{ $order->customer->name }}</p>
        <p class="mt-2"><span class="badge {{ $order->status_color }}">{{ \App\Models\Order::STATUSES[$order->status] }}</span>
           <span class="badge {{ $order->payment_color }}">{{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}</span></p>
        <ul class="mt-3 text-sm text-slate-600">
          @foreach ($order->items as $i)<li>• {{ $i->item_name }} ({{ $i->qty }}x)</li>@endforeach
        </ul>
        <p class="mt-3 text-sm">Estimasi selesai: <b>{{ $order->estimated_done_at?->format('d M Y') ?? '-' }}</b></p>
      @else
        <p class="text-sm text-rose-600">Pesanan tidak ditemukan. Periksa kembali data Anda.</p>
      @endif
    </div>
  @endif
</div>
@endsection